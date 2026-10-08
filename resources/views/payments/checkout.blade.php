<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pembayaran proyek · Nadhim Alim</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/payment.css') }}">
    @if($payment->snap_token && $payment->status !== 'paid')
        <script src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    @endif
</head>
<body>
    <main class="payment-shell">
        <a class="payment-brand" href="{{ route('home') }}">NA<span>.</span></a>
        <section class="payment-card">
            <div class="payment-mark"><span class="payment-mark-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 7.5h16v11H4zM4 10h16M8 15h3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span>PEMBAYARAN PROYEK</span></div>
            @if($payment->status === 'paid')
                <div class="payment-state-icon success">✓</div><p class="payment-eyebrow">PEMBAYARAN BERHASIL</p><h1>Terima kasih.</h1><p class="payment-intro">Tagihan ini sudah lunas. Konfirmasi pembayaran telah diterima.</p>
            @elseif($payment->expires_at?->isPast() || in_array($payment->status, ['expired','cancelled','denied']))
                <div class="payment-state-icon muted">!</div><p class="payment-eyebrow">LINK TIDAK AKTIF</p><h1>Hubungi admin.</h1><p class="payment-intro">Link pembayaran ini sudah tidak aktif. Silakan minta tautan tagihan yang baru.</p>
            @else
                <p class="payment-eyebrow">TAGIHAN UNTUK {{ mb_strtoupper($payment->customer_name) }}</p><h1>Selesaikan pembayaran.</h1><p class="payment-intro">Pembayaran aman diproses melalui Midtrans.</p>
            @endif
            <div class="payment-summary"><div><span>Proyek</span><strong>{{ $payment->workTask->title }}</strong></div><div><span>Nomor tagihan</span><strong>{{ $payment->order_id }}</strong></div><div class="payment-total"><span>Total pembayaran</span><strong>Rp {{ number_format($payment->gross_amount, 0, ',', '.') }}</strong></div></div>
            @if($payment->status !== 'paid' && !$payment->expires_at?->isPast() && !in_array($payment->status, ['expired','cancelled','denied']))
                @if($payment->snap_token)
                    <button class="pay-button" id="pay-button" type="button">Lanjutkan pembayaran <span>→</span></button>
                    <p class="payment-hint">Transfer bank, kartu, e-wallet, dan metode lain tersedia sesuai pilihan checkout.</p>
                @else
                    <div class="payment-unavailable">Sesi checkout belum tersedia. Silakan hubungi admin untuk bantuan.</div>
                @endif
            @endif
            <div class="payment-feedback" id="payment-feedback" role="status" aria-live="polite"></div>
        </section>
        <footer><span>Pembayaran diproses dengan aman oleh Midtrans</span><a href="{{ route('home') }}">Kembali ke situs</a></footer>
    </main>
    @if($payment->snap_token && $payment->status !== 'paid')
    <script>
        const payButton = document.getElementById('pay-button');
        const paymentFeedback = document.getElementById('payment-feedback');
        payButton?.addEventListener('click', () => {
            payButton.disabled = true;
            paymentFeedback.textContent = '';
            window.snap.pay(@json($payment->snap_token), {
                onSuccess: () => { paymentFeedback.textContent = 'Pembayaran diterima. Memastikan status tagihan…'; setTimeout(() => window.location.reload(), 2500); },
                onPending: () => { paymentFeedback.textContent = 'Pembayaran sedang menunggu konfirmasi. Halaman ini akan diperbarui.'; setTimeout(() => window.location.reload(), 5000); },
                onError: () => { paymentFeedback.textContent = 'Pembayaran belum berhasil. Anda dapat mencoba kembali.'; payButton.disabled = false; },
                onClose: () => { payButton.disabled = false; },
            });
        });
    </script>
    @endif
</body>
</html>
