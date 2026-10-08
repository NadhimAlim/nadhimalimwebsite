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
    <link rel="stylesheet" href="{{ asset('css/admin-polish.css') }}">
</head>
<body class="admin-body">
<div class="dashboard-layout">
    <aside class="admin-sidebar">
        <a class="brand" href="{{ route('home') }}">NA<span>.</span></a>
        <div class="sidebar-caption">WORKSPACE</div>
        <a class="sidebar-link {{ $section === 'overview' ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid-1x2"></i> Ringkasan</a>
        <a class="sidebar-link {{ $section === 'packages' ? 'active' : '' }}" href="{{ route('admin.dashboard.section', 'packages') }}"><i class="bi bi-box-seam"></i> Paket jasa</a>
        <a class="sidebar-link {{ $section === 'skills' ? 'active' : '' }}" href="{{ route('admin.dashboard.section', 'skills') }}"><i class="bi bi-stars"></i> Keahlian</a>
        <a class="sidebar-link {{ $section === 'projects' ? 'active' : '' }}" href="{{ route('admin.dashboard.section', 'projects') }}"><i class="bi bi-images"></i> Portofolio</a>
        <a class="sidebar-link {{ $section === 'profile-photo' ? 'active' : '' }}" href="{{ route('admin.dashboard.section', 'profile-photo') }}"><i class="bi bi-person-bounding-box"></i> Foto profil</a>
        <a class="sidebar-link {{ $section === 'cv-panel' ? 'active' : '' }}" href="{{ route('admin.dashboard.section', 'cv-panel') }}"><i class="bi bi-file-earmark-person"></i> Dokumen CV</a>
        <div class="sidebar-bottom">
            <div class="admin-profile"><div class="admin-avatar">NA</div><div><strong>Nadhim Alim</strong><span>Administrator</span></div></div>
            <form action="{{ route('admin.logout') }}" method="POST">@csrf<button class="sidebar-logout" type="submit"><i class="bi bi-box-arrow-left"></i> Keluar</button></form>
        </div>
    </aside>

    <main class="dashboard-main" id="overview">
        @php($sectionTitles = ['overview' => 'Ringkasan', 'packages' => 'Paket jasa', 'skills' => 'Keahlian', 'projects' => 'Portofolio', 'profile-photo' => 'Foto profil', 'cv-panel' => 'Dokumen CV'])
        <header class="dashboard-header"><div><span class="dashboard-breadcrumb">Workspace <i class="bi bi-chevron-right"></i> {{ $sectionTitles[$section] }}</span><h1>{{ $sectionTitles[$section] }}</h1><p>Kelola {{ $section === 'overview' ? 'layanan, karya, dan dokumen profesional Anda' : strtolower($sectionTitles[$section]) . ' portofolio Anda' }}.</p></div><a href="{{ route('home') }}" class="view-site" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Lihat situs</a></header>

        @if(session('success'))
            <div class="alert"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert" style="background:#fff0ed;color:#9d3e2d"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
        @endif

        @if($section === 'overview')
        <section class="dashboard-metrics">
            <article class="metric-card"><span>Total proyek</span><div><strong>{{ $projects->count() }}</strong><i class="bi bi-images"></i></div><small>Karya di portofolio</small></article>
            <article class="metric-card"><span>Paket jasa</span><div><strong>{{ $services->count() }}</strong><i class="bi bi-box-seam"></i></div><small>Layanan yang ditawarkan</small></article>
            <article class="metric-card"><span>CV profesional</span><div><strong>{{ $cvPath ? 'Siap' : '—' }}</strong><i class="bi bi-file-earmark-check"></i></div><small>{{ $cvPath ? 'Tersedia untuk pengunjung' : 'Belum ada dokumen' }}</small></article>
        </section>
        <section class="skill-summary-grid">
            @foreach(['hard' => 'Hard skill', 'soft' => 'Soft skill'] as $type => $title)
                @php($skillGroup = $skills->where('type', $type))
                <article class="skill-summary-card">
                    <div class="skill-summary-heading"><div><span class="panel-kicker">KEAHLIAN</span><h2>{{ $title }}</h2></div><span class="panel-count">{{ $skillGroup->count() }}</span></div>
                    @forelse($skillGroup as $skill)
                        <div class="skill-summary-item"><span>{{ $skill->name }}</span><strong>{{ $skill->level }}%</strong></div>
                    @empty
                        <p class="admin-sub">Belum ada {{ strtolower($title) }}.</p>
                    @endforelse
                    <a class="skill-summary-link" href="{{ route('admin.dashboard.section', 'skills') }}">Kelola keahlian <i class="bi bi-arrow-right"></i></a>
                </article>
            @endforeach
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
        @endif

        @if($section === 'skills')
        <section class="admin-panel dashboard-panel" id="skills">
            <div class="panel-heading"><div><span class="panel-kicker">KEMAMPUAN PROFESIONAL</span><h2>Soft skill & hard skill</h2><p>Tambahkan keahlian dan tingkat penguasaan yang akan tampil di halaman portofolio.</p></div><span class="panel-count">{{ $skills->count() }} keahlian</span></div>
            <form class="admin-form project-create-form" action="{{ route('admin.skills.store') }}" method="POST">
                @csrf
                <label>Nama keahlian<input name="name" required maxlength="100" placeholder="Contoh: Laravel atau Komunikasi"></label>
                <label>Jenis keahlian<select name="type" required><option value="hard">Hard skill</option><option value="soft">Soft skill</option></select></label>
                <label>Tingkat penguasaan (%)<input name="level" type="number" min="1" max="100" value="80" required></label>
                <button class="admin-button" type="submit"><i class="bi bi-plus-lg"></i> Tambahkan keahlian</button>
            </form>
            <div class="admin-project-list skill-admin-list">
                @forelse($skills as $skill)
                    <form class="skill-admin-row" action="{{ route('admin.skills.update', $skill) }}" method="POST">
                        @csrf @method('PUT')
                        <label>Nama<input name="name" required maxlength="100" value="{{ $skill->name }}"></label>
                        <label>Jenis<select name="type"><option value="hard" @selected($skill->type === 'hard')>Hard skill</option><option value="soft" @selected($skill->type === 'soft')>Soft skill</option></select></label>
                        <label>Penguasaan %<input name="level" type="number" min="1" max="100" value="{{ $skill->level }}" required></label>
                        <button class="admin-button" type="submit"><i class="bi bi-check2"></i> Simpan</button>
                        <button class="admin-button danger" type="submit" form="delete-skill-{{ $skill->id }}" onclick="return confirm('Hapus keahlian ini?')"><i class="bi bi-trash3"></i> Hapus</button>
                    </form>
                    <form id="delete-skill-{{ $skill->id }}" action="{{ route('admin.skills.delete', $skill) }}" method="POST">@csrf @method('DELETE')</form>
                @empty
                    <p class="admin-sub">Belum ada keahlian. Tambahkan soft skill atau hard skill melalui formulir di atas.</p>
                @endforelse
            </div>
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
            <div class="admin-project-list">
                @forelse($projects as $project)
                    <article class="admin-project-entry">
                    <div class="admin-project">
                        @if($project->image)
                            <img src="{{ asset('storage/'.$project->image) }}" alt="">
                        @else
                            <div class="project-placeholder"><i class="bi bi-image"></i></div>
                        @endif
                        <div class="admin-project-info"><strong>{{ $project->title }}</strong><span>{{ $project->category }} · {{ $project->created_at->format('d M Y') }}</span></div>
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
                        <form action="{{ route('admin.projects.delete', $project) }}" method="POST" onsubmit="return confirm('Hapus proyek ini?')">@csrf @method('DELETE')<button type="submit" class="admin-button danger"><i class="bi bi-trash3"></i> Hapus</button></form>
                    </div>
                    </article>
                @empty
                    <p class="admin-sub">Belum ada proyek. Tambahkan karya pertama melalui formulir di atas.</p>
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
        <footer class="dashboard-footer">Nadhim Alim · Ruang pengelolaan portofolio</footer>
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
</body>
</html>
