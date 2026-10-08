<div class="container py-5">
    <!-- Header Page -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-navy mb-1">Katalog Barang Ditemukan</h2>
            <p class="text-muted small mb-0">Daftar barang yang ditemukan di lingkungan <?= SCHOOL_NAME ?>.</p>
        </div>
        <?php if (isLoggedIn()): ?>
            <a href="<?= url('barang/tambah') ?>" class="btn btn-navy btn-rounded">
                <i class="bi bi-plus-lg me-1"></i> Tambah Barang Temuan
            </a>
        <?php endif; ?>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card card-custom p-3 mb-4 border-0">
        <form action="<?= url('barang') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-lg-5 col-md-12">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama barang, warna, lokasi..." value="<?= e($filters['search'] ?? '') ?>">
                </div>
            </div>

            <div class="col-lg-3 col-md-5">
                <select name="kategori" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat) ?>" <?= ($filters['kategori'] ?? '') === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-lg-2 col-md-4">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="tersedia" <?= ($filters['status'] ?? '') === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                    <option value="menunggu_klaim" <?= ($filters['status'] ?? '') === 'menunggu_klaim' ? 'selected' : '' ?>>Menunggu Klaim</option>
                    <option value="diklaim" <?= ($filters['status'] ?? '') === 'diklaim' ? 'selected' : '' ?>>Diklaim</option>
                    <option value="dikembalikan" <?= ($filters['status'] ?? '') === 'dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-pastel-blue btn-rounded w-100 fw-semibold">
                    Terapkan
                </button>
                <?php if (!empty($filters['search']) || !empty($filters['kategori']) || !empty($filters['status'])): ?>
                    <a href="<?= url('barang') ?>" class="btn btn-outline-secondary btn-rounded" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Items Grid -->
    <?php if (empty($items)): ?>
        <div class="card card-custom p-5 text-center border-0 my-4">
            <div class="stat-icon-wrapper mx-auto mb-3" style="background-color: var(--pastel-yellow); color: #6a530f; width: 64px; height: 64px;">
                <i class="bi bi-search fs-3"></i>
            </div>
            <h4 class="fw-bold text-navy mb-2">Barang Tidak Ditemukan</h4>
            <p class="text-muted small mx-auto" style="max-width: 480px;">
                Tidak ada barang ditemukan yang cocok dengan kriteria pencarian Anda. Silakan coba kata kunci lain atau reset filter pencarian.
            </p>
            <div class="mt-2">
                <a href="<?= url('barang') ?>" class="btn btn-pastel-blue btn-rounded">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Tampilkan Semua Barang
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($items as $item): ?>
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
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title fw-bold text-navy mb-0"><?= e($item['nama_barang']) ?></h5>
                            </div>

                            <p class="card-text text-muted small mb-3 flex-grow-1">
                                <?= e(mb_strimwidth($item['deskripsi'] ?? 'Tidak ada deskripsi detail.', 0, 95, '...')) ?>
                            </p>

                            <div class="small text-muted border-top pt-3 mb-3 d-flex flex-column gap-1">
                                <div><i class="bi bi-geo-alt-fill text-danger me-1"></i> <strong>Lokasi:</strong> <?= e($item['lokasi_ditemukan']) ?></div>
                                <div><i class="bi bi-calendar3 text-primary me-1"></i> <strong>Tanggal:</strong> <?= formatTanggalIndo($item['tanggal_ditemukan']) ?></div>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="<?= url('barang/detail?id=' . $item['id']) ?>" class="btn btn-pastel-blue btn-rounded flex-grow-1 fw-semibold">
                                    <i class="bi bi-eye me-1"></i> Lihat Detail
                                </a>

                                <?php if ($item['status'] === 'tersedia'): ?>
                                    <a href="<?= url('klaim?barang_id=' . $item['id']) ?>" class="btn btn-pastel-green btn-rounded fw-semibold" title="Ini Barang Saya">
                                        <i class="bi bi-check2-circle"></i> Klaim
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['totalPages'] > 1): ?>
            <nav class="mt-5" aria-label="Navigasi Halaman">
                <ul class="pagination justify-content-center gap-1">
                    <?php if ($pagination['page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link rounded-circle" href="<?= url('barang?page=' . ($pagination['page'] - 1) . (!empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '') . (!empty($filters['kategori']) ? '&kategori=' . urlencode($filters['kategori']) : '') . (!empty($filters['status']) ? '&status=' . urlencode($filters['status']) : '')) ?>" aria-label="Sebelumnya">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                        <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                            <a class="page-link rounded-circle" href="<?= url('barang?page=' . $i . (!empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '') . (!empty($filters['kategori']) ? '&kategori=' . urlencode($filters['kategori']) : '') . (!empty($filters['status']) ? '&status=' . urlencode($filters['status']) : '')) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                        <li class="page-item">
                            <a class="page-link rounded-circle" href="<?= url('barang?page=' . ($pagination['page'] + 1) . (!empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '') . (!empty($filters['kategori']) ? '&kategori=' . urlencode($filters['kategori']) : '') . (!empty($filters['status']) ? '&status=' . urlencode($filters['status']) : '')) ?>" aria-label="Selanjutnya">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
