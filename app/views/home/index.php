<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-7 text-center text-lg-start">
                <div class="hero-badge">
                    <i class="bi bi-stars"></i> Sistem Resmi SMK Informatika Sumedang
                </div>
                <h1 class="hero-title"><?= APP_TAGLINE ?></h1>
                <p class="hero-subtitle">
                    <?= APP_DESCRIPTION ?>
                </p>

                <!-- Action Buttons -->
                <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3 mb-4">
                    <a href="<?= url('laporkan-hilang') ?>" class="btn btn-pastel-pink btn-rounded py-2 px-4 shadow-sm fw-semibold">
                        <i class="bi bi-search-heart me-2"></i> Saya Kehilangan Barang
                    </a>
                    <a href="<?= url('laporkan-ditemukan') ?>" class="btn btn-pastel-green btn-rounded py-2 px-4 shadow-sm fw-semibold">
                        <i class="bi bi-plus-circle me-2"></i> Saya Menemukan Barang
                    </a>
                    <a href="<?= url('download-app') ?>" class="btn btn-outline-primary btn-rounded py-2 px-4 shadow-sm fw-semibold" data-android-apk-download>
                        <i class="bi bi-download me-2"></i> Download Aplikasi
                    </a>
                </div>
                <div class="alert alert-info border-0 rounded-4 py-2 px-3 small text-start" role="alert">
                    <i class="bi bi-android2 me-1"></i>
                    APK hanya dapat dipasang di Android. Admin tetap mengakses panel melalui website.
                </div>

                <!-- Search Bar -->
                <form action="<?= url('barang') ?>" method="GET" class="search-container-pastel mt-4">
                    <i class="bi bi-search text-muted fs-5 ms-3"></i>
                    <input type="text" name="search" placeholder="Cari nama barang, warna, atau lokasi..." autocomplete="off">
                    <button type="submit" class="btn btn-navy btn-rounded px-4">
                        Cari
                    </button>
                </form>
            </div>

            <!-- Hero Illustration / Card Showcase -->
            <div class="col-lg-5">
                <div class="card card-custom p-4 border-0 shadow-lg" style="background: linear-gradient(145deg, #ffffff, #f4f8fe);">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon-wrapper" style="background-color: var(--pastel-blue); color: #1d4b79;">
                                <i class="bi bi-shield-check fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-navy">Layanan Terpadu</h6>
                                <small class="text-muted">Aman, Cepat & Terverifikasi</small>
                            </div>
                        </div>
                        <span class="badge badge-pastel-green">Aktif</span>
                    </div>

                    <div class="p-3 bg-white rounded-4 border mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small text-muted">Status Barang Temuan</span>
                            <span class="badge badge-pastel-purple">Tersedia</span>
                        </div>
                        <div class="fw-bold text-navy">Tas Ransel Eiger Hitam</div>
                        <small class="text-muted"><i class="bi bi-geo-alt"></i> Lab Komputer RPL</small>
                    </div>

                    <div class="p-3 bg-white rounded-4 border">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small text-muted">Verifikasi Klaim Siswa</span>
                            <span class="badge badge-pastel-blue">Selesai</span>
                        </div>
                        <div class="fw-bold text-navy">Tumbler Biru Tupperware</div>
                        <small class="text-muted"><i class="bi bi-check-circle-fill text-success"></i> Diserahkan ke Pemilik</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dynamic Statistics Counters -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon-wrapper" style="background-color: var(--pastel-green); color: #1e5a32;">
                        <i class="bi bi-box2-heart"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?= number_format($stats['total_ditemukan']) ?></div>
                        <div class="stat-label">Total Barang Ditemukan</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon-wrapper" style="background-color: var(--pastel-pink); color: #7a2339;">
                        <i class="bi bi-exclamation-octagon"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?= number_format($stats['total_hilang']) ?></div>
                        <div class="stat-label">Total Barang Hilang</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="stat-card">
                    <div class="stat-icon-wrapper" style="background-color: var(--pastel-blue); color: #1d4b79;">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?= number_format($stats['total_dikembalikan']) ?></div>
                        <div class="stat-label">Total Barang Dikembalikan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Latest Found Items Section -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
            <div>
                <span class="badge badge-pastel-blue mb-2">Update Terkini</span>
                <h3 class="fw-bold text-navy mb-1">Barang Ditemukan Terbaru</h3>
                <p class="text-muted small mb-0">Segera cek apakah barang berikut adalah milik Anda yang tertinggal.</p>
            </div>
            <a href="<?= url('barang') ?>" class="btn btn-pastel-blue btn-rounded btn-sm">
                Lihat Semua Barang <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <?php if (empty($latestBarang)): ?>
            <div class="text-center py-5">
                <div class="stat-icon-wrapper mx-auto mb-3" style="background-color: var(--pastel-yellow); color: #6a530f; width: 64px; height: 64px;">
                    <i class="bi bi-inbox fs-3"></i>
                </div>
                <h5 class="fw-bold text-navy">Belum Ada Barang Ditemukan</h5>
                <p class="text-muted small">Saat ini belum ada data barang temuan yang dipublikasikan.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($latestBarang as $item): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card card-custom h-100">
                            <div class="item-card-img-wrapper">
                                <img src="<?= uploadUrl($item['foto']) ?>" alt="<?= e($item['nama_barang']) ?>" class="item-card-img" loading="lazy">
                                <div class="item-badge-top">
                                    <?= getStatusBadge($item['status']) ?>
                                </div>
                                <div class="item-category-tag">
                                    <i class="bi bi-tag-fill me-1"></i> <?= e($item['kategori']) ?>
                                </div>
                            </div>
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="card-title fw-bold text-navy mb-2"><?= e($item['nama_barang']) ?></h5>
                                <p class="card-text text-muted small mb-3 flex-grow-1">
                                    <?= e(mb_strimwidth($item['deskripsi'] ?? 'Tidak ada deskripsi detail.', 0, 95, '...')) ?>
                                </p>
                                
                                <div class="small text-muted border-top pt-3 mb-3 d-flex flex-column gap-1">
                                    <div><i class="bi bi-geo-alt-fill text-danger me-1"></i> <strong>Lokasi:</strong> <?= e($item['lokasi_ditemukan']) ?></div>
                                    <div><i class="bi bi-calendar3 text-primary me-1"></i> <strong>Tanggal:</strong> <?= formatTanggalIndo($item['tanggal_ditemukan']) ?></div>
                                </div>

                                <a href="<?= url('barang/detail?id=' . $item['id']) ?>" class="btn btn-pastel-blue btn-rounded w-100 fw-semibold">
                                    <i class="bi bi-eye me-1"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Panduan Cara Klaim Section -->
