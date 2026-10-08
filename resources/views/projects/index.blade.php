<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="Kumpulan proyek dan karya yang dibuat oleh Nadhim Alim.">
    <title>Portofolio Karya — Nadhim Alim</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/portfolio.css') }}"><link rel="stylesheet" href="{{ asset('css/news.css') }}"><link rel="stylesheet" href="{{ asset('css/pagination.css') }}"><link rel="stylesheet" href="{{ asset('css/project-archive.css') }}">
</head>
<body class="news-archive-page">
    <header class="news-site-header"><nav class="container news-site-nav"><a class="brand" href="{{ route('home') }}">NA<span>.</span></a><span class="news-site-label">PILIHAN KARYA</span><a class="news-back" href="{{ route('home') }}#karya"><i class="bi bi-arrow-left"></i> Portofolio</a></nav></header>
    <main class="news-archive container">
        <div class="news-archive-heading"><span class="news-archive-kicker">PORTOFOLIO NADHIM ALIM</span><h1>Karya jadi <em>cerita.</em></h1><p>Kumpulan proyek, ide, dan hal-hal yang telah diwujudkan.</p><span class="news-archive-total">{{ $projects->total() }} karya</span></div>
        <div class="news-archive-grid project-archive-grid">
            @forelse($projects as $index => $project)
            <article class="news-card project-archive-card">
                @if($project->link)<a class="news-image project-archive-image" href="{{ $project->link }}" target="_blank" rel="noreferrer"><span class="project-archive-number">{{ str_pad($projects->firstItem() + $index, 2, '0', STR_PAD_LEFT) }}</span><span class="news-image-label">LIHAT PROYEK <i class="bi bi-arrow-up-right"></i></span>@if($project->image)<img src="{{ asset('storage/'.$project->image) }}" alt="{{ $project->title }}">@else<span class="project-archive-placeholder"><i class="bi bi-asterisk"></i><small>{{ $project->category }}</small></span>@endif</a>
                @else<a class="news-image project-archive-image" href="{{ route('home') }}#kontak"><span class="project-archive-number">{{ str_pad($projects->firstItem() + $index, 2, '0', STR_PAD_LEFT) }}</span>@if($project->image)<img src="{{ asset('storage/'.$project->image) }}" alt="{{ $project->title }}">@else<span class="project-archive-placeholder"><i class="bi bi-asterisk"></i><small>{{ $project->category }}</small></span>@endif</a>@endif
                <div class="news-card-body"><div class="news-card-meta"><span>{{ $project->category }}</span><time>{{ $project->created_at->format('Y') }}</time></div><h2>{{ $project->title }}</h2><p>{{ $project->description }}</p>@if($project->link)<a class="news-read-more" href="{{ $project->link }}" target="_blank" rel="noreferrer">Lihat proyek <i class="bi bi-arrow-up-right"></i></a>@endif</div>
            </article>
            @empty
            <div class="news-empty"><i class="bi bi-camera"></i><h3>Belum ada karya.</h3><p>Proyek baru akan hadir di sini.</p></div>
            @endforelse
        </div>
        @if($projects->hasPages())<div class="news-archive-pagination">{{ $projects->links('pagination.portfolio') }}</div>@endif
        <div class="news-archive-footer"><a class="news-back" href="{{ route('home') }}#karya"><i class="bi bi-arrow-left"></i> Kembali ke halaman utama</a><span>© {{ date('Y') }} Nadhim Alim</span></div>
    </main>
</body>
</html>
