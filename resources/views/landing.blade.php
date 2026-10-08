<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f7f4"><meta name="description" content="Portofolio Nadhim Alim — kreator digital dan web developer.">
    <title>Nadhim Alim — Kreator Digital & Web Developer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"><link rel="stylesheet" href="{{ asset('css/portfolio.css') }}"><link rel="stylesheet" href="{{ asset('css/pricing.css') }}"><link rel="stylesheet" href="{{ asset('css/pagination.css') }}"><link rel="stylesheet" href="{{ asset('css/profile.css') }}"><link rel="stylesheet" href="{{ asset('css/education.css') }}"><link rel="stylesheet" href="{{ asset('css/news.css') }}"><link rel="stylesheet" href="{{ asset('css/footer.css') }}"><link rel="stylesheet" href="{{ asset('css/feedback.css') }}"><link rel="stylesheet" href="{{ asset('css/consultation.css') }}">
</head>
<body>
@if($isPreview)
<div class="preview-toolbar"><span><i class="bi bi-eye"></i> Mode pratinjau — perubahan belum dipublikasikan</span><div><a href="{{ route('admin.dashboard.section', 'profile') }}">Kembali mengedit</a><form action="{{ route('admin.profile.preview.discard') }}" method="POST" data-confirm="Buang pratinjau ini? Konten yang sudah dipublikasikan tidak akan berubah.">@csrf @method('DELETE')<button class="preview-discard" type="submit"><i class="bi bi-x-lg"></i> Buang</button></form><form action="{{ route('admin.profile.publish') }}" method="POST">@csrf<button type="submit"><i class="bi bi-send-check"></i> Publikasikan</button></form></div></div>
@endif
<header class="nav-wrap"><nav class="nav container"><a class="brand" href="#home">NA<span>.</span></a><button class="menu-toggle" aria-label="Buka menu" onclick="document.querySelector('.nav-links').classList.toggle('open')"><i class="bi bi-list"></i></button><div class="nav-links"><a href="#tentang">Tentang</a><a href="#karya">Karya</a><a href="#layanan">Layanan & harga</a><a href="#berita">Berita</a><a href="{{ route('marketplace.index') }}">Toko</a><a href="#keahlian">Keahlian</a><a href="#cv">CV</a><a href="#kontak">Kontak</a></div><a class="nav-cta" href="#kontak">Mari ngobrol <i class="bi bi-arrow-up-right"></i></a></nav></header>
<main>
<section class="hero container" id="home"><div class="hero-copy"><div class="eyebrow"><span class="pulse"></span> TERSEDIA UNTUK KOLABORASI</div><p class="hero-kicker">{{ $profile['hero_intro'] }}</p><h1>{{ $profile['hero_title'] }}</h1><p class="hero-desc">{{ $profile['hero_description'] }}</p><div class="hero-actions"><a class="button button-dark" href="#karya">Jelajahi karya <i class="bi bi-arrow-down-right"></i></a><a class="text-link" href="#tentang">Kenali saya <i class="bi bi-arrow-right"></i></a></div><div class="hero-social"><span>Temukan saya di</span><a href="{{ $profile['youtube_url'] ?: '#kontak' }}" @if($profile['youtube_url']) target="_blank" rel="noreferrer" @endif>YouTube</a><a href="{{ $profile['instagram_url'] ?: '#kontak' }}" @if($profile['instagram_url']) target="_blank" rel="noreferrer" @endif>Instagram</a><a href="{{ $profile['tiktok_url'] ?: '#kontak' }}" @if($profile['tiktok_url']) target="_blank" rel="noreferrer" @endif>TikTok</a></div></div><div class="hero-art"><div class="art-orbit orbit-one"></div><div class="art-orbit orbit-two"></div><div class="portrait-card @if($profilePhoto) has-profile-photo @endif">@if($profilePhoto)<img class="portrait-image" src="{{ asset('storage/'.$profilePhoto) }}" alt="Foto Nadhim Alim">@else<div class="portrait-sun"></div><div class="portrait-silhouette"><div class="portrait-head"></div><div class="portrait-body"></div></div>@endif<div class="portrait-caption"><span class="caption-dot"></span> IDEAS IN PROGRESS <span>✳</span></div></div><div class="floating-note note-top"><i class="bi bi-stars"></i><span>Curious by nature</span></div><div class="floating-note note-bottom"><span class="note-number">01</span><span>Design · Create · Share</span></div><div class="art-spark spark-a">✳</div><div class="art-spark spark-b">✳</div></div></section>
<section class="social-proof"><div class="container proof-inner"><p>Komunitas yang terus bertumbuh</p><div class="proof-stat"><i class="bi bi-youtube"></i><strong>5+</strong><span>subscriber YouTube</span></div><div class="proof-stat"><i class="bi bi-instagram"></i><strong>1K+</strong><span>followers Instagram</span></div><div class="proof-stat"><i class="bi bi-tiktok"></i><strong>700+</strong><span>followers TikTok</span></div></div></section>
<section class="section container about-section" id="tentang"><div class="section-label"><span>01 / TENTANG SAYA</span><i></i></div><div class="about-grid"><h2>{{ $profile['about_title'] }}</h2><div class="about-copy"><p>{{ $profile['about_paragraph_1'] }}</p><p>{{ $profile['about_paragraph_2'] }}</p><a class="text-link" href="#kontak">Ceritakan ide Anda <i class="bi bi-arrow-up-right"></i></a></div></div></section>
@if($educations->isNotEmpty())
<section class="education-section section">
    <div class="container">
        <div class="section-label"><span>RIWAYAT PENDIDIKAN</span><i></i></div>
        <div class="education-timeline">
            @foreach($educations as $education)
            <article class="education-entry">
                <div class="education-marker"><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>@if(!$loop->last)<i></i>@endif</div>
                <div class="education-card">
                    <span class="education-level">{{ $education->level }}</span>
                    <h3>{{ $education->institution }}</h3>
                    @if($education->start_year || $education->end_year)<span class="education-years">{{ $education->start_year ?: '—' }} — {{ $education->end_year ?: 'Sekarang' }}</span>@endif
                    @if($education->description)<p>{{ $education->description }}</p>@endif
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif
<section class="section work-section" id="karya"><div class="container"><div class="section-label"><span>02 / PILIHAN KARYA</span><i></i><span class="section-note">Beberapa hal yang telah dibuat</span></div><div class="work-heading"><h2>Ide jadi <em>realita.</em></h2><span>{{ $projectCount }} proyek</span></div><div class="project-grid">@forelse($projects as $index => $project)<article class="project-card"><div class="project-image" style="--project-hue: {{ ($index * 57 + 24) % 360 }}deg">@if($project->image)<img src="{{ asset('storage/'.$project->image) }}" alt="{{ $project->title }}">@else<div class="project-art"><span>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span><i class="bi bi-asterisk"></i><b>{{ $project->category }}</b></div>@endif<a href="{{ $project->link ?: '#kontak' }}" {{ $project->link ? 'target=_blank rel=noreferrer' : '' }} class="project-open" aria-label="Buka {{ $project->title }}"><i class="bi bi-arrow-up-right"></i></a></div><div class="project-meta"><div><span>{{ $project->category }}</span><h3>{{ $project->title }}</h3></div><i class="bi bi-arrow-up-right"></i></div><p class="project-description">{{ $project->description }}</p></article>@empty<div class="empty-project"><i class="bi bi-camera"></i><h3>Ruang untuk karya berikutnya</h3><p>Proyek baru akan muncul di sini. Punya ide? Mari kita wujudkan.</p></div>@endforelse</div>@if($projectCount >= 3)<div class="news-all-link-wrap portfolio-all-link-wrap"><a class="news-all-link" href="{{ route('portfolio.index') }}">Lihat semua karya <i class="bi bi-arrow-right"></i><span>({{ $projectCount }})</span></a></div>@endif</div></section>
@include('partials.services')
<section class="section container news-section" id="berita">
    <div class="section-label"><span>CATATAN & KABAR</span><i></i><span class="section-note">PEMBARUAN TERBARU</span></div>
    <div class="news-heading"><div><h2>Yang baru di <em>jurnal.</em></h2><p class="muted">Cerita, pemikiran, dan hal baru dari perjalanan berkarya.</p></div><span class="news-count"><strong>{{ $newsCount }}</strong><small>artikel diterbitkan</small></span></div>
    <div class="news-grid">
        @forelse($news as $article)
        <article class="news-card">
            @if($article->image)<a class="news-image" href="{{ route('news.show', $article) }}"><img src="{{ asset('storage/'.$article->image) }}" alt="{{ $article->title }}"><span class="news-image-label">CATATAN <i class="bi bi-arrow-up-right"></i></span></a>@else<a class="news-image news-image-placeholder" href="{{ route('news.show', $article) }}"><span>NA<span>.</span></span><small>CATATAN & KABAR</small></a>@endif
            <div class="news-card-body"><div class="news-card-meta"><span>ARTIKEL</span><time datetime="{{ $article->published_at->toIso8601String() }}">{{ $article->published_at->format('d M Y') }}</time></div><h3><a href="{{ route('news.show', $article) }}">{{ $article->title }}</a></h3><p>{{ $article->excerpt }}</p><a class="news-read-more" href="{{ route('news.show', $article) }}">Baca cerita <i class="bi bi-arrow-up-right"></i></a></div>
        </article>
        @empty
        <div class="news-empty"><i class="bi bi-journal-text"></i><h3>Berita segera hadir.</h3><p>Catatan dan kabar terbaru akan muncul di sini.</p></div>
        @endforelse
    </div>
    @if($newsCount >= 3)<div class="news-all-link-wrap"><a class="news-all-link" href="{{ route('news.index') }}">Lihat semua berita <i class="bi bi-arrow-right"></i></a></div>@endif
