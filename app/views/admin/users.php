<div class="container-fluid py-4">
    <div class="row g-4">
        

        <div class="col-12 admin-view-content">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h3 class="fw-bold text-navy mb-1">Kelola User Siswa</h3>
                    <p class="text-muted small mb-0">Admin bebas mengelola data siswa dan menghubungi mereka. Semua kontak otomatis masuk Aktivitas User.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-navy btn-rounded" data-bs-toggle="modal" data-bs-target="#createUserModal"><i class="bi bi-person-plus me-1"></i> Tambah User</button>
                    <a href="<?= url('admin/aktivitas') ?>" class="btn btn-pastel-blue btn-rounded"><i class="bi bi-activity me-1"></i> Lihat Aktivitas</a>
                </div>
            </div>

            <?php $temp = $_SESSION['temporary_password_result'] ?? null; unset($_SESSION['temporary_password_result']); ?>
            <?php if ($temp): ?>
                <div class="alert alert-warning border-0 rounded-4 shadow-sm">
                    <strong><i class="bi bi-key-fill me-1"></i>Password sementara untuk <?= e($temp['nama']) ?>:</strong>
                    <div class="fs-5 fw-bold mt-1 user-select-all"><?= e($temp['password']) ?></div>
                    <small>Sampaikan kepada siswa melalui jalur yang aman. Password ini hanya ditampilkan sekali di halaman ini.</small>
                </div>
            <?php endif; ?>

            <?php $newUser = $_SESSION['new_user_password_result'] ?? null; unset($_SESSION['new_user_password_result']); ?>
            <?php if ($newUser): ?>
                <div class="alert alert-success border-0 rounded-4 shadow-sm">
                    <strong><i class="bi bi-person-check-fill me-1"></i>User baru ditambahkan: <?= e($newUser['nama']) ?></strong>
                    <div class="fs-5 fw-bold mt-1 user-select-all"><?= e($newUser['password']) ?></div>
                    <small>Ini adalah password yang admin tentukan untuk akun siswa. Simpan dengan aman.</small>
                </div>
            <?php endif; ?>

            <?php $updatedPassword = $_SESSION['updated_user_password_result'] ?? null; unset($_SESSION['updated_user_password_result']); ?>
            <?php if ($updatedPassword): ?>
                <div class="alert alert-info border-0 rounded-4 shadow-sm">
                    <strong><i class="bi bi-shield-lock-fill me-1"></i>Password baru untuk <?= e($updatedPassword['nama']) ?>:</strong>
                    <div class="fs-5 fw-bold mt-1 user-select-all"><?= e($updatedPassword['password']) ?></div>
                    <small>Password ini sudah diset dan ditampilkan sekali untuk keperluan verifikasi.</small>
                </div>
            <?php endif; ?>

            <div class="card card-custom p-3 mb-4 border-0">
                <form method="GET" class="row g-2" action="<?= url('admin/users') ?>">
                    <div class="col-md-9">
                        <input name="search" class="form-control" value="<?= e($search) ?>" placeholder="Cari nama, NIS, email, atau nomor telepon...">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn btn-pastel-blue btn-rounded w-100"><i class="bi bi-search me-1"></i>Cari</button>
                        <?php if ($search): ?><a class="btn btn-outline-secondary btn-rounded" href="<?= url('admin/users') ?>"><i class="bi bi-x-lg"></i></a><?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card card-custom border-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-custom mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Siswa</th><th>NIS / Kelas</th><th>Email</th><th>Nomor Telepon</th><th>Terdaftar</th><th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!$users): ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted">Tidak ada user siswa.</td></tr>
                        <?php else: foreach ($users as $u): if (($u['role'] ?? '') !== 'siswa') continue; ?>
                            <tr>
                                <td><div class="fw-bold text-navy"><?= e($u['nama']) ?></div><small class="text-muted">ID #<?= (int)$u['id'] ?></small></td>
                                <td><div class="fw-semibold"><?= e($u['nis']) ?></div><small class="text-muted"><?= e($u['kelas'] ?? '-') ?></small></td>
                                <td><?= e($u['email']) ?></td>
                                <td><?= e($u['no_telepon'] ?? '-') ?></td>
                                <td><?= !empty($u['created_at']) ? formatTanggalIndo($u['created_at'], true) : '-' ?></td>
                                <td class="text-end">
                                    <?php
                                        $userPhone = preg_replace('/\D+/', '', (string)($u['no_telepon'] ?? ''));
                                        $userWa = $userPhone;
                                        if ($userWa !== '' && str_starts_with($userWa, '0')) {
                                            $userWa = '62' . substr($userWa, 1);
                                        }
                                    ?>
                                    <div class="d-flex justify-content-end gap-1">
                                        <form method="POST" action="<?= url('admin/users/impersonate') ?>" onsubmit="return confirm('Masuk sebagai <?= e($u['nama']) ?>? Aktivitas yang dilakukan tetap tercatat atas nama admin.')">
                                            <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
                                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                            <button class="btn btn-sm btn-outline-primary btn-rounded" title="Masuk sebagai user" aria-label="Masuk sebagai <?= e($u['nama']) ?>"><i class="bi bi-person-check"></i></button>
                                        </form>
                                        <?php if ($userPhone !== ''): ?>
                                            <a href="tel:<?= e($userPhone) ?>" class="js-admin-user-contact btn btn-sm btn-outline-primary btn-rounded" title="Telepon" data-target-user-id="<?= (int)$u['id'] ?>" data-channel="telepon"><i class="bi bi-telephone"></i></a>
                                            <?php if ($userWa !== ''): ?>
                                                <a href="https://wa.me/<?= e($userWa) ?>" target="_blank" rel="noopener" class="js-admin-user-contact btn btn-sm btn-outline-success btn-rounded" title="WhatsApp" data-target-user-id="<?= (int)$u['id'] ?>" data-channel="whatsapp"><i class="bi bi-whatsapp"></i></a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if (!empty($u['email'])): ?>
                                            <a href="mailto:<?= e($u['email']) ?>" class="js-admin-user-contact btn btn-sm btn-outline-secondary btn-rounded" title="Email" data-target-user-id="<?= (int)$u['id'] ?>" data-channel="email"><i class="bi bi-envelope"></i></a>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-pastel-blue btn-rounded js-edit-user"
                                            data-bs-toggle="modal" data-bs-target="#editUserModal"
                                            data-id="<?= (int)$u['id'] ?>"
                                            data-nama="<?= e($u['nama']) ?>"
                                            data-nis="<?= e($u['nis']) ?>"
                                            data-email="<?= e($u['email']) ?>"
                                            data-phone="<?= e($u['no_telepon'] ?? '') ?>"
                                            data-kelas="<?= e($u['kelas'] ?? '') ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning btn-rounded js-change-password"
                                            data-bs-toggle="modal" data-bs-target="#setPasswordModal"
                                            data-id="<?= (int)$u['id'] ?>"
                                            data-nama="<?= e($u['nama']) ?>">
                                            <i class="bi bi-key"></i>
                                        </button>
                                        <form method="POST" action="<?= url('admin/users/reset-password') ?>" onsubmit="return confirm('Buat password sementara untuk user ini?')">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                            <button class="btn btn-sm btn-outline-secondary btn-rounded" title="Password sementara"><i class="bi bi-arrow-repeat"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Satu modal saja, berada di luar tabel/TBODY agar tidak berkedip -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="<?= url('admin/users/create') ?>">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-navy" id="createUserModalLabel">Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIS</label>
                            <input type="text" name="nis" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kelas</label>
                            <input type="text" name="kelas" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email / Gmail</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor Telepon / WhatsApp</label>
                            <input type="tel" name="no_telepon" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" minlength="8" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-rounded" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-navy btn-rounded"><i class="bi bi-person-plus me-1"></i>Tambah User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="<?= url('admin/users/update') ?>">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-navy" id="editUserModalLabel">Edit Kontak User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_user_id">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" id="edit_user_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">NIS</label>
                        <input type="text" name="nis" id="edit_user_nis" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <input type="text" name="kelas" id="edit_user_kelas" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email / Gmail</label>
                        <input type="email" name="email" id="edit_user_email" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="tel" name="no_telepon" id="edit_user_phone" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-rounded" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-navy btn-rounded"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="setPasswordModal" tabindex="-1" aria-labelledby="setPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="<?= url('admin/users/password') ?>">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-navy" id="setPasswordModalLabel">Ubah Password User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="change_user_id">
                    <div class="mb-3">
                        <label class="form-label">User</label>
                        <input type="text" id="change_user_name" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-control" minlength="8" required>
                    </div>
                    <div>
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-rounded" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-navy btn-rounded"><i class="bi bi-shield-lock me-1"></i>Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-edit-user').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('edit_user_id').value = button.dataset.id || '';
            document.getElementById('edit_user_nama').value = button.dataset.nama || '';
            document.getElementById('edit_user_nis').value = button.dataset.nis || '';
            document.getElementById('edit_user_email').value = button.dataset.email || '';
            document.getElementById('edit_user_phone').value = button.dataset.phone || '';
            document.getElementById('edit_user_kelas').value = button.dataset.kelas || '';
        });
    });

    document.querySelectorAll('.js-change-password').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('change_user_id').value = button.dataset.id || '';
            document.getElementById('change_user_name').value = button.dataset.nama || '';
        });
    });
});

(function () {
    const endpoint = <?= json_encode(url('aktivitas/contact')) ?>;
    document.addEventListener('click', async function (event) {
        const link = event.target.closest('.js-admin-user-contact');
        if (!link) return;
        event.preventDefault();
        const href = link.href;
        const target = link.getAttribute('target');
        let popup = target === '_blank' ? window.open('about:blank', '_blank') : null;
        const data = new FormData();
        data.append('target_user_id', link.dataset.targetUserId || '0');
        data.append('barang_id', '0');
        data.append('laporan_hilang_id', '0');
        data.append('channel', link.dataset.channel || 'kontak');
        try {
            const response = await fetch(endpoint, {method:'POST', body:data, credentials:'same-origin', cache:'no-store', keepalive:true});
            if (!response.ok) console.error('Aktivitas kontak gagal:', await response.text());
        } catch (e) { console.error('Gagal mencatat aktivitas:', e); }
        if (popup && !popup.closed) popup.location.href = href;
        else window.location.href = href;
    });
})();
</script>
