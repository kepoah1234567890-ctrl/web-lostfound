<div class="container-fluid py-4">
    <div class="row g-4">
        <!-- Admin Sidebar -->
        

        <!-- Main Content Area -->
        <div class="col-12 admin-view-content">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h3 class="fw-bold text-navy mb-1">Kelola Barang Ditemukan</h3>
                    <p class="text-muted small mb-0">Manajemen lengkap data barang temuan di lingkungan sekolah.</p>
                </div>
                <a href="<?= url('barang/tambah') ?>" class="btn btn-navy btn-rounded">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Barang Temuan
                </a>
            </div>

            <!-- Filters -->
            <div class="card card-custom p-3 mb-4 border-0">
                <form action="<?= url('admin/barang') ?>" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama barang, warna, lokasi..." value="<?= e($filters['search'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="kategori" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= ($filters['kategori'] ?? '') === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="tersedia" <?= ($filters['status'] ?? '') === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                            <option value="menunggu_klaim" <?= ($filters['status'] ?? '') === 'menunggu_klaim' ? 'selected' : '' ?>>Menunggu Klaim</option>
                            <option value="diklaim" <?= ($filters['status'] ?? '') === 'diklaim' ? 'selected' : '' ?>>Diklaim</option>
                            <option value="dikembalikan" <?= ($filters['status'] ?? '') === 'dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-pastel-blue btn-rounded w-100 fw-semibold">Filter</button>
                        <?php if (!empty($filters['search']) || !empty($filters['kategori']) || !empty($filters['status'])): ?>
                            <a href="<?= url('admin/barang') ?>" class="btn btn-outline-secondary btn-rounded"><i class="bi bi-arrow-counterclockwise"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Table of Items -->
            <div class="card card-custom border-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Foto & Barang</th>
                                <th>Kategori</th>
                                <th>Lokasi Ditemukan</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Tidak ada data barang yang sesuai filter.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= uploadUrl($item['foto']) ?>" alt="" class="rounded-3 border" style="width: 48px; height: 48px; object-fit: cover;">
                                                <div>
                                                    <div class="fw-bold text-navy"><?= e($item['nama_barang']) ?></div>
                                                    <small class="text-muted">Ditemukan oleh: <?= e($item['pelapor_nama'] ?? 'Admin/Petugas') ?></small>
                                                    <?php if (!empty($item['potential_owners'])): ?>
                                                        <details class="mt-2">
                                                            <summary class="small fw-semibold text-primary" style="cursor:pointer;">
                                                                <i class="bi bi-person-check me-1"></i>
                                                                <?= count($item['potential_owners']) ?> kemungkinan pemilik
                                                            </summary>
                                                            <div class="mt-2 p-2 bg-light rounded-3">
                                                                <?php foreach ($item['potential_owners'] as $owner): ?>
                                                                    <?php
                                                                        $ownerPhone = preg_replace('/\D+/', '', (string)($owner['pelapor_telepon'] ?? ''));
                                                                        $ownerWa = $ownerPhone;
                                                                        if ($ownerWa !== '' && str_starts_with($ownerWa, '0')) {
                                                                            $ownerWa = '62' . substr($ownerWa, 1);
                                                                        }
                                                                    ?>
                                                                    <div class="border-bottom pb-2 mb-2">
                                                                        <div class="fw-bold text-navy small"><?= e($owner['pelapor_nama'] ?? '-') ?></div>
                                                                        <div class="text-muted" style="font-size:.75rem;">NIS: <?= e($owner['pelapor_nis'] ?? '-') ?> · <?= e($owner['pelapor_kelas'] ?? '-') ?></div>
                                                                        <div class="text-muted" style="font-size:.75rem;"><i class="bi bi-telephone me-1"></i><?= e($owner['pelapor_telepon'] ?? '-') ?></div>
                                                                        <div class="d-flex flex-wrap gap-1 mt-2">
                                                                            <?php if ($ownerPhone !== ''): ?>
                                                                                <a href="tel:<?= e($ownerPhone) ?>" class="js-admin-owner-contact btn btn-sm btn-outline-primary rounded-pill" data-target-user-id="<?= (int)$owner['user_id'] ?>" data-laporan-id="<?= (int)$owner['id'] ?>" data-barang-id="<?= (int)$item['id'] ?>" data-channel="telepon"><i class="bi bi-telephone me-1"></i>Telepon</a>
                                                                            <?php endif; ?>
                                                                            <?php if ($ownerWa !== ''): ?>
                                                                                <a href="https://wa.me/<?= e($ownerWa) ?>" target="_blank" rel="noopener" class="js-admin-owner-contact btn btn-sm btn-outline-success rounded-pill" data-target-user-id="<?= (int)$owner['user_id'] ?>" data-laporan-id="<?= (int)$owner['id'] ?>" data-barang-id="<?= (int)$item['id'] ?>" data-channel="whatsapp"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
                                                                            <?php endif; ?>
                                                                            <?php if (!empty($owner['pelapor_email'])): ?>
                                                                                <a href="mailto:<?= e($owner['pelapor_email']) ?>" class="js-admin-owner-contact btn btn-sm btn-outline-secondary rounded-pill" data-target-user-id="<?= (int)$owner['user_id'] ?>" data-laporan-id="<?= (int)$owner['id'] ?>" data-barang-id="<?= (int)$item['id'] ?>" data-channel="email"><i class="bi bi-envelope me-1"></i>Email</a>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                                <div class="small text-muted"><i class="bi bi-info-circle me-1"></i>Calon pemilik berdasarkan laporan yang berpotensi cocok. Admin tetap melakukan verifikasi sebelum serah terima.</div>
                                                            </div>
                                                        </details>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-pastel-purple"><?= e($item['kategori']) ?></span></td>
                                        <td><i class="bi bi-geo-alt-fill text-danger me-1 small"></i> <?= e($item['lokasi_ditemukan']) ?></td>
                                        <td><?= formatTanggalIndo($item['tanggal_ditemukan']) ?></td>
                                        <td>
                                            <!-- Quick Status Modal Trigger / Badge -->
                                            <div class="dropdown">
                                                <button class="btn btn-sm p-0 border-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <?= getStatusBadge($item['status']) ?>
                                                </button>
                                                <ul class="dropdown-menu shadow-sm border-0 small">
                                                    <li><h6 class="dropdown-header">Ubah Status Cepat</h6></li>
                                                    <?php foreach (['tersedia', 'menunggu_klaim', 'diklaim', 'dikembalikan'] as $st): ?>
                                                        <li>
                                                            <form action="<?= url('admin/barang/status') ?>" method="POST">
                                                                <?= csrfField() ?>
                                                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                                <input type="hidden" name="status" value="<?= $st ?>">
                                                                <button type="submit" class="dropdown-item py-1 <?= $item['status'] === $st ? 'active fw-bold' : '' ?>">
                                                                    <?= ucfirst(str_replace('_', ' ', $st)) ?>
                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="<?= url('barang/detail?id=' . $item['id']) ?>" class="btn btn-sm btn-pastel-blue btn-rounded" title="Lihat Detail" target="_blank">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="<?= url('admin/barang/edit?id=' . $item['id']) ?>" class="btn btn-sm btn-pastel-yellow btn-rounded" title="Edit Barang">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="<?= url('admin/barang/delete') ?>" method="POST" class="d-inline">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-pastel-pink btn-rounded" data-confirm-delete="Apakah kamu yakin ingin menghapus barang ini?" title="Hapus Barang">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
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

<script>
(function () {
    const endpoint = <?= json_encode(url('admin/aktivitas/contact')) ?>;
    document.addEventListener('click', async function (event) {
        const link = event.target.closest('.js-admin-owner-contact');
        if (!link) return;
        event.preventDefault();
        const href = link.href;
        const target = link.getAttribute('target');
        let popup = target === '_blank' ? window.open('about:blank', '_blank') : null;
        const data = new FormData();
        data.append('target_user_id', link.dataset.targetUserId || '0');
        data.append('laporan_hilang_id', link.dataset.laporanId || '0');
        data.append('barang_id', link.dataset.barangId || '0');
        data.append('channel', link.dataset.channel || 'kontak');
        try {
            const response = await fetch(endpoint, {method:'POST', body:data, credentials:'same-origin', cache:'no-store', keepalive:true});
            if (!response.ok) console.error('Aktivitas kontak Admin gagal:', await response.text());
        } catch (e) { console.error('Gagal mencatat aktivitas Admin:', e); }
        if (popup && !popup.closed) popup.location.href = href;
        else window.location.href = href;
    });
})();
</script>
