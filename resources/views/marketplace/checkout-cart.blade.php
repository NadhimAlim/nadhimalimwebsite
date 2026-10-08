<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Checkout · Toko Nadhim Alim</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/marketplace.css') }}?v=10">
    <link rel="stylesheet" href="{{ asset('css/feedback.css') }}?v=1">
</head>
<body>
<header class="shop-nav"><a class="shop-brand" href="{{ route('home') }}">NA<span>.</span></a><span class="checkout-steps"><b>1</b> Keranjang <i class="bi bi-chevron-right"></i> <b class="current">2</b> Pengiriman & pembayaran</span><a class="shop-nav-back" href="{{ route('marketplace.cart') }}"><i class="bi bi-arrow-left"></i> Keranjang</a></header>
<main class="shop-wrap checkout-page">
    <div class="cart-page-title"><div><span class="shop-kicker">LANGKAH TERAKHIR</span><h1>Checkout</h1><p>Isi alamat penerima dan periksa total pembayaran.</p></div></div>
    @if(session('success'))<div class="shop-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="shop-error" role="alert">{{ $errors->first() }}</div>@endif
    <div class="checkout-cart-layout">
        <form class="checkout-customer-form" action="{{ route('marketplace.checkout.place') }}" method="POST" data-loading-title="Membuat pesanan..." data-loading-description="Menyimpan pesanan dan mengamankan stok semua barang.">
            @csrf<h2><i class="bi bi-person-vcard"></i> Informasi penerima</h2>
            <label>Nama lengkap<input name="customer_name" required maxlength="120" autocomplete="name" value="{{ old('customer_name', auth()->user()->name) }}" placeholder="Nama penerima paket"></label>
            <div class="checkout-customer-row"><label>Email <span>Opsional</span><input type="email" name="customer_email" maxlength="190" autocomplete="email" value="{{ old('customer_email', auth()->user()->email) }}" placeholder="nama@email.com"></label><label>Nomor WhatsApp<input type="tel" name="customer_phone" required maxlength="30" autocomplete="tel" value="{{ old('customer_phone') }}" placeholder="08xx xxxx xxxx"></label></div>
            <label>Wilayah tujuan<select id="shipping-zone" name="shipping_zone" required data-shipping-zone>@foreach($shippingZones as $zone => $fee)<option value="{{ $zone }}" data-fee="{{ $fee }}" @selected(old('shipping_zone') === $zone)>{{ $zone }} · Rp {{ number_format($fee, 0, ',', '.') }}</option>@endforeach<option value="" data-fee="" @selected(!old('shipping_zone'))>Pilih kecamatan/kota tujuan</option></select></label>
            <label>Alamat lengkap<textarea name="shipping_address" required maxlength="1500" rows="4" autocomplete="street-address" placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan, kota, provinsi, dan kode pos">{{ old('shipping_address') }}</textarea></label>
            <div class="checkout-shipping-choice"><i class="bi bi-truck"></i><span><strong>Pengiriman reguler dari Piyungan</strong><small data-shipping-caption>Pilih wilayah tujuan untuk melihat ongkir</small></span><strong data-shipping-fee>—</strong></div>
            <fieldset class="checkout-payment-options"><legend>Pilih pembayaran manual</legend>
                @if(isset($paymentOptions['bank_transfer']))<label><input type="radio" name="payment_method" value="bank_transfer" @checked(old('payment_method')==='bank_transfer') required><span><strong>Transfer bank</strong><small>{{ $paymentOptions['bank_transfer']['name'] }} · a.n. {{ $paymentOptions['bank_transfer']['holder'] }}</small></span></label>@endif
                @if(isset($paymentOptions['qris']))<label><input type="radio" name="payment_method" value="qris" @checked(old('payment_method')==='qris') required><span><strong>QRIS</strong><small>Pindai kode QR toko setelah pesanan dibuat.</small></span></label>@endif
            </fieldset>
            @if(!$shippingConfigured)<div class="shop-unavailable"><i class="bi bi-info-circle"></i><span>Tarif per wilayah belum diatur. Admin perlu menambahkan tarif tujuan di dashboard.</span></div>@endif
            @if(empty($paymentOptions))<div class="shop-unavailable"><i class="bi bi-info-circle"></i><span>Admin belum mengatur rekening bank atau kode QRIS. Silakan hubungi admin.</span></div>@endif
            @if(!$cartAvailable)<div class="shop-error">Ada barang yang stoknya berubah. Kembali ke keranjang untuk menyesuaikan jumlah.</div>@endif
            <button class="shop-buy-button" type="submit" @disabled(empty($paymentOptions) || !$cartAvailable || !$shippingConfigured)>Buat pesanan <i class="bi bi-arrow-right"></i></button>
            <small class="shop-safe-note"><i class="bi bi-shield-check"></i> Ongkir dihitung dari wilayah tujuan sebelum pesanan dibuat.</small>
        </form>
        <aside class="checkout-cart-summary"><span class="shop-kicker">PESANAN</span><h2>{{ $cartItems->count() }} jenis barang</h2>
            <div class="checkout-cart-lines">@foreach($cartItems as $item)<article><span class="checkout-line-thumb">@if($item->product->image)<img src="{{ asset('storage/'.$item->product->image) }}" alt="{{ $item->product->name }}">@else<i class="bi bi-bag"></i>@endif</span><span class="checkout-line-name"><strong>{{ $item->product->name }}</strong><small>{{ $item->quantity }} × Rp {{ number_format($item->product->price, 0, ',', '.') }}</small></span><strong>Rp {{ number_format($item->line_total, 0, ',', '.') }}</strong></article>@endforeach</div>
            <div class="checkout-price-row"><span>Subtotal barang</span><strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong></div><div class="checkout-price-row"><span>Ongkos kirim</span><strong data-summary-shipping>—</strong></div><div class="checkout-grand-total"><span>Total pembayaran</span><strong data-summary-total data-subtotal="{{ $subtotal }}">Rp {{ number_format($subtotal, 0, ',', '.') }}</strong></div><p><i class="bi bi-info-circle"></i> Ongkir diperbarui otomatis setelah wilayah tujuan dipilih.</p>
        </aside>
    </div>
</main>
<footer class="shop-footer"><a href="{{ route('marketplace.cart') }}">← Kembali ke keranjang</a><span>NA<span>.</span> · Marketplace</span></footer>
<script>
(() => {
    const zone = document.querySelector('[data-shipping-zone]');
    const feeLabel = document.querySelector('[data-shipping-fee]');
    const caption = document.querySelector('[data-shipping-caption]');
    const summaryFee = document.querySelector('[data-summary-shipping]');
    const total = document.querySelector('[data-summary-total]');
    const format = (value) => `Rp ${new Intl.NumberFormat('id-ID').format(value)}`;
    const update = () => {
        const fee = Number(zone?.selectedOptions[0]?.dataset.fee);
        const valid = zone?.value !== '' && Number.isFinite(fee);
        feeLabel.textContent = valid ? (format(fee)) : '—';
        summaryFee.textContent = valid ? (format(fee)) : 'Pilih wilayah';
        caption.textContent = valid ? `Tarif tujuan ${zone.value}` : 'Pilih wilayah tujuan untuk melihat ongkir';
        total.textContent = format(Number(total.dataset.subtotal || 0) + (valid ? fee : 0));
    };
    zone?.addEventListener('change', update);
    update();
})();
</script>
<script src="{{ asset('js/feedback.js') }}" defer></script>
</body></html>
