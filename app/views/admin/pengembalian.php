<div class="container-fluid py-4">
    <div class="row g-4">
        <!-- Admin Sidebar -->
        

        <!-- Main Content Area -->
        <div class="col-12 admin-view-content">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h3 class="fw-bold text-navy mb-1">Serah Terima & Riwayat Pengembalian</h3>
                    <p class="text-muted small mb-0">Catat penyerahan barang yang telah disetujui klaimnya kepada pemilik sah.</p>
                </div>
            </div>

            <!-- Section 1: Klaim Disetujui yang Siap Diserahkan -->
            <div class="card card-custom border-0 mb-4">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="fw-bold text-navy mb-0">
                        <i class="bi bi-box-seam text-warning me-2"></i> Klaim Disetujui yang Belum Diserahterimakan
                    </h6>
                </div>
                <div class="p-3">
                    <?php if (empty($klaimSiap)): ?>
                        <div class="p-3 text-center text-muted small">
                            <i class="bi bi-check2-all text-success fs-4 d-block mb-1"></i>
                            Tidak ada antrean barang disetujui yang menunggu serah terima.
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($klaimSiap as $ks): ?>
                                <div class="col-md-6 col-xl-4">
                                    <div class="border rounded-4 p-3 bg-light h-100 d-flex flex-column">
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                            <img src="<?= uploadUrl($ks['barang_foto']) ?>" alt="" style="width: 50px; height: 50px; object-fit: cover;" class="rounded-3 border">
                                            <div>
                                                <div class="fw-bold text-navy"><?= e($ks['nama_barang']) ?></div>
                                                <span class="badge badge-pastel-purple"><?= e($ks['barang_kategori']) ?></span>
                                            </div>
                                        </div>
                                        <div class="small text-muted mb-3 flex-grow-1">
                                            <div><strong>Pemilik:</strong> <?= e($ks['pengklaim_nama']) ?> (<?= e($ks['pengklaim_kelas'] ?? '-') ?>)</div>
                                            <div><strong>NIS:</strong> <?= e($ks['pengklaim_nis']) ?></div>
                                            <div><strong>Telepon:</strong> <?= e($ks['pengklaim_telepon'] ?? '-') ?></div>
                                            <div><strong>Email:</strong> <?= e($ks['pengklaim_email'] ?? '-') ?></div>
                                            <div class="d-flex flex-wrap gap-1 mt-2">
                                                <?php
                                                    $phone = preg_replace('/\D+/', '', (string)($ks['pengklaim_telepon'] ?? ''));
                                                    $wa = $phone;
                                                    if ($wa !== '' && str_starts_with($wa, '0')) $wa = '62' . substr($wa, 1);
                                                ?>
                                                <?php if ($phone !== ''): ?>
                                                    <a href="tel:<?= e($phone) ?>" class="btn btn-sm btn-outline-primary rounded-pill js-log-admin-contact" data-target-user-id="<?= (int)($ks['user_id'] ?? 0) ?>" data-barang-id="<?= (int)($ks['barang_id'] ?? 0) ?>" data-klaim-id="<?= (int)($ks['id'] ?? 0) ?>" data-channel="telepon"><i class="bi bi-telephone me-1"></i>Telepon</a>
                                                    <a href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success rounded-pill js-log-admin-contact" data-target-user-id="<?= (int)($ks['user_id'] ?? 0) ?>" data-barang-id="<?= (int)($ks['barang_id'] ?? 0) ?>" data-klaim-id="<?= (int)($ks['id'] ?? 0) ?>" data-channel="whatsapp"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
                                                <?php endif; ?>
                                                <?php if (!empty($ks['pengklaim_email'])): ?>
                                                    <a href="mailto:<?= e($ks['pengklaim_email']) ?>" class="btn btn-sm btn-outline-secondary rounded-pill js-log-admin-contact" data-target-user-id="<?= (int)($ks['user_id'] ?? 0) ?>" data-barang-id="<?= (int)($ks['barang_id'] ?? 0) ?>" data-klaim-id="<?= (int)($ks['id'] ?? 0) ?>" data-channel="email"><i class="bi bi-envelope me-1"></i>Email</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-pastel-green btn-rounded w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#modalSerahTerima<?= $ks['id'] ?>">
                                            <i class="bi bi-check-circle me-1"></i> Proses Serah Terima
                                        </button>

                                        <!-- Modal Serah Terima -->
                                        <div class="modal fade" id="modalSerahTerima<?= $ks['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog text-start">
                                                <div class="modal-content rounded-4 border-0">
                                                    <div class="modal-header border-bottom px-4 pt-4 pb-3">
                                                        <h5 class="modal-title fw-bold text-navy">Konfirmasi Serah Terima Barang</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="<?= url('admin/pengembalian/proses') ?>" method="POST">
                                                        <?= csrfField() ?>
                                                        <input type="hidden" name="klaim_id" value="<?= $ks['id'] ?>">
                                                        <div class="modal-body px-4 py-3">
                                                            <p class="small text-muted mb-3">
                                                                Pastikan siswa yang bersangkutan sudah berada di ruang kesiswaan dan identitasnya telah diverifikasi dengan kartu pelajar.
                                                            </p>
                                                            <div class="p-3 bg-light rounded-3 mb-3 small">
                                                                <div><strong>Barang:</strong> <?= e($ks['nama_barang']) ?></div>
                                                                <div><strong>Penerima:</strong> <?= e($ks['pengklaim_nama']) ?> (<?= e($ks['pengklaim_kelas'] ?? '-') ?>)</div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold small">Catatan Serah Terima (Opsional)</label>
                                                                <textarea name="catatan" class="form-control" rows="3" placeholder="Contoh: Barang diserahkan dalam kondisi baik dan lengkap, disaksikan oleh guru piket."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-top px-4 py-3">
                                                            <button type="button" class="btn btn-outline-secondary btn-rounded" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-pastel-green btn-rounded fw-bold">Konfirmasi & Simpan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section 2: Riwayat Pengembalian -->
            <div class="card card-custom border-0 overflow-hidden">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="fw-bold text-navy mb-0">
                        <i class="bi bi-clock-history text-primary me-2"></i> Riwayat Pengembalian Barang
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Penerima (Siswa)</th>
                                <th>No. Telepon</th>
                                <th>Kontak</th>
                                <th>Petugas / Admin</th>
                                <th>Waktu Serah Terima</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($riwayatPengembalian)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">Belum ada riwayat pengembalian barang yang tercatat.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($riwayatPengembalian as $rp): ?>
                                    <?php
                                        $rpPhone = preg_replace('/\D+/', '', (string)($rp['siswa_telepon'] ?? ''));
                                        $rpWa = $rpPhone;
                                        if ($rpWa !== '' && str_starts_with($rpWa, '0')) {
                                            $rpWa = '62' . substr($rpWa, 1);
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= uploadUrl($rp['barang_foto']) ?>" alt="" class="rounded-3 border" style="width: 44px; height: 44px; object-fit: cover;">
                                                <div>
                                                    <div class="fw-bold text-navy"><?= e($rp['nama_barang']) ?></div>
                                                    <span class="badge badge-pastel-blue"><?= e($rp['barang_kategori']) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-navy"><?= e($rp['siswa_nama']) ?></div>
                                            <small class="text-muted">NIS: <?= e($rp['siswa_nis']) ?> | <?= e($rp['siswa_kelas'] ?? '-') ?></small>
                                        </td>
                                        <td>
                                            <span class="small text-dark"><?= e($rp['siswa_telepon'] ?? '-') ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php if ($rpPhone !== ''): ?>
                                                    <a href="tel:<?= e($rpPhone) ?>" class="btn btn-xs btn-outline-primary rounded-pill js-log-admin-contact" data-target-user-id="<?= (int)($rp['klaim_user_id'] ?? 0) ?>" data-barang-id="<?= (int)($rp['barang_id'] ?? 0) ?>" data-klaim-id="<?= (int)($rp['klaim_id'] ?? 0) ?>" data-channel="telepon"><i class="bi bi-telephone"></i></a>
                                                    <a href="https://wa.me/<?= e($rpWa) ?>" target="_blank" rel="noopener" class="btn btn-xs btn-outline-success rounded-pill js-log-admin-contact" data-target-user-id="<?= (int)($rp['klaim_user_id'] ?? 0) ?>" data-barang-id="<?= (int)($rp['barang_id'] ?? 0) ?>" data-klaim-id="<?= (int)($rp['klaim_id'] ?? 0) ?>" data-channel="whatsapp"><i class="bi bi-whatsapp"></i></a>
                                                <?php endif; ?>
                                                <?php if (!empty($rp['siswa_email'])): ?>
                                                    <a href="mailto:<?= e($rp['siswa_email']) ?>" class="btn btn-xs btn-outline-secondary rounded-pill js-log-admin-contact" data-target-user-id="<?= (int)($rp['klaim_user_id'] ?? 0) ?>" data-barang-id="<?= (int)($rp['barang_id'] ?? 0) ?>" data-klaim-id="<?= (int)($rp['klaim_id'] ?? 0) ?>" data-channel="email"><i class="bi bi-envelope"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-pastel-gray"><?= e($rp['admin_nama']) ?></span>
                                        </td>
                                        <td>
                                            <div class="small text-muted">
                                                <i class="bi bi-calendar3 me-1"></i> <?= formatTanggalIndo($rp['tanggal_dikembalikan'], true) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= nl2br(e($rp['catatan'] ?? '-')) ?></small>
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

<!-- Stabilkan popup Serah Terima tanpa mengubah isi/desain/workflow -->
<style>
    /* Bootstrap memberi animasi transform pada .modal-dialog saat .fade -> .show.
       Untuk popup Serah Terima, tampilkan langsung agar tidak gerak/kedip. */
    [id^="modalSerahTerima"].fade,
    [id^="modalSerahTerima"].fade .modal-dialog,
    [id^="modalSerahTerima"].fade .modal-content {
        transition: none !important;
        animation: none !important;
    }

    [id^="modalSerahTerima"].fade .modal-dialog,
    [id^="modalSerahTerima"].fade.show .modal-dialog {
        transform: none !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Modal Bootstrap jangan dibiarkan berada di dalam kartu/kolom.
    // Pindahkan ke <body> supaya posisi popup tidak dipengaruhi layout parent.
    document.querySelectorAll('[id^="modalSerahTerima"]').forEach(function (modal) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });
});
</script>


