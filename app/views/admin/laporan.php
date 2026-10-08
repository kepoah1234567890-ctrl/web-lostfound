<div class="admin-page-wrap">
    <div class="row g-4">
        

        <div class="col-12 admin-view-content">
            <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
                <div>
                    <div class="text-uppercase fw-bold small text-muted mb-1">Manajemen Laporan</div>
                    <h2 class="fw-bold text-navy mb-1">Laporan Barang Hilang</h2>
                    <p class="text-muted mb-0">Pantau laporan siswa lengkap dengan foto barang dan siapkan rekap resmi untuk pimpinan.</p>
                </div>
                <div class="d-flex gap-2 no-print">
                    <?php
                    $printQuery = http_build_query([
                        'search' => $filters['search'] ?? '',
                        'status' => $filters['status'] ?? '',
                        'date_from' => $filters['date_from'] ?? '',
                        'date_to' => $filters['date_to'] ?? '',
                    ]);
                    ?>
                    <a href="<?= url('admin/laporan/print') . '?' . $printQuery ?>" target="_blank" class="btn btn-navy btn-rounded fw-semibold"><i class="bi bi-printer me-1"></i> Cetak Rekap Pimpinan</a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-3"><div class="admin-stat-card p-3"><div class="text-muted small">Total</div><div class="fs-3 fw-bold text-navy"><?= number_format($reportTotal) ?></div></div></div>
                <div class="col-6 col-xl-3"><div class="admin-stat-card p-3"><div class="text-muted small">Menunggu</div><div class="fs-3 fw-bold text-warning"><?= number_format($statusCounts['menunggu']) ?></div></div></div>
                <div class="col-6 col-xl-3"><div class="admin-stat-card p-3"><div class="text-muted small">Ditemukan</div><div class="fs-3 fw-bold text-success"><?= number_format($statusCounts['ditemukan']) ?></div></div></div>
                <div class="col-6 col-xl-3"><div class="admin-stat-card p-3"><div class="text-muted small">Selesai</div><div class="fs-3 fw-bold text-navy"><?= number_format($statusCounts['selesai']) ?></div></div></div>
            </div>

            <div class="card card-custom p-3 mb-4 border-0 no-print">
                <form action="<?= url('admin/laporan') ?>" method="GET" class="row g-2 align-items-end">
                    <div class="col-xl-4 col-md-6">
                        <label class="form-label">Cari laporan</label>
                        <input name="search" class="form-control" value="<?= e($filters['search'] ?? '') ?>" placeholder="Nama barang, warna, lokasi, deskripsi...">
                    </div>
                    <div class="col-xl-2 col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <?php foreach (['menunggu','diverifikasi','ditemukan','selesai'] as $st): ?>
                                <option value="<?= $st ?>" <?= ($filters['status'] ?? '') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-xl-2 col-md-3">
                        <label class="form-label">Dari tanggal</label>
                        <input type="date" name="date_from" class="form-control" value="<?= e($filters['date_from'] ?? '') ?>">
                    </div>
                    <div class="col-xl-2 col-md-3">
                        <label class="form-label">Sampai tanggal</label>
                        <input type="date" name="date_to" class="form-control" value="<?= e($filters['date_to'] ?? '') ?>">
                    </div>
                    <div class="col-xl-2 col-md-3 d-flex gap-2">
                        <button class="btn btn-pastel-blue btn-rounded w-100"><i class="bi bi-search me-1"></i>Filter</button>
                        <a href="<?= url('admin/laporan') ?>" class="btn btn-outline-secondary btn-rounded" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </form>
            </div>

            <div class="admin-report-sheet overflow-hidden">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <div><h5 class="fw-bold text-navy mb-1">Daftar Laporan</h5><small class="text-muted">Foto barang ditampilkan agar Admin dapat melakukan verifikasi visual dengan cepat.</small></div>
                    <span class="badge badge-pastel-blue px-3 py-2"><?= number_format($reportTotal) ?> data</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0">
                        <thead><tr><th>Foto</th><th>Barang Hilang</th><th>Pelapor</th><th>Lokasi Terakhir</th><th>Tanggal Hilang</th><th>Status</th><th class="text-end no-print">Aksi</th></tr></thead>
                        <tbody>
                        <?php if (empty($laporanList)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted">Tidak ada laporan sesuai filter.</td></tr>
                        <?php else: foreach ($laporanList as $lap): ?>
                            <tr>
                                <td><img src="<?= e(uploadUrl($lap['foto'] ?? null)) ?>" class="admin-report-photo" alt="Foto <?= e($lap['nama_barang']) ?>"></td>
                                <td>
                                    <div class="fw-bold text-navy"><?= e($lap['nama_barang']) ?></div>
                                    <div class="small text-muted"><?= e($lap['kategori'] ?? '-') ?> · <?= e($lap['warna'] ?? '-') ?></div>
                                    <div class="small text-muted mt-1"><?= e(mb_strimwidth($lap['deskripsi'] ?? '', 0, 75, '...')) ?></div>
                                </td>
                                <td><div class="fw-semibold text-navy"><?= e($lap['pelapor_nama'] ?? '-') ?></div><small class="text-muted">NIS <?= e($lap['pelapor_nis'] ?? '-') ?> · <?= e($lap['pelapor_kelas'] ?? '-') ?></small><div class="small text-muted"><?= e($lap['pelapor_telepon'] ?? '-') ?></div></td>
                                <td><?= e($lap['lokasi_terakhir'] ?? '-') ?></td>
                                <td><?= !empty($lap['tanggal_hilang']) ? formatTanggalIndo($lap['tanggal_hilang']) : '-' ?></td>
                                <td><?= getStatusBadge($lap['status'] ?? '') ?></td>
                                <td class="text-end no-print">
                                    <div class="d-flex justify-content-end gap-1">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-pastel-blue btn-rounded dropdown-toggle" data-bs-toggle="dropdown">Kelola</button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            <?php foreach (['menunggu','diverifikasi','ditemukan','selesai'] as $st): ?>
                                            <li><form action="<?= url('admin/laporan/status') ?>" method="POST"><?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$lap['id'] ?>"><input type="hidden" name="status" value="<?= $st ?>"><button class="dropdown-item <?= ($lap['status'] ?? '') === $st ? 'fw-bold' : '' ?>" type="submit"><?= ucfirst($st) ?></button></form></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <form action="<?= url('admin/laporan/delete') ?>" method="POST" class="d-inline" data-confirm-delete="Hapus laporan ini? Data yang sudah dihapus tidak dapat dikembalikan.">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= (int)$lap['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-rounded" title="Hapus laporan"><i class="bi bi-trash"></i></button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="alert alert-warning border-0 rounded-4 mt-4 no-print">
                <i class="bi bi-file-earmark-pdf me-2"></i>
                <strong>Rekap untuk pimpinan:</strong> gunakan tombol <strong>Cetak Rekap Pimpinan</strong>. Browser akan membuka format laporan resmi yang bisa langsung dicetak atau dipilih <strong>Save as PDF</strong>.
            </div>
        </div>
    </div>
</div>
