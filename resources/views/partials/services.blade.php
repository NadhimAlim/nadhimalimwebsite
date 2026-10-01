<section class="section services-section" id="layanan">
    <div class="container">
        <div class="section-label"><span>03 / LAYANAN &amp; HARGA</span><i></i><span class="section-note">Harga awal, disesuaikan dengan kebutuhan</span></div>
        <div class="service-heading">
            <h2>Solusi digital,<br><em>sesuai kebutuhan.</em></h2>
            <p>Paket dirancang fleksibel. Ceritakan kebutuhan Anda untuk mendapatkan estimasi yang paling sesuai.</p>
        </div>
        <div class="public-service-grid">
            @forelse($services as $index => $service)
                <article class="public-service-card {{ $index === 2 ? 'featured' : '' }}">
                    <div class="public-service-icon"><i class="bi {{ $service->icon ?: 'bi-window' }}"></i></div>
                    <span class="public-service-index">PAKET {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <h3>{{ $service->title }}</h3>
                    <p>{{ $service->description }}</p>
                    <div class="service-price-label">Harga mulai dari</div>
                    <div class="service-price">Rp {{ number_format($service->starting_price, 0, ',', '.') }}</div>
                    <a href="#kontak" class="service-cta">Konsultasikan paket <i class="bi bi-arrow-up-right"></i></a>
                </article>
            @empty
                <div class="empty-project"><h3>Paket layanan segera hadir</h3><p>Hubungi saya untuk mendiskusikan kebutuhan digital Anda.</p></div>
            @endforelse
        </div>
    </div>
</section>
