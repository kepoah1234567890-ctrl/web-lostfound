<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-navy mb-1">Riwayat Klaim Barang Saya</h2>
            <p class="text-muted small mb-0">Status pengajuan klaim barang temuan yang Anda ajukan.</p>
        </div>
        <a href="<?= url('barang') ?>" class="btn btn-navy btn-rounded">
            <i class="bi bi-search me-1"></i> Telusuri Barang Lain
        </a>
    </div>

    <?php if (empty($klaimList)): ?>
        <div class="card card-custom p-5 text-center border-0 my-4">
            <div class="stat-icon-wrapper mx-auto mb-3" style="background-color: var(--pastel-purple); color: #472b69; width: 64px; height: 64px;">
                <i class="bi bi-patch-check fs-3"></i>
            </div>
            <h4 class="fw-bold text-navy mb-2">Belum Ada Riwayat Klaim</h4>
            <p class="text-muted small mx-auto" style="max-width: 480px;">
                Anda belum mengajukan klaim untuk barang temuan manapun. Temukan barang Anda di katalog dan klik "Ini Barang Saya".
            </p>
            <div class="mt-2">
                <a href="<?= url('barang') ?>" class="btn btn-pastel-blue btn-rounded">
                    <i class="bi bi-box2-heart me-1"></i> Lihat Katalog Barang Ditemukan
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($klaimList as $klaim): ?>
                <div class="col-lg-6">
                    <div class="card card-custom p-4 border-0 h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= uploadUrl($klaim['barang_foto']) ?>" alt="" style="width: 56px; height: 56px; object-fit: cover;" class="rounded-3 border">
                                <div>
                                    <h5 class="fw-bold text-navy mb-0"><?= e($klaim['nama_barang']) ?></h5>
                                    <small class="text-muted"><i class="bi bi-tag-fill me-1"></i> <?= e($klaim['barang_kategori']) ?></small>
                                </div>
                            </div>
                            <div>
                                <?= getStatusBadge($klaim['status']) ?>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded-4 mb-3 small">
                            <div class="fw-semibold text-navy mb-1"><i class="bi bi-card-text me-1 text-primary"></i> Ciri-ciri yang Anda Ajukan:</div>
                            <p class="text-muted mb-0"><?= nl2br(e($klaim['ciri_barang'])) ?></p>
                        </div>

                        <?php if (!empty($klaim['catatan_admin'])): ?>
                            <div class="p-3 rounded-4 mb-3 small <?= $klaim['status'] === 'disetujui' ? 'bg-success bg-opacity-10 border border-success border-opacity-25' : 'bg-danger bg-opacity-10 border border-danger border-opacity-25' ?>">
                                <div class="fw-bold <?= $klaim['status'] === 'disetujui' ? 'text-success' : 'text-danger' ?> mb-1">
                                    <i class="bi bi-chat-quote-fill me-1"></i> Catatan Petugas / Admin:
                                </div>
                                <div><?= nl2br(e($klaim['catatan_admin'])) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if ($klaim['status'] === 'disetujui'): ?>
                            <div class="alert alert-success border-0 rounded-4 py-2 px-3 small mb-3">
                                <i class="bi bi-info-circle-fill me-1"></i> <strong>Klaim Disetujui!</strong> Silakan datang ke Ruang TU / Kesiswaan SMK Informatika Sumedang pada jam kerja untuk serah terima barang.
                            </div>
                        <?php endif; ?>

                        <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center small text-muted">
                            <div><i class="bi bi-clock me-1"></i> Diajukan: <?= formatTanggalIndo($klaim['created_at'], true) ?></div>
                            <?php if ($klaim['status'] === 'menunggu'): ?>
                                <form action="<?= url('klaim/delete?id=' . $klaim['id']) ?>" method="POST">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-rounded" data-confirm-delete="Batalkan pengajuan klaim ini?">
                                        Batalkan Klaim
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
