<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pesanan {{ $order->order_id }} · Nadhim Alim</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/marketplace.css') }}?v=6">
<link rel="stylesheet" href="{{ asset('css/feedback.css') }}?v=1"></head>
<body>
<main class="order-checkout"><a class="shop-brand" href="{{ route('home') }}">NA<span>.</span></a>
    <section class="order-checkout-card"><span class="shop-kicker">PESANAN {{ $order->order_id }}</span>
        @if(session('success'))<div class="shop-unavailable" role="status">{{ session('success') }}</div>@endif
        @if($order->payment_status === 'paid')
            <div class="order-success-icon"><i class="bi bi-check-lg"></i></div><h1>Pembayaran terverifikasi.</h1><p class="checkout-intro">Pesanan Anda diterima dan akan segera disiapkan. Admin akan menghubungi Anda terkait pengiriman.</p>
        @elseif($order->payment_status === 'proof_submitted')
            <h1>Bukti pembayaran diterima.</h1><p class="checkout-intro">Admin sedang memeriksa pembayaran Anda. Status pesanan akan diperbarui setelah verifikasi.</p>
        @else
            <h1>Selesaikan pembayaran.</h1><p class="checkout-intro">Bayar sesuai total pesanan dengan {{ $order->payment_method === 'qris' ? 'QRIS' : 'transfer bank' }}, lalu unggah bukti pembayaran.</p>
        @endif
        <div class="checkout-order-lines">@forelse($order->items as $item)<div class="checkout-order-item"><span><strong>{{ $item->product_name }}</strong><small>{{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</small></span><strong>Rp {{ number_format($item->line_total, 0, ',', '.') }}</strong></div>@empty<div class="checkout-order-item"><span><strong>{{ $order->product_name }}</strong><small>{{ $order->quantity }} barang</small></span><strong>Rp {{ number_format($order->subtotal_amount ?: $order->total_amount, 0, ',', '.') }}</strong></div>@endforelse</div>
        <div class="checkout-address"><span>Dikirim kepada</span><strong>{{ $order->customer_name }}</strong><small>{{ $order->customer_phone }} · {{ $order->shipping_address }}</small></div>
        <div class="checkout-price-row"><span>Subtotal barang</span><strong>Rp {{ number_format($order->subtotal_amount ?: $order->total_amount, 0, ',', '.') }}</strong></div><div class="checkout-price-row"><span>Ongkos kirim</span><strong>Rp {{ number_format($order->shipping_amount, 0, ',', '.') }}</strong></div><div class="checkout-total"><span>Total pembayaran</span><strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></div>
        @if($order->payment_status === 'rejected')<div class="shop-error">{{ $order->payment_note ?: 'Bukti pembayaran belum dapat diverifikasi.' }} Silakan unggah bukti yang jelas.</div>@endif
        @if($order->payment_status === 'pending' || $order->payment_status === 'rejected')
            @if($order->payment_method === 'bank_transfer' && $bankPayment)<div class="checkout-address"><span>Transfer bank</span><strong>{{ $bankPayment['name'] }} · {{ $bankPayment['account'] }}</strong><small>a.n. {{ $bankPayment['holder'] }}</small></div>@elseif($order->payment_method === 'qris' && $qrisImage)<div class="checkout-address"><span>Bayar dengan QRIS</span><img src="{{ asset('storage/'.$qrisImage) }}" alt="Kode QRIS toko" style="display:block;max-width:280px;width:100%;height:auto;margin:14px auto;border-radius:12px"><small>Pindai kode dengan aplikasi pembayaran yang mendukung QRIS.</small></div>@endif
            <form class="checkout-customer-form" action="{{ route('marketplace.payment-proof', $order->public_token) }}" method="POST" enctype="multipart/form-data" data-loading-title="Mengirim bukti pembayaran..." data-loading-description="Bukti pembayaran Anda sedang diunggah dengan aman.">@csrf<label>Unggah bukti pembayaran<input type="file" name="payment_proof" accept="image/jpeg,image/png,image/webp" required></label><button class="shop-buy-button" type="submit">Kirim bukti pembayaran <i class="bi bi-arrow-up-right"></i></button></form>
        @elseif($order->payment_status === 'proof_submitted')<div class="checkout-address"><span>Status verifikasi</span><strong>Menunggu pemeriksaan admin</strong><small>Stok sudah dicadangkan sambil pembayaran diperiksa.</small></div>@endif
    </section>
    @if($whatsapp)<a class="checkout-contact" href="https://wa.me/{{ $whatsapp }}?text={{ rawurlencode('Halo, saya ingin menanyakan pesanan '.$order->order_id) }}" target="_blank" rel="noreferrer">Hubungi admin via WhatsApp <i class="bi bi-arrow-up-right"></i></a>@endif
    <a class="checkout-return" href="{{ route('marketplace.index') }}">Kembali ke toko</a>
</main><script src="{{ asset('js/feedback.js') }}" defer></script>
</body>
</html>