<script>
(function () {
    const endpoint = <?= json_encode(url('admin/aktivitas/contact')) ?>;

    document.addEventListener('click', function (event) {
        const link = event.target.closest('.js-log-admin-contact');
        if (!link) return;

        const targetUserId = link.dataset.targetUserId || '0';
        if (targetUserId === '0') return;

        const href = link.href;
        const target = link.getAttribute('target');
        let popup = null;

        if (target === '_blank') {
            popup = window.open('about:blank', '_blank');
        }

        event.preventDefault();

        const data = new FormData();
        data.append('target_user_id', targetUserId);
        data.append('barang_id', link.dataset.barangId || '0');
        data.append('klaim_id', link.dataset.klaimId || '0');
        data.append('laporan_hilang_id', link.dataset.laporanId || '0');
        data.append('channel', link.dataset.channel || 'kontak');

        let sent = false;
        try {
            if (navigator.sendBeacon) {
                sent = navigator.sendBeacon(endpoint, data);
            }
        } catch (error) {
            console.error('sendBeacon gagal:', error);
        }

        if (!sent) {
            fetch(endpoint, {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                cache: 'no-store',
                keepalive: true
            }).catch(function (error) {
                console.error('Gagal mencatat aktivitas Admin:', error);
            });
        }

        setTimeout(function () {
            if (popup && !popup.closed) {
                popup.location.href = href;
            } else {
                window.location.href = href;
            }
        }, sent ? 80 : 120);
    });
})();
</script>
