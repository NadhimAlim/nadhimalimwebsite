<?php

namespace App\Http\Controllers;

use App\Models\ProjectPayment;
use App\Models\WorkTask;
use App\Models\MarketplaceOrder;
use App\Models\MarketplaceProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function createLink(Request $request, WorkTask $workTask)
    {
        abort_unless($this->gatewayConfigured(), 503, 'Payment gateway belum dikonfigurasi.');

        $data = $request->validate([
            'customer_name' => 'required|string|max:120',
            'customer_email' => 'nullable|email|max:190',
            'amount' => 'required|integer|min:1000',
        ]);
        try {
            $payment = DB::transaction(function () use ($workTask, $data) {
                $task = WorkTask::whereKey($workTask->id)->lockForUpdate()->firstOrFail();
                $pendingInvoices = (int) $task->payments()->whereIn('status', ['pending', 'challenge'])->sum('gross_amount');
                $remaining = max(0, (int) $task->project_value - (int) $task->amount_paid - $pendingInvoices);
                if ($data['amount'] > $remaining) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['amount' => 'Nominal tagihan melebihi sisa nilai proyek.']);
                }

                return ProjectPayment::create([
                    'work_task_id' => $task->id,
                    'public_token' => Str::random(48),
                    'order_id' => 'NA-' . now()->format('ymdHis') . '-' . Str::upper(Str::random(8)),
                    'customer_name' => $data['customer_name'],
                    'customer_email' => $data['customer_email'] ?? null,
                    'gross_amount' => $data['amount'],
                    'status' => 'pending',
                    'expires_at' => now()->addDays(1),
                ]);
            });
        } catch (\Illuminate\Validation\ValidationException $exception) {
            throw $exception;
        }

        try {
            $this->createSnapTransaction($payment);
        } catch (\Throwable $exception) {
            $payment->delete();
            report($exception);
            return back()->withErrors(['payment' => 'Tagihan belum dapat dibuat. Periksa konfigurasi dan koneksi Midtrans.']);
        }

        return back()->with('success', 'Link pembayaran berhasil dibuat. Salin tautan untuk dikirim ke klien.');
    }

    public function show(ProjectPayment $payment)
    {
        $payment->load('workTask');
        abort_unless($payment->workTask, 404);
        return view('payments.checkout', compact('payment'));
    }

    public function notification(Request $request)
    {
        $payload = $request->all();
        $serverKey = (string) config('services.midtrans.server_key');
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signature = (string) ($payload['signature_key'] ?? '');

        if ($serverKey === '' || $orderId === '' || $signature === '') {
            return response()->json(['message' => 'Invalid notification.'], 400);
        }
        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if (!hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        $order = MarketplaceOrder::where('order_id', $orderId)->first();
        if ($order) {
            if (number_format((float) $order->total_amount, 2, '.', '') !== number_format((float) $grossAmount, 2, '.', '')) {
                return response()->json(['message' => 'Transaction amount mismatch.'], 400);
            }
            $this->updateMarketplaceOrderFromNotification($order, $payload);
            return response()->json(['status' => 'ok']);
        }

        $payment = ProjectPayment::where('order_id', $orderId)->first();
        if (!$payment || number_format((float) $payment->gross_amount, 2, '.', '') !== number_format((float) $grossAmount, 2, '.', '')) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        DB::transaction(function () use ($payment, $payload) {
            $payment = ProjectPayment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $task = WorkTask::whereKey($payment->work_task_id)->lockForUpdate()->first();
            $previousOnlinePaid = (int) $payment->workTask?->payments()->where('status', 'paid')->sum('gross_amount');
            $transactionStatus = $payload['transaction_status'] ?? '';
            $fraudStatus = $payload['fraud_status'] ?? null;
            $status = $payment->status === 'paid' ? 'paid' : match ($transactionStatus) {
                'settlement' => 'paid',
                'capture' => $fraudStatus === 'accept' ? 'paid' : 'pending',
                'pending' => 'pending',
                'deny' => 'denied',
                'cancel' => 'cancelled',
                'expire' => 'expired',
                default => $payment->status,
            };
            $payment->update([
                'status' => $status,
                'transaction_id' => $payload['transaction_id'] ?? $payment->transaction_id,
                'payment_type' => $payload['payment_type'] ?? $payment->payment_type,
                'notification_payload' => $payload,
                'paid_at' => $status === 'paid' ? ($payment->paid_at ?? now()) : $payment->paid_at,
            ]);

            if ($status === 'paid') {
                if ($task) {
                    $paidOnline = (int) $task->payments()->where('status', 'paid')->sum('gross_amount');
                    $manualPaid = max(0, (int) $task->amount_paid - $previousOnlinePaid);
                    $amountPaid = min((int) $task->project_value, $manualPaid + $paidOnline);
                    $task->update([
                        'amount_paid' => $amountPaid,
                        'payment_status' => $amountPaid >= (int) $task->project_value ? 'paid' : ($amountPaid > 0 ? 'partial' : 'unpaid'),
                    ]);
                }
            }
        });

        return response()->json(['status' => 'ok']);
    }

    private function updateMarketplaceOrderFromNotification(MarketplaceOrder $order, array $payload): void
    {
        DB::transaction(function () use ($order, $payload) {
            $order = MarketplaceOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->payment_status === 'paid') return;
            $previousStatus = $order->status;
            $transactionStatus = $payload['transaction_status'] ?? '';
            $newPaymentStatus = match ($transactionStatus) {
                'settlement' => 'paid',
                'capture' => ($payload['fraud_status'] ?? null) === 'accept' ? 'paid' : 'pending',
                'pending' => 'pending',
                'deny' => 'denied',
                'cancel' => 'cancelled',
                'expire' => 'expired',
                default => $order->payment_status,
            };
            $newOrderStatus = match ($newPaymentStatus) {
                'paid' => 'awaiting_fulfillment',
                'denied' => 'payment_failed',
                'cancelled' => 'cancelled',
                'expired' => 'expired',
                default => $order->status,
            };
            $order->update([
                'payment_status' => $newPaymentStatus,
                'status' => $newOrderStatus,
                'transaction_id' => $payload['transaction_id'] ?? $order->transaction_id,
                'payment_type' => $payload['payment_type'] ?? $order->payment_type,
                'notification_payload' => $payload,
                'paid_at' => $newPaymentStatus === 'paid' ? ($order->paid_at ?? now()) : $order->paid_at,
            ]);

            if (in_array($newPaymentStatus, ['denied', 'cancelled', 'expired'], true) && $previousStatus === 'awaiting_payment' && $order->marketplace_product_id) {
                MarketplaceProduct::whereKey($order->marketplace_product_id)->lockForUpdate()->increment('stock', $order->quantity);
            }
        });
    }

    private function gatewayConfigured(): bool
    {
        return filled(config('services.midtrans.server_key')) && filled(config('services.midtrans.client_key'));
    }

    private function createSnapTransaction(ProjectPayment $payment): void
    {
        $production = (bool) config('services.midtrans.is_production');
        $baseUrl = $production ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
        $customer = ['first_name' => $payment->customer_name];
        if ($payment->customer_email) $customer['email'] = $payment->customer_email;
        $response = Http::withBasicAuth(config('services.midtrans.server_key'), '')
            ->acceptJson()->timeout(20)->post($baseUrl . '/snap/v1/transactions', [
                'transaction_details' => ['order_id' => $payment->order_id, 'gross_amount' => (int) $payment->gross_amount],
                'item_details' => [[
                    'id' => (string) $payment->work_task_id,
                    'price' => (int) $payment->gross_amount,
                    'quantity' => 1,
                    'name' => Str::limit($payment->workTask->title, 50, ''),
                ]],
                'customer_details' => $customer,
                'expiry' => ['unit' => 'day', 'duration' => 1],
            ]);
        $response->throw();
        $result = $response->json();
        if (empty($result['token']) || empty($result['redirect_url'])) {
            throw new \RuntimeException('Midtrans did not return a Snap token.');
        }
        $payment->update(['snap_token' => $result['token'], 'redirect_url' => $result['redirect_url']]);
    }
}
