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
</head>
<body class="admin-body">
<div class="dashboard-layout">
    <aside class="admin-sidebar">
        <a class="brand" href="{{ route('home') }}">NA<span>.</span></a>
        <div class="sidebar-caption">WORKSPACE</div>
        <a class="sidebar-link active" href="#overview"><i class="bi bi-grid-1x2"></i> Ringkasan</a>
        <a class="sidebar-link" href="#packages"><i class="bi bi-box-seam"></i> Paket jasa</a>
        <a class="sidebar-link" href="#projects"><i class="bi bi-images"></i> Portofolio</a>
        <a class="sidebar-link" href="#cv-panel"><i class="bi bi-file-earmark-person"></i> Dokumen CV</a>
        <div class="sidebar-bottom">
            <div class="admin-profile"><div class="admin-avatar">NA</div><div><strong>Nadhim Alim</strong><span>Administrator</span></div></div>
            <form action="{{ route('admin.logout') }}" method="POST">@csrf<button class="sidebar-logout" type="submit"><i class="bi bi-box-arrow-left"></i> Keluar</button></form>
        </div>
    </aside>

    <main class="dashboard-main" id="overview">
        <header class="dashboard-header"><div><span class="dashboard-breadcrumb">Workspace <i class="bi bi-chevron-right"></i> Ringkasan</span><h1>Dashboard</h1><p>Kelola layanan, karya, dan dokumen profesional Anda.</p></div><a href="{{ route('home') }}" class="view-site" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Lihat situs</a></header>

        @if(session('success'))
            <div class="alert"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert" style="background:#fff0ed;color:#9d3e2d"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
        @endif

        <section class="dashboard-metrics">
            <article class="metric-card"><span>Total proyek</span><div><strong>{{ $projects->count() }}</strong><i class="bi bi-images"></i></div><small>Karya di portofolio</small></article>
            <article class="metric-card"><span>Paket jasa</span><div><strong>{{ $services->count() }}</strong><i class="bi bi-box-seam"></i></div><small>Layanan yang ditawarkan</small></article>
            <article class="metric-card"><span>CV profesional</span><div><strong>{{ $cvPath ? 'Siap' : '—' }}</strong><i class="bi bi-file-earmark-check"></i></div><small>{{ $cvPath ? 'Tersedia untuk pengunjung' : 'Belum ada dokumen' }}</small></article>
        </section>

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
                        <form action="{{ route('admin.services.delete', $service) }}" method="POST" class="service-delete-form" onsubmit="return confirm('Hapus paket jasa ini?')">@csrf @method('DELETE')<button type="submit" aria-label="Hapus {{ $service->title }}"><i class="bi bi-trash3"></i></button></form>
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

        <section class="admin-panel dashboard-panel" id="projects">
            <div class="panel-heading"><div><span class="panel-kicker">KARYA PILIHAN</span><h2>Portofolio proyek</h2><p>Terbitkan hasil kerja terbaru Anda.</p></div><span class="panel-count">{{ $projects->count() }} proyek</span></div>
            <form class="admin-form project-create-form" action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label>Nama proyek<input name="title" required maxlength="255" value="{{ old('title') }}" placeholder="Contoh: Website portfolio"></label>
                <label>Kategori<input name="category" required maxlength="100" value="{{ old('category') }}" placeholder="Website, aplikasi, konten"></label>
                <label class="wide">Deskripsi<textarea name="description" rows="2" required maxlength="3000" placeholder="Ceritakan tentang proyek ini">{{ old('description') }}</textarea></label>
                <label>Link proyek<input name="link" type="url" value="{{ old('link') }}" placeholder="https://"></label>
                <label>Gambar sampul<input name="image" type="file" accept="image/*"><span class="admin-sub">JPG, PNG, atau WebP · maks. 5 MB</span></label>
                <button class="admin-button" type="submit"><i class="bi bi-cloud-arrow-up"></i> Terbitkan proyek</button>
            </form>
            <div class="admin-project-list">
                @forelse($projects as $project)
                    <div class="admin-project">
                        @if($project->image)
                            <img src="{{ asset('storage/'.$project->image) }}" alt="">
                        @else
                            <div class="project-placeholder"><i class="bi bi-image"></i></div>
                        @endif
                        <div class="admin-project-info"><strong>{{ $project->title }}</strong><span>{{ $project->category }} · {{ $project->created_at->format('d M Y') }}</span></div>
                        <form action="{{ route('admin.projects.delete', $project) }}" method="POST" onsubmit="return confirm('Hapus proyek ini?')">@csrf @method('DELETE')<button type="submit" class="admin-button danger"><i class="bi bi-trash3"></i> Hapus</button></form>
                    </div>
                @empty
                    <p class="admin-sub">Belum ada proyek. Tambahkan karya pertama melalui formulir di atas.</p>
                @endforelse
            </div>
        </section>

        <section class="admin-panel dashboard-panel cv-admin-panel" id="cv-panel">
            <div class="panel-heading"><div><span class="panel-kicker">DOKUMEN PROFESIONAL</span><h2>CV Anda</h2><p>PDF ini tersedia untuk diunduh dari halaman portofolio.</p></div><span class="cv-state {{ $cvPath ? 'ready' : '' }}"><i class="bi bi-circle-fill"></i> {{ $cvPath ? 'Sudah diunggah' : 'Belum diunggah' }}</span></div>
            <form action="{{ route('admin.cv.upload') }}" method="POST" enctype="multipart/form-data" class="cv-upload-form">@csrf<label class="cv-file-input"><i class="bi bi-file-earmark-pdf"></i><span><strong>Pilih file CV dalam format PDF</strong><small>Ukuran maksimal 10 MB. Unggahan baru menggantikan file lama.</small></span><input type="file" name="cv" accept="application/pdf,.pdf" required></label><button class="admin-button" type="submit"><i class="bi bi-cloud-arrow-up"></i> {{ $cvPath ? 'Perbarui CV' : 'Unggah CV' }}</button></form>
        </section>
        <footer class="dashboard-footer">Nadhim Alim · Ruang pengelolaan portofolio</footer>
    </main>
</div>
</body>
</html>
