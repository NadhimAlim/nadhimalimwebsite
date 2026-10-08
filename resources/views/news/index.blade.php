<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="Berita, catatan, dan artikel terbaru dari Nadhim Alim.">
    <title>Berita & Artikel — Nadhim Alim</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/portfolio.css') }}"><link rel="stylesheet" href="{{ asset('css/news.css') }}"><link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
</head>
<body class="news-archive-page">
    <header class="news-site-header"><nav class="container news-site-nav"><a class="brand" href="{{ route('home') }}">NA<span>.</span></a><span class="news-site-label">CATATAN & KABAR</span><a class="news-back" href="{{ route('home') }}"><i class="bi bi-arrow-left"></i> Portofolio</a></nav></header>
    <main class="news-archive container">
        <div class="news-archive-heading"><span class="news-archive-kicker">JURNAL NADHIM ALIM</span><h1>Berita & <em>cerita.</em></h1><p>Pemikiran, proses, dan kabar terbaru dari perjalanan berkarya.</p><span class="news-archive-total">{{ $news->total() }} tulisan diterbitkan</span></div>
        <div class="news-archive-grid">
            @forelse($news as $article)
            <article class="news-card">
                @if($article->image)
                    <a class="news-image" href="{{ route('news.show', $article) }}"><img src="{{ asset('storage/'.$article->image) }}" alt="{{ $article->title }}"><span class="news-image-label">BACA ARTIKEL <i class="bi bi-arrow-up-right"></i></span></a>
                @else
                    <a class="news-image news-image-placeholder" href="{{ route('news.show', $article) }}"><span>NA<span>.</span></span><small>JURNAL NADHIM ALIM</small></a>
                @endif
                <div class="news-card-body"><div class="news-card-meta"><span>ARTIKEL</span><time datetime="{{ $article->published_at->toIso8601String() }}">{{ $article->published_at->format('d M Y') }}</time></div><h2><a href="{{ route('news.show', $article) }}">{{ $article->title }}</a></h2><p>{{ $article->excerpt }}</p><a class="news-read-more" href="{{ route('news.show', $article) }}">Baca cerita <i class="bi bi-arrow-up-right"></i></a></div>
            </article>
            @empty
            <div class="news-empty"><i class="bi bi-journal-text"></i><h3>Belum ada tulisan.</h3><p>Berita terbaru akan hadir di sini.</p></div>
            @endforelse
        </div>
        @if($news->hasPages())<div class="news-archive-pagination">{{ $news->links('pagination.portfolio') }}</div>@endif
        <div class="news-archive-footer"><a class="news-back" href="{{ route('home') }}"><i class="bi bi-arrow-left"></i> Kembali ke halaman utama</a><span>© {{ date('Y') }} Nadhim Alim</span></div>
    </main>
</body>
</html>
