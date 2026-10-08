<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="{{ $news->excerpt }}">
    <meta property="og:type" content="article"><meta property="og:title" content="{{ $news->title }}"><meta property="og:description" content="{{ $news->excerpt }}">
    @if($news->image)<meta property="og:image" content="{{ asset('storage/'.$news->image) }}">@endif
    <title>{{ $news->title }} — Nadhim Alim</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/portfolio.css') }}"><link rel="stylesheet" href="{{ asset('css/news.css') }}">
</head>
<body class="news-article-page">
    <header class="news-site-header"><nav class="container news-site-nav"><a class="brand" href="{{ route('home') }}">NA<span>.</span></a><span class="news-site-label">CATATAN & KABAR</span><a class="news-back" href="{{ route('news.index') }}"><i class="bi bi-arrow-left"></i> Semua berita</a></nav></header>
    <main class="news-article container">
        <div class="news-article-breadcrumb"><a href="{{ route('home') }}">Beranda</a><i class="bi bi-chevron-right"></i><a href="{{ route('news.index') }}">Berita</a><i class="bi bi-chevron-right"></i><span>Artikel</span></div>
        <article class="news-article-shell">
            <header class="news-article-header">
                <div class="news-article-kicker"><span>JURNAL NADHIM ALIM</span><i></i><time datetime="{{ $news->published_at->toIso8601String() }}">{{ $news->published_at->format('d M Y') }}</time><span>{{ max(1, (int) ceil(str_word_count(strip_tags($news->content)) / 220)) }} menit baca</span></div>
                <h1>{{ $news->title }}</h1>
                <p class="news-article-excerpt">{{ $news->excerpt }}</p>
                <div class="news-author"><span class="news-author-avatar">NA<span>.</span></span><span><strong>Nadhim Alim</strong><small>Penulis</small></span></div>
            </header>
            @if($news->image)
                <img class="news-article-image" src="{{ asset('storage/'.$news->image) }}" alt="{{ $news->title }}">
            @else
                <div class="news-article-placeholder"><span>NA<span>.</span></span><small>JURNAL NADHIM ALIM</small></div>
            @endif
            <div class="news-article-layout">
                <div class="news-article-body">{!! nl2br(e($news->content)) !!}</div>
                <aside class="news-article-aside"><span class="news-aside-label">BAGIKAN ARTIKEL</span><a href="https://wa.me/?text={{ urlencode($news->title . ' ' . request()->url()) }}" target="_blank" rel="noreferrer"><i class="bi bi-whatsapp"></i> WhatsApp</a><a href="mailto:?subject={{ urlencode($news->title) }}&body={{ urlencode(request()->url()) }}"><i class="bi bi-envelope"></i> Email</a><a href="{{ route('news.index') }}"><i class="bi bi-journals"></i> Semua tulisan</a></aside>
            </div>
        </article>

        @if($relatedNews->isNotEmpty())
        <section class="related-news"><div class="related-news-heading"><span class="news-archive-kicker">LANJUTKAN MEMBACA</span><h2>Cerita <em>lainnya.</em></h2></div><div class="news-grid">
            @foreach($relatedNews as $article)
            <article class="news-card">
                @if($article->image)<a class="news-image" href="{{ route('news.show', $article) }}"><img src="{{ asset('storage/'.$article->image) }}" alt="{{ $article->title }}"></a>@else<a class="news-image news-image-placeholder" href="{{ route('news.show', $article) }}"><span>NA<span>.</span></span><small>JURNAL NADHIM ALIM</small></a>@endif
                <div class="news-card-body"><div class="news-card-meta"><span>ARTIKEL</span><time datetime="{{ $article->published_at->toIso8601String() }}">{{ $article->published_at->format('d M Y') }}</time></div><h2><a href="{{ route('news.show', $article) }}">{{ $article->title }}</a></h2><p>{{ $article->excerpt }}</p><a class="news-read-more" href="{{ route('news.show', $article) }}">Baca cerita <i class="bi bi-arrow-up-right"></i></a></div>
            </article>
            @endforeach
        </div></section>
        @endif
    </main>
    <footer class="news-article-footer"><a href="{{ route('home') }}">NA<span>.</span></a><span>© {{ date('Y') }} Nadhim Alim</span><a href="{{ route('home') }}#kontak">Mari ngobrol <i class="bi bi-arrow-up-right"></i></a></footer>
</body>
</html>