<section class="py-5" id="panduan-klaim">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge badge-pastel-purple mb-2">Panduan Siswa</span>
            <h3 class="fw-bold text-navy">Bagaimana Cara Mengklaim Barang?</h3>
            <p class="text-muted small mx-auto" style="max-width: 540px;">
                Ikuti 4 langkah mudah berikut untuk mengambil kembali barang Anda yang telah ditemukan di lingkungan sekolah.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h6 class="fw-bold text-navy mb-2">Cari Barang</h6>
                    <p class="text-muted small mb-0">
                        Cek daftar barang ditemukan melalui menu katalog atau fitur pencarian.
                    </p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h6 class="fw-bold text-navy mb-2">Ajukan Klaim</h6>
                    <p class="text-muted small mb-0">
                        Klik tombol <strong>"Ini Barang Saya"</strong> pada halaman detail dan tuliskan ciri spesifik barang.
                    </p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h6 class="fw-bold text-navy mb-2">Verifikasi Admin</h6>
                    <p class="text-muted small mb-0">
                        Admin/Petugas kesiswaan akan mencocokkan bukti dan menyetujui klaim yang valid.
                    </p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h6 class="fw-bold text-navy mb-2">Ambil di Ruang TU</h6>
                    <p class="text-muted small mb-0">
                        Datang ke ruang Tata Usaha/Kesiswaan untuk serah terima barang secara langsung.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
