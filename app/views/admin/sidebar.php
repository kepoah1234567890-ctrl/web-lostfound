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
<aside class="admin-sidebar" aria-label="Navigasi admin">
    <div class="admin-brand">
        <a href="<?= url('admin') ?>" class="admin-brand-link">
            <span class="admin-brand-logo"><img src="<?= asset('images/ifsu-logo.png') ?>" alt="Logo IFSU" class="ifsu-logo"></span>
            <span class="admin-brand-text"><strong>Lost &amp; Found</strong><small>ADMIN PANEL</small></span>
        </a>
    </div>
    <nav class="admin-menu">
        <div class="admin-menu-label">MENU UTAMA</div>
        <?php foreach ($adminNav as $nav): ?>
            <?php $isActive = ($currentAdminRoute === trim($nav[0], '/')) || ($nav[0] === 'admin' && $currentAdminRoute === 'admin/dashboard'); ?>
            <a class="admin-nav-item <?= $isActive ? 'active' : '' ?>" href="<?= url($nav[0]) ?>">
                <i class="bi bi-<?= e($nav[1]) ?>"></i><span><?= e($nav[2]) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar-bottom">
        <a class="admin-nav-item" href="<?= url('') ?>"><i class="bi bi-globe2"></i><span>Lihat Situs</span></a>
        <form method="POST" action="<?= url('logout') ?>" class="admin-logout-form">
            <?= csrfField() ?>
            <button type="submit" class="admin-nav-item admin-logout"><i class="bi bi-box-arrow-right"></i><span>Keluar</span></button>
        </form>
    </div>
</aside>
