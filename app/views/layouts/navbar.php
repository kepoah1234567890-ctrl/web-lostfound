<?php
$currentRoute = getRequestPath();
$flash = getFlash();
?>
<?php if (isImpersonating()): ?>
    <?php $originalAdmin = impersonationAdmin(); ?>
    <div class="container-fluid bg-warning-subtle border-bottom py-2">
        <div class="container d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="small text-dark"><i class="bi bi-person-badge me-1"></i>Mode user: <?= e(legacyAuth()['nama'] ?? 'User') ?> · Admin <?= e($originalAdmin['nama'] ?? 'Admin') ?> sedang mengakses akun ini</span>
            <form method="POST" action="<?= url('impersonation/stop') ?>">
                <input type="hidden" name="_csrf_token" value="<?= e(csrfToken()) ?>">
                <button type="submit" class="btn btn-sm btn-outline-dark">Kembali ke Admin</button>
            </form>
        </div>
    </div>
<?php endif; ?>
<nav class="navbar navbar-expand-xl navbar-custom">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand" href="<?= url('') ?>">
            <div class="brand-icon">
                <img src="<?= asset('images/ifsu-logo.png') ?>" alt="Logo SMK Informatika Sumedang" class="ifsu-logo">
            </div>
            <div>
                <span class="navbar-brand-name">Lost &amp; Found</span>
                <span class="school-tagline"><?= SCHOOL_NAME ?></span>
            </div>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-2 text-navy"></i>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-1">
                <li class="nav-link-item">
                    <a class="nav-link <?= ($currentRoute === '' || $currentRoute === 'home') ? 'active' : '' ?>" href="<?= url('') ?>">
                        <i class="bi bi-house-door me-1"></i> Beranda
                    </a>
                </li>
                <li class="nav-link-item">
                    <a class="nav-link <?= strpos($currentRoute, 'barang') === 0 && strpos($currentRoute, 'barang-hilang') === false ? 'active' : '' ?>" href="<?= url('barang') ?>">
                        <i class="bi bi-box2-heart me-1"></i> Barang Ditemukan
                    </a>
                </li>
                <li class="nav-link-item">
                    <a class="nav-link <?= ($currentRoute === 'laporan' || $currentRoute === 'barang-hilang') ? 'active' : '' ?>" href="<?= url('laporan') ?>">
                        <i class="bi bi-search me-1"></i> Barang Hilang
                    </a>
                </li>
                <li class="nav-link-item">
                    <a class="nav-link" href="<?= url('') ?>#panduan-klaim">
                        <i class="bi bi-info-circle me-1"></i> Cara Klaim
                    </a>
                </li>
            </ul>

            <!-- Auth Actions -->
            <div class="d-flex align-items-center gap-2">
                <?php if (isLoggedIn()): ?>
                    <!-- Dropdown for Logged-in User -->
                    <div class="dropdown">
                        <button class="btn btn-pastel-blue btn-rounded dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5"></i>
                            <span class="fw-semibold text-truncate" style="max-width: 140px;"><?= e(legacyAuth()['nama']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" style="border-radius: var(--radius-md);">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold text-navy"><?= e(legacyAuth()['nama']) ?></div>
                                <small class="text-muted"><?= e(legacyAuth()['email']) ?></small>
                                <div>
                                    <span class="badge bg-light text-dark border mt-1"><?= strtoupper(e(legacyAuth()['role'])) ?> <?= legacyAuth()['kelas'] ? ' • ' . e(legacyAuth()['kelas']) : '' ?></span>
                                </div>
                            </li>

                            <?php if (isAdmin()): ?>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= url('admin') ?>">
                                        <i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard Admin
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= url('admin/barang') ?>">
                                        <i class="bi bi-boxes me-2 text-success"></i> Kelola Barang
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= url('admin/klaim') ?>">
                                        <i class="bi bi-patch-check me-2 text-warning"></i> Verifikasi Klaim
                                    </a>
                                </li>
                            <li>
                                <a class="dropdown-item py-2" href="<?= url('profil') ?>">
                                    <i class="bi bi-person-gear me-2 text-primary"></i> Profil Saya
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="<?= url('aktivitas/riwayat') ?>">
                                    <i class="bi bi-activity me-2 text-info"></i> Aktivitas Saya
                                </a>
                            </li>

                            <?php else: ?>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= url('profil') ?>">
                                        <i class="bi bi-person-gear me-2 text-primary"></i> Profil Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= url('aktivitas/riwayat') ?>">
                                        <i class="bi bi-activity me-2 text-info"></i> Aktivitas Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= url('laporkan-hilang') ?>">
                                        <i class="bi bi-exclamation-octagon me-2 text-danger"></i> Laporkan Barang Hilang
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= url('laporkan-ditemukan') ?>">
                                        <i class="bi bi-plus-circle me-2 text-success"></i> Laporkan Barang Ditemukan
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= url('laporan/riwayat') ?>">
                                        <i class="bi bi-journal-text me-2 text-info"></i> Laporan Kehilangan Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= url('klaim/riwayat') ?>">
                                        <i class="bi bi-clock-history me-2 text-warning"></i> Riwayat Klaim Saya
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="<?= url('logout') ?>">
                                    <?= csrfField() ?>
                                    <button type="submit" class="dropdown-item py-2 text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Keluar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= url('login') ?>" class="btn btn-pastel-blue btn-rounded">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                    </a>
                    <a href="<?= url('register') ?>" class="btn btn-navy btn-rounded">
                        Daftar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Flash Message Notification -->
<?php if ($flash): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <?php if ($flash['type'] === 'success'): ?>
                    <i class="bi bi-check-circle-fill fs-5"></i>
                <?php elseif ($flash['type'] === 'danger'): ?>
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <?php elseif ($flash['type'] === 'warning'): ?>
                    <i class="bi bi-exclamation-circle-fill fs-5"></i>
                <?php else: ?>
                    <i class="bi bi-info-circle-fill fs-5"></i>
                <?php endif; ?>
                <div><?= e($flash['message']) ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<main>
