<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pesanan {{ $order->order_id }} · Nadhim Alim</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/marketplace.css') }}?v=2">
    @if($order->snap_token && $order->payment_status !== 'paid')
        <script src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    @endif
</head>
<body>
<main class="order-checkout"><a class="shop-brand" href="{{ route('home') }}">NA<span>.</span></a>
    <section class="order-checkout-card"><span class="shop-kicker">RINGKASAN PESANAN</span>
        @if($order->payment_status === 'paid')
            <div class="order-success-icon"><i class="bi bi-check-lg"></i></div><h1>Pembayaran berhasil.</h1><p class="checkout-intro">Pesanan Anda diterima dan akan segera disiapkan. Admin akan menghubungi Anda terkait pengiriman.</p>
        @elseif(in_array($order->payment_status, ['expired', 'cancelled', 'denied']))
            <h1>Pesanan tidak aktif.</h1><p class="checkout-intro">Pembayaran belum berhasil. Silakan kembali ke toko dan buat pesanan baru.</p>
        @else
            <h1>Satu langkah lagi.</h1><p class="checkout-intro">Periksa kembali pesanan dan alamat sebelum menyelesaikan pembayaran.</p>
        @endif
        <div class="checkout-order-item"><span><strong>{{ $order->product_name }}</strong><small>{{ $order->quantity }} × Rp {{ number_format($order->unit_price, 0, ',', '.') }}</small></span><strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></div>
        <div class="checkout-address"><span>Dikirim kepada</span><strong>{{ $order->customer_name }}</strong><small>{{ $order->customer_phone }} · {{ $order->shipping_address }}</small></div>
        <div class="checkout-total"><span>Total pembayaran</span><strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></div>
        @if($order->payment_status !== 'paid' && !in_array($order->payment_status, ['expired', 'cancelled', 'denied']))
            @if($order->snap_token)
                <button class="shop-buy-button" id="shop-pay-button">Bayar dengan Midtrans <i class="bi bi-arrow-right"></i></button><p class="checkout-feedback" id="checkout-feedback" role="status" aria-live="polite"></p>
            @else
                <div class="shop-error">Sesi pembayaran tidak tersedia. Hubungi admin.</div>
            @endif
        @endif
    </section>
    @if($whatsapp)
        <a class="checkout-contact" href="https://wa.me/{{ $whatsapp }}?text={{ rawurlencode('Halo, saya ingin menanyakan pesanan '.$order->order_id) }}" target="_blank" rel="noreferrer">Hubungi admin via WhatsApp <i class="bi bi-arrow-up-right"></i></a>
    @endif
    <a class="checkout-return" href="{{ route('marketplace.index') }}">Kembali ke toko</a>
</main>
@if($order->snap_token && $order->payment_status !== 'paid')
    <script>
        const shopPayButton = document.getElementById('shop-pay-button');
        const checkoutFeedback = document.getElementById('checkout-feedback');
        shopPayButton?.addEventListener('click', () => {
            shopPayButton.disabled = true;
            window.snap.pay(@json($order->snap_token), {
                onSuccess: () => { checkoutFeedback.textContent = 'Pembayaran diterima. Memastikan status pesanan…'; setTimeout(() => location.reload(), 2500); },
                onPending: () => { checkoutFeedback.textContent = 'Pembayaran menunggu konfirmasi. Halaman akan diperbarui.'; setTimeout(() => location.reload(), 5000); },
                onError: () => { checkoutFeedback.textContent = 'Pembayaran belum berhasil. Silakan coba kembali.'; shopPayButton.disabled = false; },
                onClose: () => { shopPayButton.disabled = false; },
            });
        });
    </script>
@endif
</body>
</html>
