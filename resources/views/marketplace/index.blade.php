<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="Lihat dan pesan barang dari Nadhim Alim.">
    <title>Toko · Nadhim Alim</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"><link rel="stylesheet" href="{{ asset('css/marketplace.css') }}?v=2">
</head>
<body>
<header class="shop-nav"><a class="shop-brand" href="{{ route('home') }}">NA<span>.</span></a><form class="shop-search" action="{{ route('marketplace.index') }}" method="GET"><input type="search" name="q" value="{{ $search }}" placeholder="Cari barang yang kamu inginkan..." aria-label="Cari barang"><button type="submit" aria-label="Cari"><i class="bi bi-search"></i></button></form><a class="shop-nav-back" href="{{ route('home') }}"><i class="bi bi-arrow-left"></i> Kembali</a></header>
<main class="shop-wrap">
    <section class="shop-hero"><div class="shop-hero-copy"><span class="shop-kicker">TOKO NADHIM ALIM</span><h1>Barang pilihan,<br><em>langsung untukmu.</em></h1><p>Jelajahi koleksi yang tersedia. Pilih barang, tentukan jumlah, lalu lanjutkan pembayaran dengan aman.</p></div><span class="shop-hero-mark"><i class="bi bi-bag-heart"></i></span></section>
    <div class="shop-benefits"><article><i class="bi bi-shield-check"></i><span><strong>Checkout aman</strong><small>Pembayaran melalui Midtrans</small></span></article><article><i class="bi bi-box-seam"></i><span><strong>Stok diperbarui</strong><small>Ketersediaan barang terlihat langsung</small></span></article><article><i class="bi bi-chat-dots"></i><span><strong>Bantuan personal</strong><small>Admin siap membantu pesananmu</small></span></article></div>
    <section class="shop-catalog"><div class="shop-section-heading"><div><span class="shop-kicker">ETALASE</span><h2>{{ $search ? 'Hasil pencarian' : ($category ?: 'Semua barang') }}</h2></div><span>{{ $products->total() }} barang</span></div>
        @if($categories->isNotEmpty())<nav class="shop-categories" aria-label="Kategori barang"><a class="shop-category-filter {{ $category === '' ? 'active' : '' }}" href="{{ route('marketplace.index', array_filter(['q' => $search])) }}">Semua</a>@foreach($categories as $item)<a class="shop-category-filter {{ $category === $item ? 'active' : '' }}" href="{{ route('marketplace.index', array_filter(['q' => $search, 'kategori' => $item])) }}">{{ $item }}</a>@endforeach</nav>@endif
        <div class="shop-grid">
            @forelse($products as $product)
                <article class="shop-card">
                    <a class="shop-card-image" href="{{ route('marketplace.show', $product->slug) }}">
                        @if($product->image)
                            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" loading="lazy">
                        @else
                            <div class="shop-image-placeholder"><i class="bi bi-bag"></i></div>
                        @endif
                        @if($product->category)
                            <span class="shop-category">{{ $product->category }}</span>
                        @endif
                    </a>
                    <div class="shop-card-body"><h3><a href="{{ route('marketplace.show', $product->slug) }}">{{ $product->name }}</a></h3><p>{{ \Illuminate\Support\Str::limit($product->description, 105) }}</p><div class="shop-card-footer"><strong>Rp {{ number_format($product->price, 0, ',', '.') }}</strong><a href="{{ route('marketplace.show', $product->slug) }}" aria-label="Lihat {{ $product->name }}"><i class="bi bi-arrow-up-right"></i></a></div><small class="stock-note">{{ $product->stock > 0 ? 'Stok tersedia' : 'Stok habis' }}</small></div>
                </article>
            @empty
                <div class="shop-empty"><i class="bi bi-bag"></i><h3>Etalase sedang disiapkan.</h3><p>Barang yang tersedia akan muncul di sini.</p></div>
            @endforelse
        </div>
        @if($products->hasPages())<nav class="shop-pagination" aria-label="Navigasi halaman">@if($products->previousPageUrl())<a class="shop-page-link" href="{{ $products->previousPageUrl() }}">← Sebelumnya</a>@endif<span>Halaman {{ $products->currentPage() }} dari {{ $products->lastPage() }}</span>@if($products->nextPageUrl())<a class="shop-page-link" href="{{ $products->nextPageUrl() }}">Berikutnya →</a>@endif</nav>@endif
    </section>
</main>
<footer class="shop-footer"><span>NA<span>.</span> · Marketplace</span><span>© {{ now()->year }} Nadhim Alim</span></footer>
</body>
</html>
