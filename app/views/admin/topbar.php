<?php
$adminUser = legacyAuth() ?? [];
$adminNav = [
    ['admin', 'speedometer2', 'Dashboard'],
    ['admin/barang', 'boxes', 'Barang Ditemukan'],
    ['admin/laporan', 'journal-text', 'Laporan Hilang'],
    ['admin/klaim', 'patch-check', 'Verifikasi Klaim'],
    ['admin/pengembalian', 'arrow-return-left', 'Pengembalian'],
    ['admin/users', 'people', 'Pengguna'],
    ['admin/aktivitas', 'activity', 'Aktivitas User'],
];

$currentAdminRoute = getRequestPath();
?>
<header class="admin-topbar">
    <div class="container-fluid admin-topbar-inner">
        <div class="admin-topbar-left">
            <button class="admin-mobile-toggle d-lg-none" type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#adminSidebarOffcanvas"
                    aria-controls="adminSidebarOffcanvas"
                    aria-label="Buka menu admin">
                <i class="bi bi-list"></i>
            </button>

            <a href="<?= url('admin') ?>" class="admin-topbar-brand">
                <span class="admin-logo-box">
                    <img src="<?= asset('images/ifsu-logo.png') ?>" alt="Logo IFSU" class="ifsu-logo">
                </span>
                <span class="admin-brand-copy">
                    <strong>Lost &amp; Found</strong>
                    <small>Admin Panel · <?= e(SCHOOL_NAME) ?></small>
                </span>
            </a>
        </div>

        <div class="admin-topbar-actions">
            <a href="<?= url('') ?>" class="btn btn-sm btn-outline-secondary rounded-pill admin-site-button">
                <i class="bi bi-globe2 me-1"></i>
                <span>Lihat Situs</span>
            </a>

            <div class="admin-user-chip">
                <span class="admin-user-avatar"><i class="bi bi-shield-lock-fill"></i></span>
                <span class="admin-user-copy">
                    <strong><?= e($adminUser['nama'] ?? 'Administrator') ?></strong>
                    <small>Administrator</small>
                </span>
            </div>

            <form method="POST" action="<?= url('logout') ?>" class="admin-logout-form">
                <?= csrfField() ?>
                <button type="submit" class="btn btn-sm btn-danger rounded-pill">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    <span class="d-none d-md-inline">Keluar</span>
                </button>
            </form>
        </div>
    </div>
</header>

<div class="offcanvas offcanvas-start admin-mobile-offcanvas" tabindex="-1"
     id="adminSidebarOffcanvas" aria-labelledby="adminSidebarOffcanvasLabel">
    <div class="offcanvas-header admin-offcanvas-header">
        <div class="d-flex align-items-center gap-2">
            <span class="admin-logo-box admin-logo-box-sm">
                <img src="<?= asset('images/ifsu-logo.png') ?>" alt="Logo IFSU" class="ifsu-logo">
            </span>
            <div>
                <div id="adminSidebarOffcanvasLabel" class="fw-bold text-navy">ADMIN PANEL</div>
                <small class="text-muted"><?= e(SCHOOL_NAME) ?></small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
    </div>

    <div class="offcanvas-body p-3">
        <div class="admin-section-label">Menu Admin</div>
        <nav class="nav flex-column gap-1">
            <?php foreach ($adminNav as $nav): ?>
                <?php $isActive = ($currentAdminRoute === trim($nav[0], '/')); ?>
                <a class="admin-nav-item <?= $isActive ? 'active' : '' ?>"
                   href="<?= url($nav[0]) ?>" data-bs-dismiss="offcanvas">
                    <i class="bi bi-<?= e($nav[1]) ?>"></i>
                    <span><?= e($nav[2]) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="admin-section-label mt-4">Sistem</div>
        <nav class="nav flex-column gap-1">
            <a class="admin-nav-item" href="<?= url('') ?>">
                <i class="bi bi-globe2"></i><span>Lihat Situs</span>
            </a>
            <form method="POST" action="<?= url('logout') ?>" class="admin-logout-form">
                <?= csrfField() ?>
                <button type="submit" class="admin-nav-item text-danger">
                    <i class="bi bi-box-arrow-right"></i><span>Keluar</span>
                </button>
            </form>
        </nav>
    </div>
</div>
