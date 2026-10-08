<?php
$stats = $stats ?? [
    'total_barang' => 0,
    'barang_tersedia' => 0,
    'barang_hilang' => 0,
    'menunggu_klaim' => 0,
    'sudah_dikembalikan' => 0,
    'total_user' => 0,
];

$recentBarang = $recentBarang ?? [];
$recentLaporan = $recentLaporan ?? [];
$recentKlaim = $recentKlaim ?? [];
$recentAktivitas = $recentAktivitas ?? [];
$laporanStatusCounts = $laporanStatusCounts ?? [
    'menunggu' => 0,
    'diverifikasi' => 0,
    'ditemukan' => 0,
    'selesai' => 0,
];
?>

<div class="admin-page-wrap">
    <div class="row g-4">
        <!-- Admin Sidebar -->
        

        <!-- Dashboard Content -->
        <div class="col-12 admin-view-content">
            <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
                <div>
                    <div class="text-uppercase fw-bold small text-muted mb-1">Administrator</div>
                    <h2 class="fw-bold text-navy mb-1">Dashboard Admin</h2>
                    <p class="text-muted mb-0">Pantau seluruh operasional Lost &amp; Found <?= SCHOOL_NAME ?> dari satu panel.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= url('admin/laporan/print') ?>" target="_blank" class="btn btn-pastel-blue btn-rounded fw-semibold"><i class="bi bi-file-earmark-bar-graph me-1"></i> Rekap Pimpinan</a>
                    <a href="<?= url('admin/barang') ?>" class="btn btn-navy btn-rounded fw-semibold"><i class="bi bi-plus-lg me-1"></i> Kelola Barang</a>
                </div>
            </div>

            <!-- Main KPI -->
            <div class="row g-3 mb-4">
                <?php
                $cards = [
                    ['Total Barang','total_barang','bi-boxes'],
                    ['Barang Tersedia','barang_tersedia','bi-check-circle'],
                    ['Laporan Hilang','barang_hilang','bi-exclamation-triangle'],
                    ['Menunggu Klaim','menunggu_klaim','bi-hourglass-split'],
                    ['Sudah Dikembalikan','sudah_dikembalikan','bi-arrow-return-left'],
                    ['Total Siswa','total_user','bi-people'],
                ];
                foreach ($cards as $i => $card):
                ?>
                <div class="col-6 col-xl-4">
                    <div class="admin-stat-card p-3 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon"><i class="bi <?= $card[2] ?>"></i></div>
                            <div>
                                <div class="text-muted small"><?= e($card[0]) ?></div>
                                <div class="fs-3 fw-bold text-navy"><?= number_format((int)$stats[$card[1]]) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="row g-4 mb-4">
                <!-- Status Overview -->
                <div class="col-xl-7">
                    <div class="card card-custom border-0 h-100">
                        <div class="card-header bg-white border-bottom p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="fw-bold text-navy mb-1">Ikhtisar Laporan Kehilangan</h5>
                                    <small class="text-muted">Status laporan yang sedang ditangani Admin.</small>
                                </div>
                                <span class="badge badge-pastel-blue px-3 py-2"><?= number_format((int)$stats['barang_hilang']) ?> laporan</span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <?php
                            $statusRows = [
                                ['Menunggu','menunggu'],
                                ['Diverifikasi','diverifikasi'],
                                ['Ditemukan','ditemukan'],
                                ['Selesai','selesai'],
                            ];
                            $totalLost = max(1, (int)$stats['barang_hilang']);
                            foreach ($statusRows as $sr):
                                $count = (int)($laporanStatusCounts[$sr[1]] ?? 0);
                            ?>
                            <div class="admin-chart-row">
                                <div class="small fw-semibold text-navy"><?= $sr[0] ?></div>
                                <div class="admin-chart-track"><div class="admin-chart-fill" style="width:<?= min(100, ($count / $totalLost) * 100) ?>%"></div></div>
                                <div class="text-end fw-bold text-navy"><?= $count ?></div>
                            </div>
                            <?php endforeach; ?>
                            <div class="alert alert-light border mt-3 mb-0 small">
                                <i class="bi bi-info-circle me-1"></i>
                                Angka di atas berasal langsung dari database dan mencakup seluruh laporan.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity -->
                <div class="col-xl-5">
                    <div class="card card-custom border-0 h-100">
                        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-navy mb-1">Aktivitas Terbaru</h5>
                                <small class="text-muted">Aktivitas akun user.</small>
                            </div>
                            <a href="<?= url('admin/aktivitas') ?>" class="btn btn-sm btn-pastel-blue btn-rounded">Semua</a>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php if (empty($recentAktivitas)): ?>
                                <div class="p-4 text-center text-muted small">Belum ada aktivitas tercatat.</div>
                            <?php else: foreach ($recentAktivitas as $a): ?>
                                <div class="list-group-item px-3 py-3 border-0 border-bottom">
                                    <div class="d-flex gap-2">
                                        <div class="stat-icon flex-shrink-0" style="width:36px;height:36px;font-size:.95rem;"><i class="bi bi-activity"></i></div>
                                        <div class="min-w-0">
                                            <div class="fw-semibold text-navy small"><?= e($a['user_nama'] ?? 'User') ?></div>
                                            <div class="small text-muted text-truncate"><?= e($a['deskripsi'] ?? $a['aktivitas']) ?></div>
                                            <div class="small text-secondary mt-1"><?= !empty($a['created_at']) ? formatTanggalIndo($a['created_at'], true) : '-' ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lost Reports with photos -->
            <div class="card card-custom border-0 mb-4">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-navy mb-1">Laporan Barang Hilang Terbaru</h5>
                        <small class="text-muted">Foto, pelapor, lokasi, tanggal, dan status untuk pemantauan cepat.</small>
                    </div>
                    <a href="<?= url('admin/laporan') ?>" class="btn btn-sm btn-pastel-blue btn-rounded">Buka Semua Laporan</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0">
                        <thead><tr><th>Foto</th><th>Barang</th><th>Pelapor</th><th>Lokasi</th><th>Tanggal</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php if (empty($recentLaporan)): ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted">Belum ada laporan kehilangan.</td></tr>
                        <?php else: foreach ($recentLaporan as $lap): ?>
                            <tr>
                                <td><img src="<?= e(uploadUrl($lap['foto'] ?? null)) ?>" class="admin-report-thumb" alt="Foto <?= e($lap['nama_barang']) ?>"></td>
                                <td><div class="fw-bold text-navy"><?= e($lap['nama_barang']) ?></div><small class="text-muted"><?= e($lap['kategori'] ?? '-') ?> · <?= e($lap['warna'] ?? '-') ?></small></td>
                                <td><div class="fw-semibold"><?= e($lap['pelapor_nama'] ?? '-') ?></div><small class="text-muted"><?= e($lap['pelapor_kelas'] ?? '-') ?></small></td>
                                <td><?= e($lap['lokasi_terakhir'] ?? '-') ?></td>
                                <td><?= !empty($lap['tanggal_hilang']) ? formatTanggalIndo($lap['tanggal_hilang']) : '-' ?></td>
                                <td><?= getStatusBadge($lap['status'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card card-custom border-0 h-100">
                        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-navy mb-0"><i class="bi bi-box-seam me-2"></i>Barang Temuan Terbaru</h6>
                            <a href="<?= url('admin/barang') ?>" class="small text-decoration-none">Lihat semua</a>
                        </div>
                        <div class="table-responsive"><table class="table table-custom small mb-0"><tbody>
                        <?php foreach ($recentBarang as $rb): ?>
                            <tr><td class="fw-semibold text-navy"><?= e($rb['nama_barang']) ?></td><td><?= e($rb['kategori']) ?></td><td><?= getStatusBadge($rb['status']) ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (!$recentBarang): ?><tr><td class="text-center text-muted py-4">Belum ada barang.</td></tr><?php endif; ?>
                        </tbody></table></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card card-custom border-0 h-100">
                        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-navy mb-0"><i class="bi bi-patch-check me-2"></i>Klaim Terbaru</h6>
                            <a href="<?= url('admin/klaim') ?>" class="small text-decoration-none">Verifikasi</a>
                        </div>
                        <div class="table-responsive"><table class="table table-custom small mb-0"><tbody>
                        <?php foreach ($recentKlaim as $rk): ?>
                            <tr><td class="fw-semibold text-navy"><?= e($rk['nama_barang'] ?? '-') ?></td><td><?= e($rk['pengklaim_nama'] ?? $rk['user_nama'] ?? '-') ?></td><td><?= getStatusBadge($rk['status'] ?? '') ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (!$recentKlaim): ?><tr><td class="text-center text-muted py-4">Belum ada klaim.</td></tr><?php endif; ?>
                        </tbody></table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
