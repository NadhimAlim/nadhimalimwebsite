<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $product->name }} · Toko Nadhim Alim</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"><link rel="stylesheet" href="{{ asset('css/marketplace.css') }}?v=2">
</head>
<body>
<header class="shop-nav"><a class="shop-brand" href="{{ route('home') }}">NA<span>.</span></a><form class="shop-search" action="{{ route('marketplace.index') }}" method="GET"><input type="search" name="q" placeholder="Cari barang lain..." aria-label="Cari barang"><button type="submit" aria-label="Cari"><i class="bi bi-search"></i></button></form><a class="shop-nav-back" href="{{ route('marketplace.index') }}"><i class="bi bi-arrow-left"></i> Kembali ke toko</a></header>
<main class="shop-wrap product-page"><div class="product-breadcrumb"><a href="{{ route('marketplace.index') }}">Toko</a> <i class="bi bi-chevron-right"></i> {{ $product->category ?: 'Detail barang' }} <i class="bi bi-chevron-right"></i> {{ $product->name }}</div><div class="product-detail-grid">
    <div class="product-detail-gallery"><div class="product-thumbnails"><div class="product-thumbnail">@if($product->image)<img src="{{ asset('storage/'.$product->image) }}" alt="">@else<i class="bi bi-bag"></i>@endif</div></div><div class="product-detail-image">@if($product->image)<img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">@else<div class="shop-image-placeholder"><i class="bi bi-bag"></i></div>@endif</div></div>
    <article class="product-detail-copy"><span class="shop-kicker">{{ $product->category ?: 'BARANG PILIHAN' }}</span><h1>{{ $product->name }}</h1><strong class="product-price">Rp {{ number_format($product->price, 0, ',', '.') }}</strong><p class="product-description">{{ $product->description }}</p><div class="product-stock"><i class="bi bi-box-seam"></i>{{ $product->stock > 0 ? $product->stock.' tersedia' : 'Stok sedang habis' }}</div>
        @if($errors->any())<div class="shop-error" role="alert">{{ $errors->first() }}</div>@endif
        @if($product->stock > 0 && $paymentEnabled)
        <form class="shop-order-form" action="{{ route('marketplace.order', $product->slug) }}" method="POST" data-loading-title="Menyiapkan pembayaran..." data-loading-description="Membuat tagihan dan mengamankan stok barang.">@csrf
            <h2>Data pemesan</h2><label>Nama lengkap<input name="customer_name" required maxlength="120" autocomplete="name" value="{{ old('customer_name') }}" placeholder="Nama penerima"></label>
            <div class="shop-form-row"><label>Email <span>Opsional</span><input type="email" name="customer_email" maxlength="190" autocomplete="email" value="{{ old('customer_email') }}" placeholder="nama@email.com"></label><label>Nomor WhatsApp<input type="tel" name="customer_phone" required maxlength="30" autocomplete="tel" value="{{ old('customer_phone') }}" placeholder="08xx xxxx xxxx"></label></div>
            <label>Alamat pengiriman<textarea name="shipping_address" required maxlength="1500" rows="3" autocomplete="street-address" placeholder="Alamat lengkap, kota, kode pos">{{ old('shipping_address') }}</textarea></label>
            <label>Jumlah<select name="quantity">@for($quantity=1; $quantity<=min(20,$product->stock); $quantity++)<option value="{{ $quantity }}" @selected(old('quantity',1)==$quantity)>{{ $quantity }} barang</option>@endfor</select></label>
            <div class="shipping-note"><i class="bi bi-truck"></i><span>Biaya dan metode pengiriman akan dikonfirmasi admin melalui WhatsApp setelah pesanan masuk.</span></div><button class="shop-buy-button" type="submit">Lanjut ke pembayaran <i class="bi bi-arrow-right"></i></button><small class="shop-safe-note"><i class="bi bi-shield-lock"></i> Pembayaran diproses dengan aman melalui Midtrans.</small>
        </form>
        @elseif($product->stock <= 0)<div class="sold-out-note">Barang ini sedang habis. Silakan cek kembali nanti.</div>
        @else<div class="shop-unavailable"><i class="bi bi-info-circle"></i><span>Pembayaran online sedang disiapkan. Hubungi admin untuk menanyakan barang ini.</span></div>@if($whatsapp)<a class="checkout-contact" href="https://wa.me/{{ $whatsapp }}?text={{ rawurlencode('Halo, saya tertarik dengan '.$product->name) }}" target="_blank" rel="noreferrer">Tanyakan lewat WhatsApp <i class="bi bi-arrow-up-right"></i></a>@endif @endif
    </article>
</div></main><footer class="shop-footer"><a href="{{ route('marketplace.index') }}">← Kembali ke toko</a><span>NA<span>.</span> · Marketplace</span></footer>@if($paymentEnabled)<script src="{{ asset('js/feedback.js') }}" defer></script>@endif
</body></html>
