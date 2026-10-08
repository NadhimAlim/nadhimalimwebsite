<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceOrder;
use App\Models\MarketplaceProduct;
use App\Models\PortfolioSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
        return view('marketplace.show', compact('product'));
    }

    public function order(Request $request, MarketplaceProduct $product)
    {
        $this->addToCart($request, $product);
        return redirect()->route('marketplace.cart');
    }

    public function addToCart(Request $request, MarketplaceProduct $product)
    {
        abort_unless($product->is_active, 404);
        $data = $request->validate(['quantity' => 'required|integer|min:1|max:100']);
        if ($product->stock < 1) return back()->withErrors(['cart' => 'Stok barang ini sedang habis.']);

        $cart = $request->session()->get('marketplace_cart', []);
        $key = (string) $product->id;
        $newQuantity = (int) ($cart[$key] ?? 0) + (int) $data['quantity'];
        if ($newQuantity > $product->stock) {
            return back()->withErrors(['cart' => 'Jumlah melebihi stok yang tersedia. Maksimal ' . $product->stock . ' barang.']);
        }
        $cart[$key] = $newQuantity;
        $request->session()->put('marketplace_cart', $cart);
        return back()->with('cart_success', $product->name . ' ditambahkan ke keranjang.');
    }

    public function cart(Request $request)
    {
        $cartItems = $this->cartItems($request);
        $subtotal = $cartItems->sum('line_total');
        $shippingConfigured = !empty($this->shippingZones());
        return view('marketplace.cart', compact('cartItems', 'subtotal', 'shippingConfigured'));
    }

    public function updateCart(Request $request, MarketplaceProduct $product)
    {
        $data = $request->validate(['quantity' => 'required|integer|min:1|max:100']);
        if (!$product->is_active || $data['quantity'] > $product->stock) {
            return back()->withErrors(['cart' => 'Jumlah melebihi stok yang tersedia.']);
        }
        $cart = $request->session()->get('marketplace_cart', []);
        $cart[(string) $product->id] = (int) $data['quantity'];
        $request->session()->put('marketplace_cart', $cart);
        return back()->with('cart_success', 'Jumlah barang diperbarui.');
    }

    public function removeFromCart(Request $request, MarketplaceProduct $product)
    {
        $cart = $request->session()->get('marketplace_cart', []);
        unset($cart[(string) $product->id]);
        $request->session()->put('marketplace_cart', $cart);
        return back()->with('cart_success', 'Barang dihapus dari keranjang.');
    }

    public function checkoutCart(Request $request)
    {
        if (!Auth::check()) return redirect()->guest(route('marketplace.account.login'));
        $cartItems = $this->cartItems($request);
        if ($cartItems->isEmpty()) return redirect()->route('marketplace.cart');
        $subtotal = $cartItems->sum('line_total');
        $shippingZones = $this->shippingZones();
        $shippingConfigured = !empty($shippingZones);
        $paymentOptions = $this->paymentOptions();
        $cartAvailable = $cartItems->every(fn ($item) => $item->available);
        return view('marketplace.checkout-cart', compact('cartItems', 'subtotal', 'shippingZones', 'paymentOptions', 'cartAvailable', 'shippingConfigured'));
    }

    public function placeOrder(Request $request)
    {
        if (!Auth::check()) return redirect()->guest(route('marketplace.account.login'));
        $data = $request->validate([
            'customer_name' => 'required|string|max:120',
            'customer_email' => 'nullable|email|max:190',
            'customer_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\-\s]{8,30}$/'],
            'shipping_address' => 'required|string|max:1500',
            'shipping_zone' => 'required|string|max:100',
            'payment_method' => 'required|in:bank_transfer,qris',
        ]);
        abort_unless(array_key_exists($data['payment_method'], $this->paymentOptions()), 422, 'Metode pembayaran belum tersedia.');
        $shippingZones = $this->shippingZones();
        abort_unless(array_key_exists($data['shipping_zone'], $shippingZones), 422, 'Wilayah tujuan belum memiliki tarif ongkir. Silakan pilih wilayah lain atau hubungi admin.');
        $cart = $request->session()->get('marketplace_cart', []);
        if (empty($cart)) return redirect()->route('marketplace.cart');
        $shippingFee = (int) $shippingZones[$data['shipping_zone']];

        $order = DB::transaction(function () use ($cart, $data, $shippingFee) {
            $products = MarketplaceProduct::whereIn('id', array_keys($cart))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $subtotal = 0;
            $itemQuantity = 0;
            foreach ($cart as $productId => $quantity) {
                $product = $products->get((int) $productId);
                if (!$product || !$product->is_active || $quantity < 1 || $quantity > $product->stock) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['cart' => 'Stok atau ketersediaan barang berubah. Periksa kembali keranjang Anda.']);
                }
                $subtotal += $product->price * $quantity;
                $itemQuantity += $quantity;
            }
            $order = MarketplaceOrder::create([
                'marketplace_product_id' => null,
                'public_token' => Str::random(48),
                'order_id' => 'SHOP-' . now()->format('ymdHis') . '-' . Str::upper(Str::random(8)),
                'product_name' => $itemQuantity . ' barang',
                'unit_price' => $subtotal,
                'quantity' => $itemQuantity,
                'subtotal_amount' => $subtotal,
                'shipping_amount' => $shippingFee,
                'shipping_method' => 'Pengiriman reguler · ' . $data['shipping_zone'],
                'total_amount' => $subtotal + $shippingFee,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'],
                'shipping_address' => $data['shipping_address'],
                'status' => 'awaiting_payment',
                'payment_status' => 'pending',
                'payment_method' => $data['payment_method'],
                'user_id' => Auth::id(),
            ]);
            foreach ($cart as $productId => $quantity) {
                $product = $products->get((int) $productId);
                $product->decrement('stock', $quantity);
                $order->items()->create([
                    'marketplace_product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $quantity,
                    'line_total' => $product->price * $quantity,
                ]);
            }
            return $order;
        });

        $request->session()->forget('marketplace_cart');
        return redirect()->route('marketplace.checkout', $order->public_token);
    }

    public function updateShippingFee(Request $request)
    {
        $data = $request->validate(['shipping_zones' => 'required|string|max:10000']);
        $zones = [];
        foreach (preg_split('/\R/', $data['shipping_zones']) as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $parts = array_map('trim', explode('|', $line, 2));
            if (count($parts) !== 2 || $parts[0] === '' || mb_strlen($parts[0]) > 100 || !ctype_digit($parts[1]) || (int) $parts[1] < 1 || (int) $parts[1] > 999999999) {
                return back()->withErrors(['shipping_zones' => 'Format tarif salah. Tulis satu wilayah per baris dengan format: Nama wilayah | tarif rupiah.'])->withInput();
            }
            if (array_key_exists($parts[0], $zones)) return back()->withErrors(['shipping_zones' => 'Nama wilayah tidak boleh duplikat.'])->withInput();
            $zones[$parts[0]] = (int) $parts[1];
        }
        if ($zones === []) return back()->withErrors(['shipping_zones' => 'Tambahkan minimal satu wilayah dengan tarif ongkir lebih dari Rp0.'])->withInput();
        PortfolioSetting::updateOrCreate(['key' => 'marketplace_shipping_zones'], ['value' => json_encode($zones, JSON_UNESCAPED_UNICODE)]);
        return back()->with('success', 'Tarif ongkir per wilayah berhasil diperbarui.');
    }

    private function shippingZones(): array
    {
        $value = PortfolioSetting::where('key', 'marketplace_shipping_zones')->value('value');
        $zones = is_string($value) ? json_decode($value, true) : null;
        return is_array($zones) ? $zones : [];
    }

    public function updatePaymentSettings(Request $request)
    {
        $data = $request->validate([
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:60',
            'bank_holder' => 'nullable|string|max:120',
            'qris_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'remove_qris' => 'nullable|boolean',
        ]);
        foreach (['bank_name' => 'marketplace_bank_name', 'bank_account' => 'marketplace_bank_account', 'bank_holder' => 'marketplace_bank_holder'] as $field => $key) {
            PortfolioSetting::updateOrCreate(['key' => $key], ['value' => trim((string) ($data[$field] ?? ''))]);
        }
        if ($request->hasFile('qris_image')) {
            $old = PortfolioSetting::where('key', 'marketplace_qris_image')->value('value');
            $path = $request->file('qris_image')->store('marketplace', 'public');
            PortfolioSetting::updateOrCreate(['key' => 'marketplace_qris_image'], ['value' => $path]);
            if ($old) Storage::disk('public')->delete($old);
        } elseif ($request->boolean('remove_qris')) {
            $old = PortfolioSetting::where('key', 'marketplace_qris_image')->value('value');
            if ($old) Storage::disk('public')->delete($old);
            PortfolioSetting::where('key', 'marketplace_qris_image')->delete();
        }
        return back()->with('success', 'Metode pembayaran manual berhasil diperbarui.');
    }

    public function uploadPaymentProof(Request $request, MarketplaceOrder $order)
    {
        abort_unless(in_array($order->payment_status, ['pending', 'rejected'], true), 404);
        $data = $request->validate(['payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120']);
        if ($order->payment_proof_path) Storage::disk('local')->delete($order->payment_proof_path);
        $path = $data['payment_proof']->store('marketplace-proofs', 'local');
        $order->update(['payment_proof_path' => $path, 'payment_status' => 'proof_submitted', 'payment_note' => null]);
        return back()->with('success', 'Bukti pembayaran terkirim. Admin akan memeriksanya.');
    }

    public function showPaymentProof(MarketplaceOrder $order)
    {
        abort_unless($order->payment_proof_path && Storage::disk('local')->exists($order->payment_proof_path), 404);
        return Storage::disk('local')->response($order->payment_proof_path);
    }

    public function reviewPayment(Request $request, MarketplaceOrder $order)
    {
        $data = $request->validate(['decision' => 'required|in:approve,reject', 'payment_note' => 'nullable|string|max:500']);
        abort_unless($order->payment_status === 'proof_submitted' && $order->payment_proof_path, 422, 'Pesanan belum memiliki bukti pembayaran untuk diperiksa.');
        if ($data['decision'] === 'approve') {
            $order->update(['payment_status' => 'paid', 'status' => 'awaiting_fulfillment', 'paid_at' => now(), 'payment_note' => null]);
            return back()->with('success', 'Pembayaran diverifikasi. Pesanan siap diproses.');
        }
        $order->update(['payment_status' => 'rejected', 'payment_note' => $data['payment_note'] ?: 'Bukti pembayaran belum dapat diverifikasi.']);
        return back()->with('success', 'Bukti pembayaran ditolak. Pelanggan dapat mengirim ulang bukti.');
    }

    private function paymentOptions(): array
    {
        $settings = PortfolioSetting::whereIn('key', ['marketplace_bank_name', 'marketplace_bank_account', 'marketplace_bank_holder', 'marketplace_qris_image'])->pluck('value', 'key');
        $options = [];
        if (filled($settings['marketplace_bank_name'] ?? null) && filled($settings['marketplace_bank_account'] ?? null) && filled($settings['marketplace_bank_holder'] ?? null)) {
            $options['bank_transfer'] = ['name' => $settings['marketplace_bank_name'], 'account' => $settings['marketplace_bank_account'], 'holder' => $settings['marketplace_bank_holder']];
        }
        if (filled($settings['marketplace_qris_image'] ?? null)) $options['qris'] = ['image' => $settings['marketplace_qris_image']];
        return $options;
    }

    private function cartItems(Request $request)
    {
        $cart = $request->session()->get('marketplace_cart', []);
        if (!is_array($cart) || empty($cart)) return collect();
        $products = MarketplaceProduct::whereIn('id', array_keys($cart))->get()->keyBy('id');
        $cart = collect($cart)->filter(fn ($quantity, $productId) => $products->has((int) $productId))->all();
        $request->session()->put('marketplace_cart', $cart);
        return collect($cart)->map(function ($quantity, $productId) use ($products) {
            $product = $products->get((int) $productId);
            if (!$product) return null;
            $quantity = max(1, (int) $quantity);
            return (object) [
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => $product->price * $quantity,
                'available' => $product->is_active && $product->stock >= $quantity,
            ];
        })->filter()->values();
    }

    private function releaseOrderStockAndDelete(MarketplaceOrder $order): void
    {
        DB::transaction(function () use ($order) {
            $order = MarketplaceOrder::with('items')->whereKey($order->id)->lockForUpdate()->first();
            if (!$order) return;
            foreach ($order->items as $item) {
                if ($item->marketplace_product_id) MarketplaceProduct::whereKey($item->marketplace_product_id)->lockForUpdate()->increment('stock', $item->quantity);
            }
            $order->delete();
        });
    }

    public function checkout(MarketplaceOrder $order)
    {
        $order->load('items');
        $whatsapp = preg_replace('/\D+/', '', PortfolioSetting::where('key', 'admin_whatsapp')->value('value') ?: config('portfolio.whatsapp'));
        $settings = PortfolioSetting::whereIn('key', ['marketplace_bank_name', 'marketplace_bank_account', 'marketplace_bank_holder', 'marketplace_qris_image'])->pluck('value', 'key');
        $bankPayment = filled($settings['marketplace_bank_name'] ?? null) && filled($settings['marketplace_bank_account'] ?? null) && filled($settings['marketplace_bank_holder'] ?? null)
            ? ['name' => $settings['marketplace_bank_name'], 'account' => $settings['marketplace_bank_account'], 'holder' => $settings['marketplace_bank_holder']] : null;
        $qrisImage = $settings['marketplace_qris_image'] ?? null;
        return view('marketplace.checkout', compact('order', 'whatsapp', 'bankPayment', 'qrisImage'));
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

}
