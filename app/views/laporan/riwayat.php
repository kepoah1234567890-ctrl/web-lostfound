<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-navy mb-1">Riwayat Laporan Kehilangan Saya</h2>
            <p class="text-muted small mb-0">Pantau perkembangan status pencarian barang Anda yang dilaporkan hilang.</p>
        </div>
        <a href="<?= url('laporkan-hilang') ?>" class="btn btn-navy btn-rounded">
            <i class="bi bi-plus-lg me-1"></i> Buat Laporan Baru
        </a>
    </div>

    <?php if (empty($laporanList)): ?>
        <div class="card card-custom p-5 text-center border-0 my-4">
            <div class="stat-icon-wrapper mx-auto mb-3" style="background-color: var(--pastel-blue); color: #1d4b79; width: 64px; height: 64px;">
                <i class="bi bi-journal-check fs-3"></i>
            </div>
            <h4 class="fw-bold text-navy mb-2">Belum Ada Laporan Kehilangan</h4>
            <p class="text-muted small mx-auto" style="max-width: 480px;">
                Anda belum pernah mengirimkan laporan barang hilang. Jika kehilangan barang, klik tombol di bawah untuk membuat laporan.
            </p>
            <div class="mt-2">
                <a href="<?= url('laporkan-hilang') ?>" class="btn btn-navy btn-rounded">
                    <i class="bi bi-plus-circle me-1"></i> Laporkan Barang Hilang
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card card-custom border-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Kategori & Warna</th>
                            <th>Tanggal Hilang</th>
                            <th>Lokasi Terakhir</th>
                            <th>Status Laporan</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($laporanList as $lap): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if (!empty($lap['foto'])): ?>
                                            <img src="<?= uploadUrl($lap['foto']) ?>" alt="" class="rounded-3 border" style="width: 48px; height: 48px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="stat-icon-wrapper" style="background: var(--pastel-purple); color: #472b69; width: 48px; height: 48px;">
                                                <i class="bi bi-box"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold text-navy"><?= e($lap['nama_barang']) ?></div>
                                            <small class="text-muted"><?= e(mb_strimwidth($lap['deskripsi'] ?? '', 0, 45, '...')) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-pastel-purple me-1"><?= e($lap['kategori']) ?></span>
                                    <small class="text-muted"><?= e($lap['warna'] ?? '-') ?></small>
                                </td>
                                <td><?= formatTanggalIndo($lap['tanggal_hilang']) ?></td>
                                <td><?= e($lap['lokasi_terakhir'] ?? '-') ?></td>
                                <td>
                                    <?= getStatusBadge($lap['status']) ?>
                                </td>
                                <td class="text-end">
                                    <form action="<?= url('laporan/delete?id=' . $lap['id']) ?>" method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-rounded" data-confirm-delete="Hapus laporan barang hilang ini?">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
