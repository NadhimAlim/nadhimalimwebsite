<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Admin — Nadhim Alim</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/portfolio.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-polish.css') }}?v=20261008-messages1">
    <link rel="stylesheet" href="{{ asset('css/feedback.css') }}">
</head>
<body class="admin-body">
<div class="dashboard-layout">
    <aside class="admin-sidebar">
        <a class="brand" href="{{ route('home') }}">NA<span>.</span></a>
        <div class="sidebar-caption">WORKSPACE</div>
        <a class="sidebar-link {{ $section === 'overview' ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid-1x2"></i> Ringkasan</a>
        <a class="sidebar-link {{ $section === 'packages' ? 'active' : '' }}" href="{{ route('admin.dashboard.section', 'packages') }}"><i class="bi bi-box-seam"></i> Paket jasa</a>
        <a class="sidebar-link {{ $section === 'messages' ? 'active' : '' }}" href="{{ route('admin.dashboard.section', 'messages') }}"><i class="bi bi-envelope"></i> Pesan masuk @if($unreadMessageCount)<span class="sidebar-badge">{{ $unreadMessageCount }}</span>@endif</a>
        <a class="sidebar-link {{ $section === 'projects' ? 'active' : '' }}" href="{{ route('admin.dashboard.section', 'projects') }}"><i class="bi bi-images"></i> Portofolio</a>
        <a class="sidebar-link {{ $section === 'profile' ? 'active' : '' }}" href="{{ route('admin.dashboard.section', 'profile') }}"><i class="bi bi-person-lines-fill"></i> Konten profil</a>
        <a class="sidebar-link {{ $section === 'education' ? 'active' : '' }}" href="{{ route('admin.dashboard.section', 'education') }}"><i class="bi bi-mortarboard"></i> Pendidikan</a>
        <a class="sidebar-link {{ $section === 'news' ? 'active' : '' }}" href="{{ route('admin.dashboard.section', 'news') }}"><i class="bi bi-newspaper"></i> Berita</a>
        <a class="sidebar-link {{ $section === 'marketplace' ? 'active' : '' }}" href="{{ route('admin.dashboard.section', 'marketplace') }}"><i class="bi bi-bag"></i> Marketplace @if($marketplaceOrders->where('payment_status', 'paid')->where('status', 'awaiting_fulfillment')->count())<span class="sidebar-badge">{{ $marketplaceOrders->where('payment_status', 'paid')->where('status', 'awaiting_fulfillment')->count() }}</span>@endif</a>
        <a class="sidebar-link {{ $section === 'schedule' ? 'active' : '' }}" href="{{ route('admin.dashboard.section', 'schedule') }}"><i class="bi bi-calendar3"></i> Jadwal kerja @if($upcomingTaskCount)<span class="sidebar-badge">{{ $upcomingTaskCount }}</span>@endif</a>
        <a class="sidebar-link {{ $section === 'data-backup' ? 'active' : '' }}" href="{{ route('admin.dashboard.section', 'data-backup') }}"><i class="bi bi-database-down"></i> Data & backup</a>
        <a class="sidebar-link {{ $section === 'profile-photo' ? 'active' : '' }}" href="{{ route('admin.dashboard.section', 'profile-photo') }}"><i class="bi bi-person-bounding-box"></i> Foto profil</a>
        <a class="sidebar-link {{ $section === 'cv-panel' ? 'active' : '' }}" href="{{ route('admin.dashboard.section', 'cv-panel') }}"><i class="bi bi-file-earmark-person"></i> Dokumen CV</a>
        <div class="sidebar-bottom">
            <a class="admin-profile" href="{{ route('admin.dashboard.section', 'account') }}" aria-label="Ubah profil admin {{ $adminProfile['name'] }}"><div class="admin-avatar">@if($adminProfile['photo'])<img src="{{ asset('storage/'.$adminProfile['photo']) }}" alt="">@else{{ strtoupper(substr($adminProfile['name'], 0, 2)) }}@endif</div><div><strong>{{ $adminProfile['name'] }}</strong><span>Administrator</span></div></a>
            <form action="{{ route('admin.logout') }}" method="POST">@csrf<button class="sidebar-logout" type="submit"><i class="bi bi-box-arrow-left"></i> Keluar</button></form>
        </div>
    </aside>

    <main class="dashboard-main" id="overview">
        @php($sectionTitles = ['overview' => 'Ringkasan', 'packages' => 'Paket jasa', 'messages' => 'Pesan masuk', 'projects' => 'Portofolio', 'profile' => 'Konten profil', 'education' => 'Pendidikan', 'news' => 'Berita', 'marketplace' => 'Marketplace', 'profile-photo' => 'Foto profil', 'cv-panel' => 'Dokumen CV', 'account' => 'Profil admin', 'schedule' => 'Jadwal kerja', 'data-backup' => 'Data & backup'])
        <header class="dashboard-header"><div><span class="dashboard-breadcrumb">Workspace <i class="bi bi-chevron-right"></i> {{ $sectionTitles[$section] }}</span><h1>{{ $sectionTitles[$section] }}</h1><p>Kelola {{ $section === 'overview' ? 'layanan, karya, dan dokumen profesional Anda' : strtolower($sectionTitles[$section]) . ' portofolio Anda' }}.</p></div><a href="{{ route('home') }}" class="view-site" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Lihat situs</a></header>

        @if(session('success'))
            <div class="alert"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert" style="background:#fff0ed;color:#9d3e2d"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
        @endif

        @if($section === 'overview')
        <section class="dashboard-metrics overview-metrics">
            <article class="metric-card"><span>Total proyek</span><div><strong>{{ $projects->count() }}</strong><i class="bi bi-images"></i></div><small>Karya di portofolio</small><a class="metric-link" href="{{ route('admin.dashboard.section', 'projects') }}">Kelola karya <i class="bi bi-arrow-right"></i></a></article>
            <article class="metric-card"><span>Pesan belum dibaca</span><div><strong>{{ $unreadMessageCount }}</strong><i class="bi bi-envelope"></i></div><small>{{ $contactMessages->count() }} pesan total</small><a class="metric-link" href="{{ route('admin.dashboard.section', 'messages') }}">Buka pesan <i class="bi bi-arrow-right"></i></a></article>
            <article class="metric-card"><span>Agenda mendatang</span><div><strong>{{ $upcomingTaskCount }}</strong><i class="bi bi-calendar-check"></i></div><small>Jadwal kerja aktif</small><a class="metric-link" href="{{ route('admin.dashboard.section', 'schedule') }}">Lihat jadwal <i class="bi bi-arrow-right"></i></a></article>
            <article class="metric-card {{ $overdueDeadlineCount ? 'metric-urgent' : '' }}"><span>Deadline perlu perhatian</span><div><strong>{{ $overdueDeadlineCount + $nearDeadlineCount }}</strong><i class="bi bi-alarm"></i></div><small>{{ $overdueDeadlineCount }} terlambat · {{ $nearDeadlineCount }} dalam 3 hari</small><a class="metric-link" href="{{ route('admin.dashboard.section', 'schedule') }}">Periksa deadline <i class="bi bi-arrow-right"></i></a></article>
        </section>
        <section class="overview-tools-grid">
            <article class="overview-clock-card"><span class="panel-kicker">WAKTU SEKARANG</span><strong data-live-clock>--:--:--</strong><span data-live-date></span><small>Waktu Indonesia Barat · WIB</small></article>
            <article class="overview-calendar-card"><div class="overview-calendar-heading"><div><span class="panel-kicker">KALENDER</span><h2 data-overview-month></h2></div><div><button type="button" data-overview-prev aria-label="Bulan sebelumnya"><i class="bi bi-chevron-left"></i></button><button type="button" data-overview-next aria-label="Bulan berikutnya"><i class="bi bi-chevron-right"></i></button></div></div><div class="overview-calendar-grid" data-overview-calendar></div><div class="overview-calendar-legend"><i></i> Ada agenda kerja</div></article>
        </section>
        <div class="overview-calendar-source" aria-hidden="true">@foreach($workTasks->where('status', '!=', 'done') as $task)<span data-date="{{ $task->scheduled_at->toDateString() }}"></span>@endforeach</div>
        <section class="admin-panel dashboard-panel"><div class="panel-heading"><div><span class="panel-kicker">PEKERJAAN PRIBADI</span><h2>Jadwal proyek</h2><p>{{ $upcomingTaskCount }} agenda mendatang</p></div><a class="admin-button" href="{{ route('admin.dashboard.section', 'schedule') }}"><i class="bi bi-calendar3"></i> Atur jadwal</a></div>
            <div class="schedule-quick-list">@forelse($workTasks->where('status', '!=', 'done')->take(3) as $task)<article><strong>{{ $task->title }}</strong><span>{{ $task->scheduled_at->format('d M Y, H:i') }}@if($task->client) · {{ $task->client }}@endif</span></article>@empty<p class="admin-sub">Belum ada jadwal aktif. Tambahkan agenda proyek agar pekerjaan lebih teratur.</p>@endforelse</div>
        </section>
        @endif

        @if($section === 'messages')
        <section class="admin-panel dashboard-panel messages-panel">
            <div class="panel-heading"><div><span class="panel-kicker">KOTAK MASUK</span><h2>Pesan dari pengunjung</h2><p>Kelola pertanyaan dan permintaan konsultasi yang dikirim melalui halaman utama.</p></div><span class="panel-count">{{ $contactMessages->count() }} pesan</span></div>
            <div class="messages-overview"><article><span>Total pesan</span><strong>{{ $contactMessages->count() }}</strong><i class="bi bi-inbox"></i></article><article class="unread"><span>Belum dibaca</span><strong>{{ $unreadMessageCount }}</strong><i class="bi bi-envelope-exclamation"></i></article><article><span>Sudah dibaca</span><strong>{{ $contactMessages->count() - $unreadMessageCount }}</strong><i class="bi bi-envelope-check"></i></article></div>
            <div class="messages-toolbar"><label class="messages-search"><i class="bi bi-search"></i><input type="search" placeholder="Cari nama, email, atau isi pesan..." aria-label="Cari pesan" data-message-search></label><div class="messages-filters" role="group" aria-label="Filter pesan"><button type="button" class="active" data-message-filter="all" aria-pressed="true">Semua <span>{{ $contactMessages->count() }}</span></button><button type="button" data-message-filter="unread" aria-pressed="false">Belum dibaca <span>{{ $unreadMessageCount }}</span></button><button type="button" data-message-filter="read" aria-pressed="false">Sudah dibaca <span>{{ $contactMessages->count() - $unreadMessageCount }}</span></button></div></div>
            <div class="contact-message-list" data-message-list>
                @forelse($contactMessages as $contactMessage)
                    <article class="contact-message-card {{ $contactMessage->is_read ? 'is-read' : 'is-unread' }}" data-message-card data-status="{{ $contactMessage->is_read ? 'read' : 'unread' }}">
                        <div class="contact-message-heading"><div class="message-sender"><span class="message-avatar">{{ strtoupper(substr(trim($contactMessage->name), 0, 1)) }}</span><div><div class="message-sender-line"><h3>{{ $contactMessage->name }}</h3><span class="message-state {{ $contactMessage->is_read ? 'read' : 'unread' }}">{{ $contactMessage->is_read ? 'Sudah dibaca' : 'Belum dibaca' }}</span>@if(str_starts_with($contactMessage->message, '[PERMINTAAN KONSULTASI]'))<span class="message-kind"><i class="bi bi-chat-square-text"></i> Konsultasi</span>@endif</div><a href="mailto:{{ $contactMessage->email }}"><i class="bi bi-envelope"></i> {{ $contactMessage->email }}</a></div></div><time datetime="{{ $contactMessage->created_at->toIso8601String() }}" title="{{ $contactMessage->created_at->format('d M Y, H:i') }}">{{ $contactMessage->created_at->diffForHumans() }}<span>{{ $contactMessage->created_at->format('d M Y · H:i') }}</span></time></div>
                        <div class="message-content"><span class="message-content-label">ISI PESAN</span><p>{{ $contactMessage->message }}</p></div>
                        <div class="message-actions"><a class="admin-button message-reply-button" href="mailto:{{ $contactMessage->email }}?subject={{ rawurlencode('Re: Pesan untuk Nadhim Alim') }}"><i class="bi bi-reply"></i> Balas email</a><form action="{{ route('admin.messages.status', $contactMessage) }}" method="POST" data-loading-title="Memperbarui status pesan...">@csrf @method('PUT')<input type="hidden" name="is_read" value="{{ $contactMessage->is_read ? 0 : 1 }}"><button class="admin-button message-status-button" type="submit"><i class="bi {{ $contactMessage->is_read ? 'bi-envelope' : 'bi-envelope-check' }}"></i> Tandai {{ $contactMessage->is_read ? 'belum dibaca' : 'sudah dibaca' }}</button></form><form action="{{ route('admin.messages.delete', $contactMessage) }}" method="POST" data-confirm="Hapus pesan dari {{ $contactMessage->name }}? Tindakan ini tidak dapat dibatalkan." data-loading-title="Menghapus pesan..." data-loading-description="Pesan sedang dihapus dari kotak masuk.">@csrf @method('DELETE')<button class="admin-button danger" type="submit"><i class="bi bi-trash3"></i> Hapus</button></form></div>
                    </article>
                @empty
                    <div class="messages-empty"><span><i class="bi bi-inbox"></i></span><h3>Belum ada pesan masuk</h3><p>Pesan dari formulir kontak akan muncul di sini.</p></div>
                @endforelse
                @if($contactMessages->isNotEmpty())<p class="messages-no-results" data-message-empty hidden>Tidak ada pesan yang cocok dengan pencarian ini.</p>@endif
            </div>
        </section>
        @endif

        @if($section === 'packages')
        <section class="admin-panel dashboard-panel" id="packages">
            <div class="panel-heading"><div><span class="panel-kicker">LAYANAN & HARGA</span><h2>Kelola paket jasa</h2><p>Harga yang Anda atur tampil langsung di halaman portofolio.</p></div><span class="panel-count">{{ $services->count() }} paket</span></div>
            <div class="service-admin-grid">
                @foreach($services as $service)
                    <article class="service-manage-card">
                        <div class="service-manage-top"><span class="service-icon"><i class="bi {{ $service->icon ?: 'bi-window' }}"></i></span><span class="service-status"><i></i> Aktif</span></div>
                        <form action="{{ route('admin.services.update', $service) }}" method="POST" class="admin-form service-edit-form">
                            @csrf
                            @method('PUT')
                            <label>Nama paket<input name="title" required maxlength="255" value="{{ $service->title }}"></label>
                            <label class="wide">Deskripsi<textarea name="description" rows="3" required maxlength="2000">{{ $service->description }}</textarea></label>
                            <label>Harga mulai dari (Rp)<input name="starting_price" type="number" min="0" step="1000" required value="{{ (int) $service->starting_price }}"></label>
                            <input type="hidden" name="icon" value="{{ $service->icon }}">
                            <button class="admin-button" type="submit"><i class="bi bi-check2"></i> Simpan perubahan</button>
                        </form>
                        <form action="{{ route('admin.services.delete', $service) }}" method="POST" class="service-delete-form" data-confirm="Hapus paket jasa ini? Tindakan ini tidak dapat dibatalkan.">@csrf @method('DELETE')<button type="submit" aria-label="Hapus {{ $service->title }}"><i class="bi bi-trash3"></i></button></form>
                    </article>
                @endforeach
            </div>
            <div class="new-service-wrap">
                <h3><i class="bi bi-plus-circle"></i> Tambah paket baru</h3>
                <form class="admin-form new-service-form" action="{{ route('admin.services.store') }}" method="POST">
                    @csrf
                    <label>Nama paket<input name="title" required maxlength="255" placeholder="Contoh: Maintenance Website"></label>
                    <label>Harga mulai dari (Rp)<input name="starting_price" type="number" min="0" step="1000" required placeholder="750000"></label>
                    <label class="wide">Deskripsi<textarea name="description" rows="2" required maxlength="2000" placeholder="Jelaskan fitur dan manfaat paket ini"></textarea></label>
                    <input type="hidden" name="icon" value="bi-stars">
                    <button class="admin-button" type="submit"><i class="bi bi-plus-lg"></i> Tambahkan paket</button>
                </form>
            </div>
        </section>
        @endif

        @if($section === 'profile')
        <section class="admin-panel dashboard-panel">
            <div class="panel-heading"><div><span class="panel-kicker">TAMPILAN HALAMAN UTAMA</span><h2>Konten profil</h2><p>Ubah teks perkenalan dan bagian tentang saya. Perubahan baru aktif setelah dipublikasikan.</p></div></div>
            <form class="admin-form profile-content-form" action="{{ route('admin.profile.preview') }}" method="POST" data-loading-title="Menyiapkan pratinjau..." data-loading-description="Menyusun tampilan halaman utama.">
                @csrf
                <label>Kalimat perkenalan<input name="hero_intro" maxlength="120" required value="{{ old('hero_intro', $profileContent['hero_intro']) }}"></label>
                <label class="wide">Judul utama<input name="hero_title" maxlength="180" required value="{{ old('hero_title', $profileContent['hero_title']) }}"></label>
                <label class="wide">Deskripsi singkat<textarea name="hero_description" rows="3" maxlength="500" required>{{ old('hero_description', $profileContent['hero_description']) }}</textarea></label>
                <label class="wide">Judul bagian tentang saya<input name="about_title" maxlength="180" required value="{{ old('about_title', $profileContent['about_title']) }}"></label>
                <label class="wide">Paragraf pertama<textarea name="about_paragraph_1" rows="3" maxlength="1000" required>{{ old('about_paragraph_1', $profileContent['about_paragraph_1']) }}</textarea></label>
                <label class="wide">Paragraf kedua<textarea name="about_paragraph_2" rows="3" maxlength="1000" required>{{ old('about_paragraph_2', $profileContent['about_paragraph_2']) }}</textarea></label>
                <div class="social-settings-heading"><span class="panel-kicker">TAUTAN MEDIA SOSIAL</span><p>Kosongkan jika akun tidak ingin ditampilkan.</p></div>
                <label>YouTube<input type="url" name="youtube_url" maxlength="2048" value="{{ old('youtube_url', $profileContent['youtube_url']) }}" placeholder="https://youtube.com/@username"></label>
                <label>Instagram<input type="url" name="instagram_url" maxlength="2048" value="{{ old('instagram_url', $profileContent['instagram_url']) }}" placeholder="https://instagram.com/username"></label>
                <label class="wide">TikTok<input type="url" name="tiktok_url" maxlength="2048" value="{{ old('tiktok_url', $profileContent['tiktok_url']) }}" placeholder="https://tiktok.com/@username"></label>
                <button class="admin-button" type="submit"><i class="bi bi-eye"></i> Lihat pratinjau</button>
            </form>
        </section>
        @endif


        @if($section === 'projects')
        <section class="admin-panel dashboard-panel" id="projects">
            <div class="panel-heading"><div><span class="panel-kicker">KARYA PILIHAN</span><h2>Portofolio proyek</h2><p>Terbitkan hasil kerja terbaru Anda.</p></div><span class="panel-count">{{ $projects->count() }} proyek</span></div>
            <form class="admin-form project-create-form" action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label>Nama proyek<input name="title" required maxlength="255" value="{{ old('title') }}" placeholder="Contoh: Website portfolio"></label>
                <label>Kategori<input name="category" required maxlength="100" value="{{ old('category') }}" placeholder="Website, aplikasi, konten"></label>
                <label class="wide">Deskripsi<textarea name="description" rows="2" required maxlength="3000" placeholder="Ceritakan tentang proyek ini">{{ old('description') }}</textarea></label>
                <label>Link proyek<input name="link" type="url" value="{{ old('link') }}" placeholder="https://"></label>
                <label>Gambar sampul<input name="image" type="file" accept="image/jpeg,image/png,image/webp"><span class="admin-sub">JPG, PNG, atau WebP · maks. 5 MB</span></label>
                <button class="admin-button" type="submit"><i class="bi bi-cloud-arrow-up"></i> Terbitkan proyek</button>
            </form>
            <label class="admin-search"><i class="bi bi-search"></i><input type="search" data-admin-search="projects" placeholder="Cari nama proyek atau kategori..."></label><p class="admin-sub search-empty" data-search-empty="projects" hidden>Tidak ada proyek yang cocok.</p>
            <div class="admin-project-list">
                @forelse($projects as $project)
                    <article class="admin-project-entry" data-search-item="projects">
                    <div class="admin-project">
                        @if($project->image)
                            <img src="{{ asset('storage/'.$project->image) }}" alt="">
                        @else
                            <div class="project-placeholder"><i class="bi bi-image"></i></div>
                        @endif
                        <div class="admin-project-info"><strong>{{ $project->title }}</strong><span>{{ $project->category }} · {{ $project->created_at->format('d M Y') }}</span></div>
                        <div class="project-order-controls"><form action="{{ route('admin.projects.order', $project) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="direction" value="up"><button type="submit" aria-label="Naikkan {{ $project->title }}" @disabled($loop->first)><i class="bi bi-arrow-up"></i></button></form><form action="{{ route('admin.projects.order', $project) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="direction" value="down"><button type="submit" aria-label="Turunkan {{ $project->title }}" @disabled($loop->last)><i class="bi bi-arrow-down"></i></button></form></div>
                        <details class="project-actions"><summary class="admin-button"><i class="bi bi-pencil-square"></i> Edit</summary>
                            <form class="admin-form project-edit-form" action="{{ route('admin.projects.update', $project) }}" method="POST" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <label>Nama proyek<input name="title" required maxlength="255" value="{{ $project->title }}"></label>
                                <label>Kategori<input name="category" required maxlength="100" value="{{ $project->category }}"></label>
                                <label class="wide">Deskripsi<textarea name="description" rows="3" required maxlength="3000">{{ $project->description }}</textarea></label>
                                <label>Link proyek<input name="link" type="url" value="{{ $project->link }}" placeholder="https://"></label>
                                <label>Ganti foto sampul<input name="image" type="file" accept="image/jpeg,image/png,image/webp"><span class="admin-sub">Opsional · JPG, PNG, WebP · maks. 5 MB</span></label>
                                <button type="submit" class="admin-button"><i class="bi bi-check2"></i> Simpan perubahan</button>
                            </form>
                        </details>
                        <form action="{{ route('admin.projects.delete', $project) }}" method="POST" data-confirm="Hapus proyek ini? Tindakan ini tidak dapat dibatalkan.">@csrf @method('DELETE')<button type="submit" class="admin-button danger"><i class="bi bi-trash3"></i> Hapus</button></form>
                    </div>
                    </article>
                @empty
                    <p class="admin-sub">Belum ada proyek. Tambahkan karya pertama melalui formulir di atas.</p>
                @endforelse
            </div>
        </section>
        @endif

        @if($section === 'education')
        <section class="admin-panel dashboard-panel">
            <div class="panel-heading"><div><span class="panel-kicker">RIWAYAT BELAJAR</span><h2>Pendidikan</h2><p>Isi nama sekolah atau kampus dan tahun. Hanya jenjang yang sudah diisi akan tampil di halaman utama.</p></div></div>
            <div class="education-admin-list">
                @foreach($educations as $education)
                <form class="education-admin-card" action="{{ route('admin.education.update', $education) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="education-admin-level"><span class="education-admin-number">{{ str_pad($education->sort_order, 2, '0', STR_PAD_LEFT) }}</span><div><span class="panel-kicker">JENJANG</span><h3>{{ $education->level }}</h3></div></div>
                    <label>Nama sekolah/kampus<input name="institution" maxlength="255" value="{{ old('institution', $education->institution) }}" placeholder="Contoh: SD Negeri 01"></label>
                    <label>Tahun mulai<input name="start_year" type="number" min="1900" max="2100" value="{{ old('start_year', $education->start_year) }}" placeholder="2010"></label>
                    <label>Tahun selesai<input name="end_year" type="number" min="1900" max="2100" value="{{ old('end_year', $education->end_year) }}" placeholder="2016"></label>
                    <label class="wide">Keterangan (opsional)<textarea name="description" rows="2" maxlength="500" placeholder="Jurusan, fokus, atau pencapaian">{{ old('description', $education->description) }}</textarea></label>
                    <button class="admin-button" type="submit"><i class="bi bi-check2"></i> Simpan {{ $education->level }}</button>
                </form>
                @endforeach
            </div>
        </section>
        @endif

        @if($section === 'news')
        <section class="admin-panel dashboard-panel">
            <div class="panel-heading"><div><span class="panel-kicker">PUBLIKASI</span><h2>Berita & artikel</h2><p>Kelola tulisan yang tampil di halaman utama dan arsip berita.</p></div><div class="news-admin-counts"><span><strong>{{ $newsArticles->whereNotNull('published_at')->count() }}</strong> Terbit</span><span><strong>{{ $newsArticles->whereNull('published_at')->count() }}</strong> Draft</span></div></div>
            <details class="news-create-panel">
                <summary><span class="news-create-icon"><i class="bi bi-pencil-square"></i></span><span><strong>Tulis berita baru</strong><small>Tambahkan tulisan, gambar, dan atur status publikasi.</small></span><i class="bi bi-plus-lg"></i></summary>
            <form class="admin-form news-create-form" action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label>Judul<input name="title" required maxlength="255" placeholder="Judul berita"></label>
                <label class="wide">Ringkasan<textarea name="excerpt" rows="2" required maxlength="500" placeholder="Ringkasan singkat untuk kartu berita"></textarea></label>
                <label class="wide">Isi berita<textarea name="content" rows="8" required maxlength="30000" placeholder="Tulis isi berita di sini..."></textarea></label>
                <label>Gambar (opsional)<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
                <label class="news-publish-option"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1"> Terbitkan sekarang</label>
                <button class="admin-button" type="submit"><i class="bi bi-plus-lg"></i> Simpan berita</button>
            </form>
            </details>
            <div class="news-admin-list">
                @forelse($newsArticles as $article)
                <details class="news-admin-entry">
                    <summary><span class="news-admin-thumb">@if($article->image)<img src="{{ asset('storage/'.$article->image) }}" alt="">@else<i class="bi bi-image"></i>@endif</span><span class="news-admin-summary"><span class="news-admin-status {{ $article->published_at ? 'published' : 'draft' }}"><i class="bi {{ $article->published_at ? 'bi-globe2' : 'bi-file-earmark' }}"></i> {{ $article->published_at ? 'Terbit' : 'Draft' }}</span><strong class="news-admin-title">{{ $article->title }}</strong><small>{{ $article->excerpt }}</small></span><span class="news-admin-date">{{ $article->published_at?->format('d M Y') ?: 'Belum diterbitkan' }}</span><i class="bi bi-chevron-down"></i></summary>
                    <form class="admin-form news-edit-form" action="{{ route('admin.news.update', $article) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <label>Judul<input name="title" required maxlength="255" value="{{ $article->title }}"></label>
                        <label class="wide">Ringkasan<textarea name="excerpt" rows="2" required maxlength="500">{{ $article->excerpt }}</textarea></label>
                        <label class="wide">Isi berita<textarea name="content" rows="8" required maxlength="30000">{{ $article->content }}</textarea></label>
                        <label>Ganti gambar<input type="file" name="image" accept="image/jpeg,image/png,image/webp">@if($article->image)<small>Gambar saat ini tersedia.</small>@endif</label>
                        <label class="news-publish-option"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked($article->published_at)> Terbitkan</label>
                        <button class="admin-button" type="submit"><i class="bi bi-check2"></i> Simpan perubahan</button>
                    </form>
                    <div class="news-admin-actions">@if($article->published_at)<a href="{{ route('news.show', $article) }}" target="_blank" rel="noreferrer">Lihat artikel <i class="bi bi-arrow-up-right"></i></a>@else<span class="admin-sub">Terbitkan artikel untuk melihat halaman publik.</span>@endif<form action="{{ route('admin.news.delete', $article) }}" method="POST" data-confirm="Hapus berita ini? Tindakan ini tidak dapat dibatalkan.">@csrf @method('DELETE')<button class="admin-button danger" type="submit"><i class="bi bi-trash3"></i> Hapus</button></form></div>
                </details>
                @empty
                    <p class="admin-sub">Belum ada berita. Tambahkan berita pertama melalui formulir di atas.</p>
                @endforelse
            </div>
        </section>
        @endif

        @if($section === 'profile-photo')
        <section class="admin-panel dashboard-panel cv-admin-panel" id="profile-photo">
            <div class="panel-heading"><div><span class="panel-kicker">IDENTITAS PORTOFOLIO</span><h2>Foto profil</h2><p>Foto ini akan menggantikan ilustrasi pada halaman utama.</p></div><span class="cv-state {{ $profilePhoto ? 'ready' : '' }}"><i class="bi bi-circle-fill"></i> {{ $profilePhoto ? 'Sudah diunggah' : 'Belum diunggah' }}</span></div>
            <div class="profile-photo-manager">
                <div class="profile-photo-preview" id="profile-photo-preview">@if($profilePhoto)<img src="{{ asset('storage/'.$profilePhoto) }}" alt="Foto profil saat ini">@else<i class="bi bi-person"></i>@endif</div>
                <form action="{{ route('admin.profile-photo.upload') }}" method="POST" enctype="multipart/form-data" class="cv-upload-form">@csrf<label class="cv-file-input"><i class="bi bi-person-bounding-box"></i><span><strong>{{ $profilePhoto ? 'Pilih foto profil baru' : 'Pilih foto profil' }}</strong><small>JPG, PNG, atau WebP · maksimal 5 MB.</small><small class="selected-photo-name" aria-live="polite">Belum ada file dipilih</small></span><input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required data-photo-preview="profile-photo-preview"></label><button class="admin-button" type="submit"><i class="bi bi-cloud-arrow-up"></i> {{ $profilePhoto ? 'Perbarui foto' : 'Unggah foto' }}</button></form>
            </div>
        </section>
        @endif

        @if($section === 'cv-panel')
        <section class="admin-panel dashboard-panel cv-admin-panel" id="cv-panel">
            <div class="panel-heading"><div><span class="panel-kicker">DOKUMEN PROFESIONAL</span><h2>CV Anda</h2><p>PDF ini tersedia untuk diunduh dari halaman portofolio.</p></div><span class="cv-state {{ $cvPath ? 'ready' : '' }}"><i class="bi bi-circle-fill"></i> {{ $cvPath ? 'Sudah diunggah' : 'Belum diunggah' }}</span></div>
            <form action="{{ route('admin.cv.upload') }}" method="POST" enctype="multipart/form-data" class="cv-upload-form">@csrf<label class="cv-file-input"><i class="bi bi-file-earmark-pdf"></i><span><strong>Pilih file CV dalam format PDF</strong><small>Ukuran maksimal 10 MB. Unggahan baru menggantikan file lama.</small></span><input type="file" name="cv" accept="application/pdf,.pdf" required></label><button class="admin-button" type="submit"><i class="bi bi-cloud-arrow-up"></i> {{ $cvPath ? 'Perbarui CV' : 'Unggah CV' }}</button></form>
        </section>
        @endif

        @if($section === 'account')
        <section class="admin-panel dashboard-panel"><div class="panel-heading"><div><span class="panel-kicker">AKUN PENGELOLA</span><h2>Profil admin</h2><p>Atur nama dan foto yang tampil di sidebar, serta perbarui kata sandi masuk.</p></div></div>
            <div class="account-settings-grid"><form class="admin-form account-form" action="{{ route('admin.account.profile.update') }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
                <div class="account-photo-preview">@if($adminProfile['photo'])<img src="{{ asset('storage/'.$adminProfile['photo']) }}" alt="Foto admin">@else<i class="bi bi-person"></i>@endif</div>
                <label>Nama admin<input name="name" required maxlength="120" value="{{ old('name', $adminProfile['name']) }}"></label>
                <label>Foto admin<input type="file" name="photo" accept="image/jpeg,image/png,image/webp"><small>Opsional · JPG, PNG, atau WebP · maks. 5 MB.</small></label>
                <label>Nomor WhatsApp admin<input type="tel" name="whatsapp" inputmode="tel" maxlength="30" value="{{ old('whatsapp', $adminProfile['whatsapp']) }}" placeholder="6281234567890"><small>Gunakan kode negara, misalnya 6281234567890. Dipakai untuk tombol konsultasi.</small></label>
                <button class="admin-button" type="submit"><i class="bi bi-check2"></i> Simpan profil</button>
            </form>
            <form class="admin-form account-form" action="{{ route('admin.account.password.update') }}" method="POST">@csrf @method('PUT')
                <h3><i class="bi bi-shield-lock"></i> Ubah kata sandi</h3>
                <label>Kata sandi saat ini<input type="password" name="current_password" autocomplete="current-password" required></label>
                <label>Kata sandi baru<input type="password" name="new_password" autocomplete="new-password" minlength="10" required><small>Minimal 10 karakter.</small></label>
                <label>Ulangi kata sandi baru<input type="password" name="new_password_confirmation" autocomplete="new-password" minlength="10" required></label>
                <button class="admin-button" type="submit"><i class="bi bi-key"></i> Perbarui kata sandi</button>
            </form></div>
        </section>
        @endif

        @if($section === 'schedule')
        <section class="admin-panel dashboard-panel"><div class="panel-heading"><div><span class="panel-kicker">PERENCANAAN KERJA</span><h2>Jadwal proyek pribadi</h2><p>Catat agenda, deadline, progres pekerjaan, dan pembayaran proyek.</p></div><div class="schedule-heading-actions"><span class="panel-count">{{ $workTasks->count() }} agenda</span><a class="admin-button" href="{{ route('admin.work-tasks.export') }}"><i class="bi bi-file-earmark-spreadsheet"></i> Ekspor CSV</a></div></div>
            <div class="schedule-finance-grid"><article><span>Total nilai proyek</span><strong>Rp {{ number_format($workTasks->sum('project_value'), 0, ',', '.') }}</strong></article><article><span>Sudah dibayar</span><strong>Rp {{ number_format($workTasks->sum('amount_paid'), 0, ',', '.') }}</strong></article><article><span>Sisa pembayaran</span><strong>Rp {{ number_format($workTasks->sum(fn($task) => max(0, (float)$task->project_value - (float)$task->amount_paid)), 0, ',', '.') }}</strong></article></div>
            @if($overdueDeadlineCount || $nearDeadlineCount)<div class="deadline-alerts">@if($overdueDeadlineCount)<div class="deadline-alert overdue"><i class="bi bi-exclamation-octagon"></i><span><strong>{{ $overdueDeadlineCount }} deadline terlambat</strong><small>Periksa agenda yang sudah melewati tenggat.</small></span></div>@endif @if($nearDeadlineCount)<div class="deadline-alert soon"><i class="bi bi-alarm"></i><span><strong>{{ $nearDeadlineCount }} deadline dalam 3 hari</strong><small>Pastikan pekerjaan yang mendekati tenggat terjadwal.</small></span></div>@endif</div>@endif
            <div class="work-calendar" data-calendar><div class="calendar-toolbar"><div><span class="panel-kicker">KALENDER KERJA</span><h3 data-calendar-title></h3></div><div class="calendar-controls"><button type="button" data-calendar-prev aria-label="Periode sebelumnya"><i class="bi bi-chevron-left"></i></button><button type="button" data-calendar-today>Hari ini</button><button type="button" data-calendar-next aria-label="Periode berikutnya"><i class="bi bi-chevron-right"></i></button><div class="calendar-view-toggle"><button type="button" data-calendar-view="month" class="active">Bulan</button><button type="button" data-calendar-view="week">Minggu</button></div></div></div><div class="calendar-weekdays"><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span></div><div class="calendar-grid" data-calendar-grid></div><div class="calendar-legend"><span><i class="schedule-dot"></i> Jadwal</span><span><i class="deadline-dot"></i> Deadline</span><span><i class="overdue-dot"></i> Terlambat</span></div></div>
            <div class="calendar-source" aria-hidden="true">@foreach($workTasks as $task)<span data-title="{{ $task->title }}" data-start="{{ $task->scheduled_at->toDateString() }}" data-start-time="{{ $task->scheduled_at->format('H:i') }}" data-deadline="{{ $task->deadline_at?->toDateString() }}" data-status="{{ $task->status }}"></span>@endforeach</div>
            <form class="admin-form schedule-create-form" action="{{ route('admin.work-tasks.store') }}" method="POST">@csrf
                <label>Nama pekerjaan<input name="title" required maxlength="255" placeholder="Contoh: Membuat landing page"></label><label>Klien / proyek<input name="client" maxlength="255" placeholder="Opsional"></label>
                <label>Jadwal mulai<input type="datetime-local" name="scheduled_at" required value="{{ now()->format('Y-m-d\TH:i') }}"></label><label>Deadline (opsional)<input type="datetime-local" name="deadline_at"></label>
                <label>Prioritas<select name="priority"><option value="normal">Normal</option><option value="high">Tinggi</option><option value="low">Rendah</option></select></label><label>Status<select name="status"><option value="planned">Terencana</option><option value="in_progress">Sedang dikerjakan</option><option value="done">Selesai</option></select></label>
                <label>Nilai proyek (Rp)<input type="number" name="project_value" min="0" step="1000" value="0"></label><label>Sudah dibayar (Rp)<input type="number" name="amount_paid" min="0" step="1000" value="0"></label><label>Status pembayaran<select name="payment_status"><option value="unpaid">Belum dibayar</option><option value="partial">Dibayar sebagian</option><option value="paid">Lunas</option></select></label>
                <label class="wide">Catatan<textarea name="description" rows="2" maxlength="3000" placeholder="Rincian atau catatan pekerjaan"></textarea></label><button class="admin-button" type="submit"><i class="bi bi-plus-lg"></i> Tambahkan jadwal</button>
            </form>
            <label class="admin-search"><i class="bi bi-search"></i><input type="search" data-admin-search="tasks" placeholder="Cari agenda, klien, atau catatan..."></label><p class="admin-sub search-empty" data-search-empty="tasks" hidden>Tidak ada agenda yang cocok.</p><div class="work-task-list">@forelse($workTasks as $task)<details class="work-task-card" data-search-item="tasks"><summary><span class="work-task-date"><strong>{{ $task->scheduled_at->format('d') }}</strong><small>{{ $task->scheduled_at->format('M Y') }}</small></span><span class="work-task-summary"><strong>{{ $task->title }}</strong><small>{{ $task->client ?: 'Proyek pribadi' }} · {{ $task->scheduled_at->format('H:i') }}@if($task->deadline_at) · Deadline {{ $task->deadline_at->format('d M Y') }}@endif</small></span><span class="task-state {{ $task->status }}">{{ ['planned' => 'Terencana', 'in_progress' => 'Dikerjakan', 'done' => 'Selesai'][$task->status] }}</span><i class="bi bi-chevron-down"></i></summary>
                <form class="admin-form work-task-edit" action="{{ route('admin.work-tasks.update', $task) }}" method="POST">@csrf @method('PUT')<label>Nama pekerjaan<input name="title" required maxlength="255" value="{{ $task->title }}"></label><label>Klien / proyek<input name="client" maxlength="255" value="{{ $task->client }}"></label><label>Jadwal mulai<input type="datetime-local" name="scheduled_at" required value="{{ $task->scheduled_at->format('Y-m-d\TH:i') }}"></label><label>Deadline<input type="datetime-local" name="deadline_at" value="{{ $task->deadline_at?->format('Y-m-d\TH:i') }}"></label><label>Prioritas<select name="priority"><option value="low" @selected($task->priority==='low')>Rendah</option><option value="normal" @selected($task->priority==='normal')>Normal</option><option value="high" @selected($task->priority==='high')>Tinggi</option></select></label><label>Status<select name="status"><option value="planned" @selected($task->status==='planned')>Terencana</option><option value="in_progress" @selected($task->status==='in_progress')>Sedang dikerjakan</option><option value="done" @selected($task->status==='done')>Selesai</option></select></label><label class="wide">Rincian pekerjaan<textarea name="description" rows="2" maxlength="3000">{{ $task->description }}</textarea></label><label>Nilai proyek (Rp)<input type="number" name="project_value" min="0" step="1000" value="{{ $task->project_value }}"></label><label>Sudah dibayar (Rp)<input type="number" name="amount_paid" min="0" step="1000" value="{{ $task->amount_paid }}"></label><label>Status pembayaran<select name="payment_status"><option value="unpaid" @selected($task->payment_status==='unpaid')>Belum dibayar</option><option value="partial" @selected($task->payment_status==='partial')>Dibayar sebagian</option><option value="paid" @selected($task->payment_status==='paid')>Lunas</option></select></label><label class="wide">Catatan progres<textarea name="progress_notes" rows="4" maxlength="5000" placeholder="Catat pekerjaan yang sudah selesai dan langkah berikutnya">{{ $task->progress_notes }}</textarea></label><button class="admin-button" type="submit"><i class="bi bi-check2"></i> Simpan perubahan</button></form>
                @php($availablePaymentAmount = max(0, (int)$task->project_value - (int)$task->amount_paid - (int)$task->payments->whereIn('status', ['pending','challenge'])->sum('gross_amount')))<div class="task-payments"><div class="task-payments-heading"><strong><i class="bi bi-credit-card-2-front"></i> Tagihan online</strong><span>Midtrans</span></div>
                    @if($task->payments->isNotEmpty())<div class="payment-link-list">@foreach($task->payments->sortByDesc('created_at') as $payment)<div class="payment-link-row"><span><strong>Rp {{ number_format($payment->gross_amount, 0, ',', '.') }}</strong><small>{{ $payment->customer_name }} · {{ ['pending'=>'Menunggu pembayaran','paid'=>'Lunas','denied'=>'Ditolak','cancelled'=>'Dibatalkan','expired'=>'Kedaluwarsa','challenge'=>'Perlu verifikasi'][$payment->status] ?? ucfirst($payment->status) }}</small></span><button class="admin-button payment-copy" type="button" data-copy="{{ route('payments.show', $payment->public_token) }}" @disabled(!$payment->snap_token)><i class="bi bi-link-45deg"></i> Salin link</button></div>@endforeach</div>@endif
                    @if($midtransConfigured && $availablePaymentAmount >= 1000)<form class="admin-form payment-create-form" action="{{ route('admin.work-tasks.payment-links.store', $task) }}" method="POST">@csrf<label>Nama klien<input name="customer_name" required maxlength="120" placeholder="Nama penerima tagihan"></label><label>Email (opsional)<input type="email" name="customer_email" maxlength="190" placeholder="nama@email.com"></label><label>Nominal tagihan (Rp)<input type="number" name="amount" required min="1000" step="1000" max="{{ $availablePaymentAmount }}" value="{{ $availablePaymentAmount }}"></label><button class="admin-button" type="submit"><i class="bi bi-plus-circle"></i> Buat tagihan</button></form>@elseif(!$midtransConfigured)<p class="payment-gateway-note"><i class="bi bi-info-circle"></i> Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY di .env untuk mengaktifkan pembayaran online.</p>@else<p class="payment-gateway-note"><i class="bi bi-check-circle"></i> Sisa proyek sudah tertagih atau lunas.</p>@endif
                </div>
                <form action="{{ route('admin.work-tasks.delete', $task) }}" method="POST" data-confirm="Hapus jadwal ini? Tindakan ini tidak dapat dibatalkan.">@csrf @method('DELETE')<button class="admin-button danger" type="submit"><i class="bi bi-trash3"></i> Hapus jadwal</button></form></details>@empty<p class="admin-sub">Belum ada jadwal. Gunakan formulir di atas untuk merencanakan pekerjaan pertama.</p>@endforelse</div>
        </section>
        @endif
        @if($section === 'marketplace')
        <section class="admin-panel dashboard-panel marketplace-admin-panel">
            <div class="panel-heading"><div><span class="panel-kicker">ETALASE & PESANAN</span><h2>Kelola marketplace</h2><p>Tambah barang, atur stok dan harga, lalu pantau pembayaran serta pengiriman.</p></div><a class="admin-button" href="{{ route('marketplace.index') }}" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Lihat toko</a></div>
            <form class="admin-form marketplace-payment-settings" action="{{ route('admin.marketplace.payment-settings.update') }}" method="POST" enctype="multipart/form-data" data-loading-title="Menyimpan pengaturan pembayaran..." data-loading-description="Memperbarui rekening dan QRIS toko.">@csrf @method('PUT')<div class="wide"><span class="panel-kicker">PEMBAYARAN MANUAL</span><h3>Rekening bank & QRIS</h3><p class="admin-sub">Isi rekening bank, unggah gambar QRIS, atau aktifkan keduanya. Pelanggan mengirim bukti dan Anda memverifikasinya di daftar pesanan.</p></div><label>Nama bank<input name="bank_name" maxlength="100" value="{{ $marketplaceBankName }}" placeholder="Contoh: BCA"></label><label>Nomor rekening<input name="bank_account" maxlength="60" value="{{ $marketplaceBankAccount }}" placeholder="Nomor rekening"></label><label>Nama pemilik rekening<input name="bank_holder" maxlength="120" value="{{ $marketplaceBankHolder }}" placeholder="Nama sesuai rekening"></label><div class="marketplace-qris-control"><label>Gambar QRIS<input type="file" name="qris_image" accept="image/jpeg,image/png,image/webp"></label>@if($marketplaceQrisImage)<small>QRIS tersimpan · <a href="{{ asset('storage/'.$marketplaceQrisImage) }}" target="_blank" rel="noreferrer">Lihat gambar</a></small><label class="marketplace-remove-qris"><input type="checkbox" name="remove_qris" value="1"> Hapus QRIS saat menyimpan</label>@else<small>JPG, PNG, atau WebP · maks. 5 MB</small>@endif</div><button class="admin-button" type="submit"><i class="bi bi-check2"></i> Simpan pembayaran</button></form>
            <form class="marketplace-shipping-setting marketplace-zone-setting" action="{{ route('admin.marketplace.shipping-fee.update') }}" method="POST" data-loading-title="Menyimpan tarif ongkir..." data-loading-description="Tarif wilayah akan digunakan pada checkout berikutnya.">@csrf @method('PUT')<div><strong><i class="bi bi-truck"></i> Tarif ongkir per wilayah</strong><small>Asal pengiriman: Piyungan, Bantul, Yogyakarta. Tentukan tarif sesuai jarak ke setiap wilayah tujuan.</small></div><label>Wilayah tujuan | tarif (Rp)<textarea name="shipping_zones" rows="5" required placeholder="Piyungan | nominal ongkir&#10;Bantul | nominal ongkir&#10;Kota Yogyakarta | nominal ongkir">{{ old('shipping_zones', $marketplaceShippingZones) }}</textarea></label><button class="admin-button" type="submit">Simpan tarif</button></form>
            <form class="admin-form marketplace-create-form" action="{{ route('admin.marketplace.products.store') }}" method="POST" enctype="multipart/form-data" data-loading-title="Menambahkan barang..." data-loading-description="Menyimpan informasi barang ke etalase.">@csrf
                <label>Nama barang<input name="name" required maxlength="180" placeholder="Contoh: Kaos Nadhim"></label><label>Kategori<input name="category" maxlength="100" placeholder="Opsional"></label><label>Harga (Rp)<input type="number" name="price" required min="1000" step="1000" placeholder="150000"></label><label>Stok<input type="number" name="stock" required min="0" value="1"></label><label>Status<select name="is_active" required><option value="1">Tampil di toko</option><option value="0">Disembunyikan</option></select></label><label>Foto barang<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label><label class="wide">Deskripsi<textarea name="description" rows="3" required maxlength="5000" placeholder="Jelaskan detail barang, ukuran, bahan, atau isi paket."></textarea></label><button class="admin-button" type="submit"><i class="bi bi-plus-lg"></i> Tambah barang</button>
            </form>
            <div class="marketplace-admin-grid">@forelse($marketplaceProducts as $product)<details class="marketplace-product-admin"><summary><span class="marketplace-admin-thumb">@if($product->image)<img src="{{ asset('storage/'.$product->image) }}" alt="">@else<i class="bi bi-bag"></i>@endif</span><span class="marketplace-admin-summary"><strong>{{ $product->name }}</strong><small>Rp {{ number_format($product->price, 0, ',', '.') }} · Stok {{ $product->stock }}</small></span><span class="marketplace-admin-status {{ $product->is_active ? 'active' : '' }}">{{ $product->is_active ? 'Tayang' : 'Disembunyikan' }}</span><i class="bi bi-chevron-down"></i></summary>
                <form class="admin-form marketplace-edit-form" action="{{ route('admin.marketplace.products.update', $product) }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')<label>Nama barang<input name="name" required maxlength="180" value="{{ $product->name }}"></label><label>Kategori<input name="category" maxlength="100" value="{{ $product->category }}"></label><label>Harga (Rp)<input type="number" name="price" min="1000" step="1000" required value="{{ $product->price }}"></label><label>Stok<input type="number" name="stock" min="0" required value="{{ $product->stock }}"></label><label>Status<select name="is_active"><option value="1" @selected($product->is_active)>Tampil di toko</option><option value="0" @selected(!$product->is_active)>Disembunyikan</option></select></label><label>Ganti foto<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label><label class="wide">Deskripsi<textarea name="description" rows="3" required maxlength="5000">{{ $product->description }}</textarea></label><button class="admin-button" type="submit"><i class="bi bi-check2"></i> Simpan barang</button></form>
                <form class="marketplace-delete-form" action="{{ route('admin.marketplace.products.destroy', $product) }}" method="POST" data-confirm="Hapus barang ini dari marketplace? Pesanan berbayar tetap perlu disimpan.">@csrf @method('DELETE')<button class="admin-button danger" type="submit"><i class="bi bi-trash3"></i> Hapus barang</button></form></details>@empty<p class="admin-sub">Belum ada barang. Tambahkan barang pertama agar toko dapat mulai ditampilkan.</p>@endforelse</div>
        </section>
        <section class="admin-panel dashboard-panel marketplace-orders-panel"><div class="panel-heading"><div><span class="panel-kicker">PENJUALAN</span><h2>Pesanan masuk</h2><p>{{ $marketplaceOrders->count() }} pesanan tersimpan · pembayaran diverifikasi manual.</p></div><div class="marketplace-order-counts"><span class="panel-count needs-review">{{ $marketplaceOrders->where('payment_status', 'proof_submitted')->count() }} perlu verifikasi</span><span class="panel-count">{{ $marketplaceOrders->where('payment_status', 'paid')->count() }} dibayar</span></div></div>
            <div class="marketplace-order-list">@forelse($marketplaceOrders as $order)<details class="marketplace-order"><summary><span class="order-number"><strong>#{{ substr($order->order_id, -8) }}</strong><small>{{ $order->created_at->format('d M Y, H:i') }}</small></span><span class="order-customer"><strong>{{ $order->customer_name }}</strong><small>{{ $order->items->isNotEmpty() ? $order->items->count().' jenis barang · '.$order->quantity.' pcs' : $order->product_name.' × '.$order->quantity }}</small></span><strong class="order-total">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong><span class="order-state {{ $order->payment_status === 'paid' ? 'paid' : '' }}">{{ ['pending' => 'Menunggu bayar','proof_submitted' => 'Bukti diterima','rejected' => 'Bukti perlu diperbaiki','paid' => 'Lunas','denied' => 'Ditolak','cancelled' => 'Dibatalkan','expired' => 'Kedaluwarsa'][$order->payment_status] ?? ucfirst($order->payment_status) }}</span><i class="bi bi-chevron-down"></i></summary>
                <div class="marketplace-order-detail"><div class="order-items-admin"><span>Barang dipesan</span>@foreach($order->items as $item)<strong>{{ $item->product_name }} × {{ $item->quantity }} <small>Rp {{ number_format($item->line_total, 0, ',', '.') }}</small></strong>@endforeach</div><div><span>Subtotal barang</span><strong>Rp {{ number_format($order->subtotal_amount ?: $order->total_amount, 0, ',', '.') }}</strong></div><div><span>Ongkos kirim</span><strong>Rp {{ number_format($order->shipping_amount, 0, ',', '.') }}</strong></div><div><span>Telepon</span><strong>{{ $order->customer_phone }}</strong></div><div><span>Email</span><strong>{{ $order->customer_email ?: '—' }}</strong></div><div class="order-address"><span>Alamat pengiriman</span><strong>{{ $order->shipping_address }}</strong></div><div><span>Metode bayar</span><strong>{{ $order->payment_method === 'qris' ? 'QRIS' : ($order->payment_method === 'bank_transfer' ? 'Transfer bank' : '—') }}</strong></div>
                    @if($order->payment_proof_path)<div><span>Bukti pembayaran</span><strong><a href="{{ route('admin.marketplace.orders.proof', $order) }}" target="_blank" rel="noreferrer">Lihat bukti</a></strong></div>@endif @if($order->payment_note)<div><span>Catatan verifikasi</span><strong>{{ $order->payment_note }}</strong></div>@endif
                    @if($order->payment_status === 'proof_submitted')<form action="{{ route('admin.marketplace.orders.payment-review', $order) }}" method="POST" data-confirm="Simpan keputusan verifikasi untuk pesanan ini?" data-loading-title="Memperbarui status pembayaran..." data-loading-description="Status pesanan sedang diperbarui.">@csrf @method('PUT')<label>Catatan jika bukti ditolak<input name="payment_note" maxlength="500" placeholder="Contoh: nominal belum sesuai"></label><button class="admin-button" name="decision" value="approve" type="submit"><i class="bi bi-check-circle"></i> Verifikasi pembayaran</button><button class="admin-button danger" name="decision" value="reject" type="submit"><i class="bi bi-x-circle"></i> Tolak bukti</button></form>@endif
                    @if($order->payment_status === 'pending')<button class="admin-button payment-copy" type="button" data-copy="{{ route('marketplace.checkout', $order->public_token) }}"><i class="bi bi-link-45deg"></i> Salin link pesanan</button>@endif @if($order->payment_status === 'paid')<form action="{{ route('admin.marketplace.orders.update', $order) }}" method="POST">@csrf @method('PUT')<label>Status pengiriman<select name="status"><option value="awaiting_fulfillment" @selected($order->status==='awaiting_fulfillment')>Perlu diproses</option><option value="processing" @selected($order->status==='processing')>Sedang disiapkan</option><option value="shipped" @selected($order->status==='shipped')>Sudah dikirim</option><option value="completed" @selected($order->status==='completed')>Selesai</option></select></label><button class="admin-button" type="submit"><i class="bi bi-check2"></i> Perbarui status</button></form>@endif
                </div></details>@empty<p class="admin-sub">Belum ada pesanan masuk. Pesanan pelanggan akan muncul di sini.</p>@endforelse</div>
        </section>
        @endif
        @if($section === 'data-backup')
        <section class="admin-panel dashboard-panel backup-panel">
            <div class="backup-hero"><span class="backup-hero-icon"><i class="bi bi-shield-check"></i></span><div><span class="panel-kicker">PUSAT KEAMANAN DATA</span><h2>Backup & pemulihan</h2><p>Siapkan salinan data sebelum terjadi gangguan. Arsip dapat dipulihkan setelah aplikasi dan database kembali aktif.</p></div></div>
            <div class="backup-offsite-note"><i class="bi bi-cloud-check"></i><div><strong>Simpan salinan di luar server</strong><p>Setelah mengunduh file ZIP, salin ke Google Drive, penyimpanan cloud lain, atau hard disk eksternal. Jika server mati, halaman ini ikut tidak bisa diakses; salinan di luar server yang dapat digunakan untuk pemulihan.</p></div></div>
            <div class="backup-inventory"><div class="backup-inventory-heading"><span class="panel-kicker">ISI YANG AKAN DICADANGKAN</span><small>Dibuat langsung saat Anda mengunduh</small></div><div class="backup-inventory-grid"><article><i class="bi bi-images"></i><strong>{{ $projects->count() }}</strong><span>Proyek</span></article><article><i class="bi bi-newspaper"></i><strong>{{ $newsArticles->count() }}</strong><span>Berita</span></article><article><i class="bi bi-calendar-week"></i><strong>{{ $workTasks->count() }}</strong><span>Agenda kerja</span></article><article><i class="bi bi-credit-card"></i><strong>{{ $workTasks->sum(fn($task) => $task->payments->count()) }}</strong><span>Tagihan proyek</span></article><article><i class="bi bi-bag"></i><strong>{{ $marketplaceProducts->count() }}</strong><span>Barang toko</span></article><article><i class="bi bi-receipt"></i><strong>{{ $marketplaceOrders->count() }}</strong><span>Pesanan toko</span></article><article><i class="bi bi-image"></i><strong>{{ $projects->whereNotNull('image')->count() + $newsArticles->whereNotNull('image')->count() + $marketplaceProducts->whereNotNull('image')->count() }}</strong><span>Gambar terkait</span></article></div></div>
            <div class="backup-steps"><article><span>01</span><div><strong>Unduh arsip</strong><small>Buat ZIP berisi data dan gambar.</small></div></article><article><span>02</span><div><strong>Simpan di tempat aman</strong><small>Pisahkan salinan dari server ini.</small></div></article><article><span>03</span><div><strong>Pulihkan saat dibutuhkan</strong><small>Unggah ZIP setelah aplikasi aktif.</small></div></article></div>
            <div class="backup-action-grid"><article class="backup-download-card"><span class="backup-action-icon"><i class="bi bi-cloud-arrow-down"></i></span><div><h3>Unduh backup sekarang</h3><p>Arsip ZIP menyertakan proyek, berita, agenda, riwayat pembayaran, barang marketplace, pesanan, dan gambar yang tersimpan. Simpan file hasil unduhan di luar server.</p><a class="admin-button" href="{{ route('admin.backup.download') }}"><i class="bi bi-download"></i> Buat & unduh backup</a></div></article>
                <article class="backup-restore-card"><span class="backup-action-icon"><i class="bi bi-arrow-counterclockwise"></i></span><div><h3>Pulihkan dari backup</h3><p>Pilih arsip ZIP yang pernah diunduh. Sistem memeriksa format dan menambahkan data yang belum ada.</p><form action="{{ route('admin.backup.restore') }}" method="POST" enctype="multipart/form-data" class="backup-restore-form">@csrf<label>Pilih arsip backup<input type="file" name="backup" accept=".zip,application/zip" required data-backup-file></label><small class="backup-file-name" data-backup-file-name>Belum ada file dipilih · ZIP maks. 100 MB</small><button class="admin-button" type="submit" data-loading-title="Memeriksa dan memulihkan..." data-loading-description="Memvalidasi file backup lalu menambahkan data yang belum ada."><i class="bi bi-arrow-counterclockwise"></i> Validasi & pulihkan</button></form></div></article>
            </div>
            <div class="backup-restore-note"><i class="bi bi-info-circle"></i><span><strong>Pemulihan aman:</strong> data yang sudah ada tidak ditimpa atau dihapus. Duplikat proyek, berita, dan agenda akan dilewati.</span></div>
        </section>
        @endif        <footer class="dashboard-footer">Nadhim Alim · Ruang pengelolaan portofolio</footer>
    </main>
</div>
<script>
document.querySelectorAll('[data-photo-preview]').forEach((input) => {
    input.addEventListener('change', () => {
        const file = input.files?.[0];
        const preview = document.getElementById(input.dataset.photoPreview);
        const filename = input.closest('label')?.querySelector('.selected-photo-name');
        if (!file) return;
        if (filename) filename.textContent = file.name;
        if (preview && file.type.startsWith('image/')) {
            const image = preview.querySelector('img') || document.createElement('img');
            image.src = URL.createObjectURL(file);
            image.alt = 'Pratinjau foto yang dipilih';
            preview.replaceChildren(image);
        }
    });
});
</script>
<script>
document.querySelectorAll('[data-copy]').forEach((button) => button.addEventListener('click', async () => {
    const original = button.innerHTML;
    try {
        await navigator.clipboard.writeText(button.dataset.copy);
        button.innerHTML = '<i class="bi bi-check2"></i> Tersalin';
        setTimeout(() => { button.innerHTML = original; }, 1800);
    } catch {
        window.prompt('Salin link pembayaran ini:', button.dataset.copy);
    }
}));
(() => {
    const backupInput = document.querySelector('[data-backup-file]');
    backupInput?.addEventListener('change', () => {
        const file = backupInput.files?.[0];
        const label = document.querySelector('[data-backup-file-name]');
        if (label) label.textContent = file ? `${file.name} · ${(file.size / 1024 / 1024).toFixed(2)} MB` : 'Belum ada file dipilih · ZIP maks. 100 MB';
    });
    document.querySelectorAll('[data-admin-search]').forEach((input) => {
        const group = input.dataset.adminSearch;
        const items = [...document.querySelectorAll(`[data-search-item="${group}"]`)];
        const empty = document.querySelector(`[data-search-empty="${group}"]`);
        input.addEventListener('input', () => {
            const query = input.value.trim().toLocaleLowerCase('id');
            let visible = 0;
            items.forEach((item) => {
                const match = item.textContent.toLocaleLowerCase('id').includes(query);
                item.hidden = !match;
                if (match) visible++;
            });
            if (empty) empty.hidden = visible > 0 || query.length === 0;
        });
    });
    const clock = document.querySelector('[data-live-clock]');
    const dateLabel = document.querySelector('[data-live-date]');
    const calendarGrid = document.querySelector('[data-overview-calendar]');
    if (clock && dateLabel) {
        const updateClock = () => {
            const now = new Date();
            clock.textContent = new Intl.DateTimeFormat('id-ID', { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23' }).format(now);
            dateLabel.textContent = new Intl.DateTimeFormat('id-ID', { timeZone: 'Asia/Jakarta', weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(now);
        };
        updateClock(); window.setInterval(updateClock, 1000);
    }
    if (calendarGrid) {
        const tasks = new Set([...document.querySelectorAll('.overview-calendar-source [data-date]')].map((item) => item.dataset.date));
        const monthTitle = document.querySelector('[data-overview-month]');
        const current = new Date();
        let viewedMonth = new Date(current.getFullYear(), current.getMonth(), 1);
        const keyFor = (date) => `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}`;
        const renderCalendar = () => {
            monthTitle.textContent = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(viewedMonth);
            calendarGrid.replaceChildren();
            ['Sen','Sel','Rab','Kam','Jum','Sab','Min'].forEach((day) => { const item=document.createElement('span'); item.className='overview-weekday'; item.textContent=day; calendarGrid.append(item); });
            const first = new Date(viewedMonth.getFullYear(), viewedMonth.getMonth(), 1);
            const offset = (first.getDay()+6)%7;
            const days = new Date(viewedMonth.getFullYear(), viewedMonth.getMonth()+1, 0).getDate();
            for(let blank=0; blank<offset; blank++){ const empty=document.createElement('span'); empty.className='overview-calendar-day blank'; calendarGrid.append(empty); }
            for(let day=1; day<=days; day++){
                const date = new Date(viewedMonth.getFullYear(), viewedMonth.getMonth(), day);
                const item=document.createElement('span'); item.className=`overview-calendar-day${keyFor(date)===keyFor(current)?' today':''}${tasks.has(keyFor(date))?' has-task':''}`; item.textContent=day; calendarGrid.append(item);
            }
        };
        document.querySelector('[data-overview-prev]')?.addEventListener('click',()=>{viewedMonth.setMonth(viewedMonth.getMonth()-1);renderCalendar();});
        document.querySelector('[data-overview-next]')?.addEventListener('click',()=>{viewedMonth.setMonth(viewedMonth.getMonth()+1);renderCalendar();});
        renderCalendar();
    }
})();
(() => {
    const calendar = document.querySelector('[data-calendar]');
    if (!calendar) return;
    const grid = calendar.querySelector('[data-calendar-grid]');
    const title = calendar.querySelector('[data-calendar-title]');
    const tasks = [...document.querySelectorAll('.calendar-source span')].map((item) => ({
        title: item.dataset.title, start: item.dataset.start, time: item.dataset.startTime,
        deadline: item.dataset.deadline, status: item.dataset.status,
    }));
    let current = new Date();
    let mode = 'month';
    const dateKey = (date) => `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}`;
    const monday = (date) => { const copy = new Date(date.getFullYear(), date.getMonth(), date.getDate()); copy.setDate(copy.getDate() - ((copy.getDay()+6)%7)); return copy; };
    const todayKey = dateKey(new Date());
    const render = () => {
        const start = mode === 'month' ? monday(new Date(current.getFullYear(), current.getMonth(), 1)) : monday(current);
        const count = mode === 'month' ? 42 : 7;
        const end = new Date(start); end.setDate(end.getDate()+count-1);
        const formatter = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' });
        title.textContent = mode === 'month' ? formatter.format(current) : `${start.getDate()} ${new Intl.DateTimeFormat('id-ID',{month:'short'}).format(start)} – ${end.getDate()} ${formatter.format(end)}`;
        grid.replaceChildren();
        for (let offset=0; offset<count; offset++) {
            const date = new Date(start); date.setDate(start.getDate()+offset);
            const key = dateKey(date);
            const cell = document.createElement('div');
            cell.className = `calendar-day${key===todayKey?' today':''}${mode==='month'&&date.getMonth()!==current.getMonth()?' outside':''}`;
            const number = document.createElement('span'); number.className='calendar-day-number'; number.textContent=date.getDate(); cell.append(number);
            tasks.forEach((task) => {
                if (task.start === key) { const event=document.createElement('span'); event.className=`calendar-event schedule-event ${task.status}`; event.textContent=`${task.time} ${task.title}`; event.title=`Jadwal: ${task.title}`; cell.append(event); }
                if (task.deadline === key && task.status !== 'done') { const event=document.createElement('span'); const delta=Math.floor((new Date(`${key}T00:00:00`)-new Date(`${todayKey}T00:00:00`))/86400000); event.className=`calendar-event deadline-event${delta<0?' overdue':delta<=3?' soon':''}`; event.textContent=`Tenggat · ${task.title}`; event.title=`Deadline: ${task.title}`; cell.append(event); }
            });
            grid.append(cell);
        }
    };
    calendar.querySelector('[data-calendar-prev]').addEventListener('click',()=>{ if(mode==='month') current.setMonth(current.getMonth()-1); else current.setDate(current.getDate()-7); render(); });
    calendar.querySelector('[data-calendar-next]').addEventListener('click',()=>{ if(mode==='month') current.setMonth(current.getMonth()+1); else current.setDate(current.getDate()+7); render(); });
    calendar.querySelector('[data-calendar-today]').addEventListener('click',()=>{ current=new Date(); render(); });
    calendar.querySelectorAll('[data-calendar-view]').forEach((button)=>button.addEventListener('click',()=>{ mode=button.dataset.calendarView; calendar.querySelectorAll('[data-calendar-view]').forEach((item)=>item.classList.toggle('active',item===button)); render(); }));
    render();
})();
(() => {
    const cards = [...document.querySelectorAll('[data-message-card]')];
    if (!cards.length) return;
    const search = document.querySelector('[data-message-search]');
    const filters = [...document.querySelectorAll('[data-message-filter]')];
    const empty = document.querySelector('[data-message-empty]');
    let statusFilter = 'all';
    const apply = () => {
        const query = (search?.value || '').trim().toLocaleLowerCase('id');
        let shown = 0;
        cards.forEach((card) => {
            const matchesStatus = statusFilter === 'all' || card.dataset.status === statusFilter;
            const matchesQuery = card.textContent.toLocaleLowerCase('id').includes(query);
            card.hidden = !(matchesStatus && matchesQuery);
            if (!card.hidden) shown++;
        });
        if (empty) empty.hidden = shown > 0;
    };
    search?.addEventListener('input', apply);
    filters.forEach((button) => button.addEventListener('click', () => {
        statusFilter = button.dataset.messageFilter;
        filters.forEach((item) => {
            const active = item === button;
            item.classList.toggle('active', active);
            item.setAttribute('aria-pressed', String(active));
        });
        apply();
    }));
    apply();
})();
</script>
<script src="{{ asset('js/feedback.js') }}" defer></script>
</body>
</html>
