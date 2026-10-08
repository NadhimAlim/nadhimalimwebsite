<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceOrder;
use App\Models\MarketplaceProduct;
use App\Models\PortfolioSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $search = Str::limit(trim((string) $request->query('q', '')), 80, '');
        $category = Str::limit(trim((string) $request->query('kategori', '')), 100, '');
        $query = MarketplaceProduct::where('is_active', true);
        if ($search !== '') $query->where(fn ($builder) => $builder->where('name', 'like', '%' . $search . '%')->orWhere('description', 'like', '%' . $search . '%'));
        if ($category !== '') $query->where('category', $category);
        $products = $query->orderBy('sort_order')->orderByDesc('created_at')->paginate(12)->withQueryString();
        $categories = MarketplaceProduct::where('is_active', true)->whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category')->pluck('category');
        return view('marketplace.index', compact('products', 'categories', 'search', 'category'));
    }

    public function show(MarketplaceProduct $product)
    {
        abort_unless($product->is_active, 404);
        $paymentEnabled = $this->gatewayConfigured();
        $whatsapp = preg_replace('/\D+/', '', PortfolioSetting::where('key', 'admin_whatsapp')->value('value') ?: config('portfolio.whatsapp'));
        return view('marketplace.show', compact('product', 'paymentEnabled', 'whatsapp'));
    }

    public function order(Request $request, MarketplaceProduct $product)
    {
        abort_unless($product->is_active, 404);
        abort_unless($this->gatewayConfigured(), 503, 'Pembayaran online belum tersedia. Silakan hubungi admin.');
        $data = $request->validate([
            'customer_name' => 'required|string|max:120',
            'customer_email' => 'nullable|email|max:190',
            'customer_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\-\s]{8,30}$/'],
            'shipping_address' => 'required|string|max:1500',
            'quantity' => 'required|integer|min:1|max:20',
        ]);

        $order = DB::transaction(function () use ($product, $data) {
            $lockedProduct = MarketplaceProduct::whereKey($product->id)->lockForUpdate()->firstOrFail();
            if (!$lockedProduct->is_active || $lockedProduct->stock < $data['quantity']) {
                throw \Illuminate\Validation\ValidationException::withMessages(['quantity' => 'Stok barang tidak mencukupi. Silakan muat ulang halaman.']);
            }
            $lockedProduct->decrement('stock', $data['quantity']);
            $total = $lockedProduct->price * $data['quantity'];

            return MarketplaceOrder::create([
                'marketplace_product_id' => $lockedProduct->id,
                'public_token' => Str::random(48),
                'order_id' => 'SHOP-' . now()->format('ymdHis') . '-' . Str::upper(Str::random(8)),
                'product_name' => $lockedProduct->name,
                'unit_price' => $lockedProduct->price,
                'quantity' => $data['quantity'],
                'total_amount' => $total,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'],
                'shipping_address' => $data['shipping_address'],
                'status' => 'awaiting_payment',
                'payment_status' => 'pending',
            ]);
        });

        try {
            $this->createSnapTransaction($order);
        } catch (\Throwable $exception) {
            DB::transaction(function () use ($order) {
                $order = MarketplaceOrder::whereKey($order->id)->lockForUpdate()->first();
                if ($order?->marketplace_product_id) {
                    MarketplaceProduct::whereKey($order->marketplace_product_id)->lockForUpdate()->increment('stock', $order->quantity);
                }
                $order?->delete();
            });
            report($exception);
            return back()->withErrors(['payment' => 'Checkout belum dapat dibuat. Silakan coba kembali atau hubungi admin.'])->withInput();
        }

        return redirect()->route('marketplace.checkout', $order->public_token);
    }

    public function checkout(MarketplaceOrder $order)
    {
        $whatsapp = preg_replace('/\D+/', '', PortfolioSetting::where('key', 'admin_whatsapp')->value('value') ?: config('portfolio.whatsapp'));
        return view('marketplace.checkout', compact('order', 'whatsapp'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedProduct($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['sort_order'] = (MarketplaceProduct::max('sort_order') ?? 0) + 1;
        if ($request->hasFile('image')) $data['image'] = $request->file('image')->store('marketplace', 'public');
        MarketplaceProduct::create($data);
        return back()->with('success', 'Barang berhasil ditambahkan ke marketplace.');
    }

    public function update(Request $request, MarketplaceProduct $product)
    {
        $data = $this->validatedProduct($request);
        $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
        if ($request->hasFile('image')) {
            $previous = $product->image;
            $data['image'] = $request->file('image')->store('marketplace', 'public');
            if ($previous) Storage::disk('public')->delete($previous);
        }
        $product->update($data);
        return back()->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(MarketplaceProduct $product)
    {
        if ($product->orders()->where('payment_status', 'paid')->exists()) {
            return back()->withErrors(['product' => 'Barang memiliki riwayat pesanan yang sudah dibayar. Nonaktifkan barang agar riwayat tetap tersimpan.']);
        }
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        return back()->with('success', 'Barang berhasil dihapus.');
    }

    public function updateOrder(Request $request, MarketplaceOrder $order)
    {
        $data = $request->validate(['status' => 'required|in:awaiting_fulfillment,processing,shipped,completed']);
        if ($order->payment_status !== 'paid') {
            return back()->withErrors(['order' => 'Pesanan baru dapat diproses setelah pembayaran terkonfirmasi.']);
        }
        $order->update(['status' => $data['status']]);
        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    private function validatedProduct(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:180',
            'category' => 'nullable|string|max:100',
            'description' => 'required|string|max:5000',
            'price' => 'required|integer|min:1000|max:999999999999',
            'stock' => 'required|integer|min:0|max:1000000',
            'is_active' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'barang';
        $slug = $base;
        $suffix = 2;
        while (MarketplaceProduct::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $suffix++;
        }
        return $slug;
    }

    private function gatewayConfigured(): bool
    {
        return filled(config('services.midtrans.server_key')) && filled(config('services.midtrans.client_key'));
    }

    private function createSnapTransaction(MarketplaceOrder $order): void
    {
        $production = (bool) config('services.midtrans.is_production');
        $baseUrl = $production ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
        $customer = ['first_name' => $order->customer_name, 'phone' => $order->customer_phone];
        if ($order->customer_email) $customer['email'] = $order->customer_email;
        $response = Http::withBasicAuth(config('services.midtrans.server_key'), '')->acceptJson()->timeout(20)
            ->post($baseUrl . '/snap/v1/transactions', [
                'transaction_details' => ['order_id' => $order->order_id, 'gross_amount' => $order->total_amount],
                'item_details' => [[
                    'id' => 'product-' . $order->marketplace_product_id,
                    'price' => $order->unit_price,
                    'quantity' => $order->quantity,
                    'name' => Str::limit($order->product_name, 50, ''),
                ]],
                'customer_details' => $customer,
                'expiry' => ['unit' => 'day', 'duration' => 1],
                'expiry' => ['unit' => 'day', 'duration' => 1],
            ]);
        $response->throw();
        $result = $response->json();
        if (empty($result['token']) || empty($result['redirect_url'])) throw new \RuntimeException('Midtrans did not return a Snap token.');
        $order->update(['snap_token' => $result['token'], 'redirect_url' => $result['redirect_url']]);
    }
}