</section>
<section class="section container skills-section" id="keahlian"><div class="section-label"><span>04 / KEAHLIAN SAYA</span><i></i></div><div class="skills-grid"><div><h2>Menyatukan <em>kreativitas</em> dan fungsi.</h2><p class="muted">Kumpulan kemampuan teknis dan interpersonal yang mendukung setiap karya.</p></div><div class="public-skill-groups">@foreach(['hard' => 'Hard skill', 'soft' => 'Soft skill'] as $type => $title)<div class="public-skill-group"><h3>{{ $title }}</h3>@forelse($skills->get($type, collect()) as $skill)<div class="public-skill"><div><span>{{ $skill->name }}</span><small>{{ $skill->level }}%</small></div><div class="skill-meter"><i style="width: {{ $skill->level }}%"></i></div></div>@empty<p class="muted">Keahlian akan segera ditambahkan.</p>@endforelse</div>@endforeach</div></div></section>
<section class="cv-section" id="cv"><div class="container cv-inner"><div><div class="section-label"><span>05 / RINGKASAN PROFESIONAL</span><i></i></div><h2>Perjalanan saya,<br><em>dalam satu halaman.</em></h2><p>Unduh CV untuk melihat pengalaman, keahlian, dan informasi profesional saya.</p></div>@if($cvPath)<a class="button button-light" href="{{ asset('storage/'.$cvPath) }}" target="_blank">Unduh CV <i class="bi bi-download"></i></a>@else<a class="button button-light" href="#kontak">Minta CV <i class="bi bi-arrow-up-right"></i></a>@endif</div></section>
<section class="section container contact-section" id="kontak"><div class="section-label"><span>06 / KATAKAN HALO</span><i></i></div><div class="contact-grid"><h2>Ada ide menarik?<br><em>Saya siap mendengar.</em></h2><div><p>Ceritakan sedikit tentang proyek atau kolaborasi yang sedang Anda bayangkan.</p>@if(session('success'))<p role="status" class="contact-success">{{ session('success') }}</p>@endif @if($errors->has('message'))<p role="alert" style="color:#a63e2d">{{ $errors->first('message') }}</p>@endif<form action="{{ route('contact.send') }}" method="POST" class="contact-form">@csrf<div class="form-row"><label>Nama<input name="name" required placeholder="Nama Anda" value="{{ old('name') }}"></label><label>Email<input type="email" name="email" required placeholder="nama@email.com" value="{{ old('email') }}"></label></div><label>Pesan<textarea name="message" rows="3" required placeholder="Ceritakan ide Anda...">{{ old('message') }}</textarea></label><button class="button button-dark" type="submit">Kirim pesan <i class="bi bi-arrow-up-right"></i></button></form></div></div></section>
</main>
<dialog class="consultation-dialog" id="consultation-dialog" aria-labelledby="consultation-title">
    <div class="consultation-dialog-header"><div><span class="panel-kicker">MULAI DARI SINI</span><h2 id="consultation-title">Ceritakan kebutuhan Anda.</h2><p>Isi singkat formulir ini. Pesan masuk ke dashboard admin dan disiapkan untuk WhatsApp.</p></div><button type="button" class="consultation-close" data-consultation-close aria-label="Tutup formulir"><i class="bi bi-x-lg"></i></button></div>
    <form id="consultation-form" class="consultation-form" action="{{ route('contact.send') }}" method="POST" data-ajax-form="true">@csrf<input type="hidden" name="consultation" value="1">
        <div class="consultation-fields"><label>Nama<input name="name" required maxlength="255" autocomplete="name" placeholder="Nama Anda"></label><label>Email<input name="email" type="email" required autocomplete="email" placeholder="nama@email.com"></label>
            <label>Nomor WhatsApp Anda <span>Opsional</span><input name="phone" type="tel" maxlength="30" autocomplete="tel" placeholder="08xx xxxx xxxx"></label><label>Paket yang diminati<select name="service" required><option value="">Pilih paket</option>@foreach($services as $service)<option value="{{ $service->title }}">{{ $service->title }}</option>@endforeach<option value="Kebutuhan khusus">Kebutuhan khusus</option></select></label>
            <label>Kisaran anggaran<select name="budget"><option value="">Belum ditentukan</option><option>Di bawah Rp 2 juta</option><option>Rp 2–5 juta</option><option>Rp 5–10 juta</option><option>Di atas Rp 10 juta</option></select></label><label>Target pengerjaan<select name="timeline"><option value="">Fleksibel</option><option>Secepatnya</option><option>Dalam 2–4 minggu</option><option>Dalam 1–3 bulan</option><option>Belum ditentukan</option></select></label>
            <label class="consultation-wide">Ceritakan kebutuhan Anda<textarea name="message" rows="4" required maxlength="5000" placeholder="Apa yang ingin dibuat? Siapa yang akan menggunakannya?"></textarea></label>
        </div>
        <p class="consultation-status" data-consultation-status role="status" aria-live="polite"></p><a class="consultation-wa-fallback" data-wa-fallback hidden target="_blank" rel="noreferrer">Buka WhatsApp <i class="bi bi-arrow-up-right"></i></a>
        <div class="consultation-form-footer"><small><i class="bi bi-lock"></i> Informasi Anda hanya digunakan untuk menindaklanjuti konsultasi.</small><button class="button button-dark" type="submit"><span data-consultation-button>Ajukan konsultasi</span><i class="bi bi-arrow-up-right"></i></button></div>
    </form>
