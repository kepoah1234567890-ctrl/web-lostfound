<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url('') ?>">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= url('barang') ?>">Barang Ditemukan</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($item['nama_barang']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Item Photo Column -->
        <div class="col-lg-5">
            <div class="card card-custom p-3 border-0">
                <div class="position-relative rounded-4 overflow-hidden" style="max-height: 380px; background-color: #F1F5F9;">
                    <img src="<?= uploadUrl($item['foto']) ?>" alt="<?= e($item['nama_barang']) ?>" class="w-100 h-100 object-fit-contain" style="max-height: 380px;">
                    <div class="position-absolute top-0 end-0 p-3">
                        <?= getStatusBadge($item['status']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Item Info Column -->
        <div class="col-lg-7">
            <div class="card card-custom p-4 p-md-5 border-0 h-100 d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge badge-pastel-purple"><i class="bi bi-tag-fill me-1"></i> <?= e($item['kategori']) ?></span>
                    <?php if (!empty($item['warna'])): ?>
                        <span class="badge badge-pastel-gray"><i class="bi bi-palette me-1"></i> Warna: <?= e($item['warna']) ?></span>
                    <?php endif; ?>
                </div>

                <h2 class="fw-bold text-navy mb-3"><?= e($item['nama_barang']) ?></h2>

                <div class="mb-4">
                    <h6 class="fw-bold text-navy mb-2">Deskripsi Barang:</h6>
                    <p class="text-muted" style="line-height: 1.7;">
                        <?= nl2br(e($item['deskripsi'] ?: 'Tidak ada deskripsi detail mengenai barang ini.')) ?>
                    </p>
                </div>

                <div class="row g-3 p-3 bg-light rounded-4 mb-4">
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Lokasi Ditemukan:</small>
                        <strong class="text-navy"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= e($item['lokasi_ditemukan']) ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Tanggal Ditemukan:</small>
                        <strong class="text-navy"><i class="bi bi-calendar-event text-primary me-1"></i> <?= formatTanggalIndo($item['tanggal_ditemukan']) ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Pelapor Temuan:</small>
                        <span class="text-navy"><i class="bi bi-person-fill text-secondary me-1"></i> <?= e($item['pelapor_nama'] ?? 'Petugas Sekolah') ?> <?= !empty($item['pelapor_kelas']) ? '(' . e($item['pelapor_kelas']) . ')' : '' ?></span>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Tanggal Dipublikasikan:</small>
                        <span class="text-muted"><i class="bi bi-clock-history me-1"></i> <?= formatTanggalIndo($item['created_at'], true) ?></span>
                    </div>
                </div>

                <?php
                    $adminPhone = preg_replace('/\D+/', '', (string)($adminContact['no_telepon'] ?? ''));
                    if ($adminPhone !== '' && str_starts_with($adminPhone, '0')) {
                        $adminPhone = '62' . substr($adminPhone, 1);
                    }
                ?>

                <div class="card border-0 bg-light rounded-4 p-3 mb-4">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-shield-check text-primary fs-5"></i>
                        <div>
                            <div class="fw-bold text-navy">Ada 2 jalur komunikasi</div>
                            <div class="small text-muted">
                                Penemu dapat menghubungi pemilik yang cocok, dan tetap bisa menghubungi Admin.
                                Admin juga menerima laporan dan menjadi pengawas proses pengembalian.
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <?php if (!empty($adminContact['email'])): ?>
                            <a href="mailto:<?= e($adminContact['email']) ?>?subject=Lost%20%26%20Found%20-%20<?= rawurlencode($item['nama_barang']) ?>" class="btn btn-outline-primary btn-sm btn-rounded">
                                <i class="bi bi-envelope me-1"></i> Hubungi Admin
                            </a>
                        <?php endif; ?>
                        <?php if ($adminPhone !== ''): ?>
                            <a href="https://wa.me/<?= e($adminPhone) ?>" target="_blank" rel="noopener" class="btn btn-success btn-sm btn-rounded">
                                <i class="bi bi-whatsapp me-1"></i> WhatsApp Admin
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($matchingLostReports)): ?>
                    <div class="card border-0 rounded-4 p-3 mb-4" style="background:#f0fdf4;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-search-heart text-success fs-5"></i>
                            <div class="fw-bold text-navy">Kemungkinan Pemilik Barang</div>
                        </div>
                        <p class="small text-muted mb-3">
                            Sistem menemukan laporan kehilangan yang mirip. Penemu dapat menghubungi pemilik, sementara Admin tetap menerima laporan.
                        </p>

                        <?php foreach ($matchingLostReports as $match): ?>
                            <?php
                                $ownerPhone = preg_replace('/\D+/', '', (string)($match['pelapor_telepon'] ?? ''));
                                if ($ownerPhone !== '' && str_starts_with($ownerPhone, '0')) {
                                    $ownerPhone = '62' . substr($ownerPhone, 1);
                                }
                            ?>
                            <div class="bg-white rounded-3 p-3 mb-2 border">
                                <div class="fw-semibold text-navy"><?= e($match['nama_barang']) ?></div>
                                <div class="small text-muted">
                                    Pemilik: <?= e($match['pelapor_nama']) ?>
                                    <?php if (!empty($match['pelapor_kelas'])): ?>
                                        • <?= e($match['pelapor_kelas']) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <?php if ($ownerPhone !== ''): ?>
                                        <a href="https://wa.me/<?= e($ownerPhone) ?>?text=<?= rawurlencode('Halo, saya menemukan barang yang mungkin milik Anda: ' . $item['nama_barang'] . '. Saya sudah melaporkannya di Lost & Found. Kita bisa koordinasi dengan Admin/Ruang Guru.') ?>" target="_blank" rel="noopener" class="btn btn-success btn-sm btn-rounded">
                                            <i class="bi bi-whatsapp me-1"></i> Hubungi Pemilik
                                        </a>
                                        <a href="tel:<?= e($match['pelapor_telepon']) ?>" class="btn btn-outline-primary btn-sm btn-rounded">
                                            <i class="bi bi-telephone me-1"></i> Telepon
                                        </a>
                                    <?php elseif (!empty($match['pelapor_email'])): ?>
                                        <a href="mailto:<?= e($match['pelapor_email']) ?>?subject=<?= rawurlencode('Barang ditemukan - ' . $item['nama_barang']) ?>" class="btn btn-outline-primary btn-sm btn-rounded">
                                            <i class="bi bi-envelope me-1"></i> Hubungi Pemilik
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Claim Action Section -->
                <div class="mt-auto pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <a href="<?= url('barang') ?>" class="btn btn-outline-secondary btn-rounded">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
                    </a>

                    <div class="d-flex gap-2">
                        <?php if (isAdmin()): ?>
                            <a href="<?= url('barang/edit?id=' . $item['id']) ?>" class="btn btn-pastel-yellow btn-rounded">
                                <i class="bi bi-pencil-square me-1"></i> Edit Barang
                            </a>
                        <?php endif; ?>

                        <?php if ($item['status'] === 'tersedia'): ?>
                            <?php if ($alreadyClaimed): ?>
                                <button class="btn btn-pastel-yellow btn-rounded" disabled>
                                    <i class="bi bi-hourglass-split me-1"></i> Klaim Sedang Ditinjau
                                </button>
                            <?php else: ?>
                                <a href="<?= url('klaim?barang_id=' . $item['id']) ?>" class="btn btn-pastel-green btn-rounded fw-bold px-4">
                                    <i class="bi bi-check2-circle me-1"></i> Ini Barang Saya
                                </a>
                            <?php endif; ?>
                        <?php elseif ($item['status'] === 'menunggu_klaim'): ?>
                            <span class="badge badge-pastel-yellow fs-6 py-2 px-3">
                                <i class="bi bi-clock-history me-1"></i> Sedang Dalam Proses Verifikasi Klaim
                            </span>
                        <?php elseif ($item['status'] === 'diklaim'): ?>
                            <span class="badge badge-pastel-purple fs-6 py-2 px-3">
                                <i class="bi bi-patch-check me-1"></i> Klaim Disetujui (Menunggu Pengambilan)
                            </span>
                        <?php elseif ($item['status'] === 'dikembalikan'): ?>
                            <span class="badge badge-pastel-blue fs-6 py-2 px-3">
                                <i class="bi bi-check-all me-1"></i> Sudah Dikembalikan ke Pemilik
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
