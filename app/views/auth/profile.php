<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            <div class="card card-custom border-0 overflow-hidden">
                <div class="p-4 p-md-5" style="background:linear-gradient(135deg,#eef6ff,#ffffff);">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon-wrapper" style="background:var(--pastel-blue); color:#1d4b79; width:68px;height:68px;">
                                <i class="bi bi-person-circle fs-2"></i>
                            </div>
                            <div>
                                <h2 class="fw-bold text-navy mb-1"><?= e($user['nama']) ?></h2>
                                <div class="text-muted mb-1"><?= e($user['email']) ?></div>
                                <span class="badge bg-light text-dark border">
                                    <?= strtoupper(e($user['role'])) ?><?= !empty($user['kelas']) ? ' • ' . e($user['kelas']) : '' ?>
                                </span>
                            </div>
                        </div>
                        <a href="<?= url('') ?>" class="btn btn-outline-secondary btn-rounded">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <div class="mb-4">
                        <h4 class="fw-bold text-navy mb-1">Data Akun</h4>
                        <p class="text-muted mb-0">Email dan nomor telepon bisa kamu ubah. NIS tidak bisa diubah.</p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger border-0 rounded-3">
                            <i class="bi bi-exclamation-circle me-2"></i><?= e($error) ?>
                        </div>
                    <?php endif; ?>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="small text-muted mb-1">Nama Lengkap</div>
                                <div class="fw-semibold text-navy"><?= e($user['nama']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="small text-muted mb-1">NIS</div>
                                <div class="fw-semibold text-navy"><?= e($user['nis']) ?></div>
                                <span class="badge bg-secondary-subtle text-secondary border mt-1">Tidak dapat diubah</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="small text-muted mb-1">Kelas</div>
                                <div class="fw-semibold text-navy"><?= e($user['kelas'] ?? '-') ?></div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="<?= url('profil') ?>">
                        <?= csrfField() ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Gmail / Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                    <input type="email" id="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required>
                                </div>
                                <div class="form-text">Email ini digunakan untuk login.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="no_telepon" class="form-label fw-semibold">Nomor Telepon / WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" id="no_telepon" name="no_telepon" class="form-control" value="<?= e($user['no_telepon'] ?? '') ?>" placeholder="081234567890" required>
                                </div>
                                <div class="form-text">Nomor ini dapat digunakan Admin untuk menghubungi kamu.</div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button class="btn btn-navy btn-rounded px-4" type="submit">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                            <a href="<?= url('laporan/riwayat') ?>" class="btn btn-outline-secondary btn-rounded">
                                <i class="bi bi-journal-text me-1"></i> Laporan Saya
                            </a>
                            <a href="<?= url('klaim/riwayat') ?>" class="btn btn-outline-secondary btn-rounded">
                                <i class="bi bi-clock-history me-1"></i> Riwayat Klaim
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