</dialog>
<footer class="site-footer">
    <div class="container footer-main">
        <div class="footer-callout">
            <div><span class="footer-eyebrow">ADA IDE YANG INGIN DIWUJUDKAN?</span><h2>Mari buat sesuatu<br><em>yang bermakna.</em></h2></div>
            <a class="footer-contact" href="#kontak">Mulai percakapan <i class="bi bi-arrow-up-right"></i></a>
        </div>
        <div class="footer-columns">
            <div class="footer-about"><a class="brand" href="#home">NA<span>.</span></a><p>Kreator digital dan web developer. Merancang pengalaman digital yang berguna dan berkesan.</p></div>
            <div class="footer-column"><span class="footer-label">JELAJAHI</span><a href="#tentang">Tentang saya</a><a href="#karya">Portofolio</a><a href="#layanan">Layanan</a><a href="#keahlian">Keahlian</a></div>
            <div class="footer-column"><span class="footer-label">TERHUBUNG</span>
                @if($profile['instagram_url'])<a href="{{ $profile['instagram_url'] }}" target="_blank" rel="noreferrer">Instagram <i class="bi bi-arrow-up-right"></i></a>@endif
                @if($profile['youtube_url'])<a href="{{ $profile['youtube_url'] }}" target="_blank" rel="noreferrer">YouTube <i class="bi bi-arrow-up-right"></i></a>@endif
                @if($profile['tiktok_url'])<a href="{{ $profile['tiktok_url'] }}" target="_blank" rel="noreferrer">TikTok <i class="bi bi-arrow-up-right"></i></a>@endif
                <a href="{{ route('admin.login') }}">Admin <i class="bi bi-box-arrow-in-right"></i></a>
            </div>
        </div>
        <div class="footer-bottom"><span>© {{ date('Y') }} Nadhim Alim</span><span>Dirancang dengan rasa ingin tahu <i class="bi bi-sparkle"></i></span><a href="#home">Kembali ke atas <i class="bi bi-arrow-up"></i></a></div>
    </div>
</footer>
<script src="{{ asset('js/consultation.js') }}"></script><script src="{{ asset('js/feedback.js') }}"></script></body></html>






