<div class="container-fluid py-4">
    <div class="row g-4">
        <!-- Admin Sidebar -->
        

        <!-- Main Content Area -->
        <div class="col-12 admin-view-content">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h3 class="fw-bold text-navy mb-1">Verifikasi Klaim Barang</h3>
                    <p class="text-muted small mb-0">Tinjau kesesuaian ciri-ciri barang yang diajukan siswa sebelum disetujui.</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="card card-custom p-3 mb-4 border-0">
                <form action="<?= url('admin/klaim') ?>" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-8">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Status Klaim</option>
                            <option value="menunggu" <?= ($filters['status'] ?? '') === 'menunggu' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                            <option value="disetujui" <?= ($filters['status'] ?? '') === 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                            <option value="ditolak" <?= ($filters['status'] ?? '') === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-pastel-blue btn-rounded w-100 fw-semibold">Filter Status</button>
                        <?php if (!empty($filters['status'])): ?>
                            <a href="<?= url('admin/klaim') ?>" class="btn btn-outline-secondary btn-rounded"><i class="bi bi-arrow-counterclockwise"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Claims Table -->
            <div class="card card-custom border-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Barang Temuan</th>
                                <th>Siswa Pengklaim</th>
                                <th>Ciri yang Dilaporkan</th>
                                <th>Status</th>
                                <th class="text-end">Aksi Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($klaimList)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Belum ada data pengajuan klaim.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($klaimList as $k): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= uploadUrl($k['barang_foto']) ?>" alt="" class="rounded-3 border" style="width: 48px; height: 48px; object-fit: cover;">
                                                <div>
                                                    <div class="fw-bold text-navy"><?= e($k['nama_barang']) ?></div>
                                                    <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= e($k['lokasi_ditemukan']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-navy"><?= e($k['pengklaim_nama']) ?></div>
                                            <small class="text-muted">NIS: <?= e($k['pengklaim_nis']) ?> | Kelas: <?= e($k['pengklaim_kelas'] ?? '-') ?></small>
                                            <div class="small text-muted"><i class="bi bi-envelope"></i> <?= e($k['pengklaim_email']) ?></div>
                                        </td>
                                        <td>
                                            <div class="small p-2 bg-light rounded-3 mb-1" style="max-width: 320px;">
                                                <?= nl2br(e($k['ciri_barang'])) ?>
                                            </div>
                                            <?php if (!empty($k['bukti_kepemilikan'])): ?>
                                                <a href="<?= uploadUrl($k['bukti_kepemilikan']) ?>" target="_blank" class="badge badge-pastel-blue text-decoration-none">
                                                    <i class="bi bi-image me-1"></i> Lihat Foto Bukti
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($k['catatan_admin'])): ?>
                                                <div class="small text-muted mt-1">
                                                    <strong>Catatan:</strong> <?= e($k['catatan_admin']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= getStatusBadge($k['status']) ?>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-navy btn-rounded" data-bs-toggle="modal" data-bs-target="#verifyModal<?= $k['id'] ?>">
                                                <i class="bi bi-check2-circle me-1"></i> Verifikasi
                                            </button>
                                            <form action="<?= url('admin/klaim/delete') ?>" method="POST" class="d-inline" data-confirm-delete="Hapus klaim ini? Data yang sudah dihapus tidak dapat dikembalikan.">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger btn-rounded" title="Hapus klaim"><i class="bi bi-trash"></i></button>
                                            </form>

                                            <!-- Modal Verifikasi -->
                                            <div class="modal fade verify-claim-modal" id="verifyModal<?= $k['id'] ?>" tabindex="-1" aria-labelledby="verifyModalLabel<?= $k['id'] ?>" aria-hidden="true">
                                                <div class="modal-dialog text-start">
                                                    <div class="modal-content rounded-4 border-0">
                                                        <div class="modal-header border-bottom px-4 pt-4 pb-3">
                                                            <h5 class="modal-title fw-bold text-navy" id="verifyModalLabel<?= $k['id'] ?>">
                                                                Verifikasi Klaim #<?= $k['id'] ?>
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form action="<?= url('admin/klaim/verifikasi') ?>" method="POST">
                                                            <?= csrfField() ?>
                                                            <input type="hidden" name="id" value="<?= $k['id'] ?>">
                                                            <div class="modal-body px-4 py-3">
                                                                <div class="mb-3 p-3 bg-light rounded-3 small">
                                                                    <div><strong>Barang:</strong> <?= e($k['nama_barang']) ?></div>
                                                                    <div><strong>Pengklaim:</strong> <?= e($k['pengklaim_nama']) ?> (<?= e($k['pengklaim_kelas'] ?? '-') ?>)</div>
                                                                    <div class="mt-2"><strong>Ciri-ciri dari Siswa:</strong></div>
                                                                    <div class="text-muted"><?= nl2br(e($k['ciri_barang'])) ?></div>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Keputusan Verifikasi <span class="text-danger">*</span></label>
                                                                    <select name="status" class="form-select" required>
                                                                        <option value="disetujui" <?= $k['status'] === 'disetujui' ? 'selected' : '' ?>>Disetujui (Klaim Valid)</option>
                                                                        <option value="ditolak" <?= $k['status'] === 'ditolak' ? 'selected' : '' ?>>Ditolak (Ciri Tidak Cocok)</option>
                                                                        <option value="menunggu" <?= $k['status'] === 'menunggu' ? 'selected' : '' ?>>Menunggu Peninjauan</option>
                                                                    </select>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Catatan untuk Siswa</label>
                                                                    <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Contoh: Silakan datang ke ruang kesiswaan membawa kartu pelajar untuk serah terima..."><?= e($k['catatan_admin'] ?? '') ?></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer border-top px-4 py-3">
                                                                <button type="button" class="btn btn-outline-secondary btn-rounded" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-navy btn-rounded fw-semibold">Simpan Keputusan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Verifikasi Klaim: cegah modal ikut animasi/transform dari elemen tabel atau parent. */
#verifyModal\[id\], .verify-claim-modal {
    transition: none !important;
}
.verify-claim-modal .modal-dialog,
.verify-claim-modal.show .modal-dialog {
    transition: none !important;
    transform: none !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.verify-claim-modal').forEach(function (modal) {
        // Modal Bootstrap yang berada di dalam <tr>/<td> dapat ikut terpengaruh
        // layout/transform tabel. Pindahkan ke body sebelum Bootstrap menampilkannya.
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });
});
</script>
