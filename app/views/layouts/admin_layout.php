<?php
/**
 * Admin Panel Master Layout
 * Lost & Found SMK Informatika Sumedang
 * Completely isolated from user/homepage layout
 */

$adminUser = legacyAuth() ?? [];
$currentRoute = getRequestPath();

$adminNav = [
    [
        'route' => 'admin',
        'icon'  => 'speedometer2',
        'label' => 'Dashboard',
        'match' => ['admin', 'admin/dashboard']
    ],
    [
        'route' => 'admin/barang',
        'icon'  => 'boxes',
        'label' => 'Barang Ditemukan',
        'match' => ['admin/barang', 'admin/barang/edit']
    ],
    [
        'route' => 'admin/laporan',
        'icon'  => 'journal-text',
        'label' => 'Laporan Hilang',
        'match' => ['admin/laporan']
    ],
    [
        'route' => 'admin/klaim',
        'icon'  => 'patch-check',
        'label' => 'Verifikasi Klaim',
        'match' => ['admin/klaim']
    ],
    [
        'route' => 'admin/pengembalian',
        'icon'  => 'arrow-return-left',
        'label' => 'Pengembalian',
        'match' => ['admin/pengembalian']
    ],
    [
        'route' => 'admin/users',
        'icon'  => 'people',
        'label' => 'Pengguna',
        'match' => ['admin/users']
    ],
    [
        'route' => 'admin/aktivitas',
        'icon'  => 'activity',
        'label' => 'Aktivitas User',
        'match' => ['admin/aktivitas']
    ],
];

$adminCssVersion = file_exists(PUBLIC_PATH . '/assets/css/admin.css')
    ? filemtime(PUBLIC_PATH . '/assets/css/admin.css')
    : time();

$adminJsVersion = file_exists(PUBLIC_PATH . '/assets/js/admin.js')
    ? filemtime(PUBLIC_PATH . '/assets/js/admin.js')
    : time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin Panel - Lost & Found SMK Informatika Sumedang') ?></title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Isolated Admin Stylesheet with Cache Busting -->
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>?v=<?= $adminCssVersion ?>">
</head>
<body class="admin-page">

    <div class="admin-layout">
        <!-- Dedicated Admin Sidebar (Starts from Top 0) -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header">
                <a href="<?= url('admin') ?>" class="admin-sidebar-brand">
                    <img src="<?= asset('images/ifsu-logo.png') ?>" alt="Logo IFSU" class="admin-sidebar-logo">
                    <span class="admin-sidebar-title">
                        <strong>ADMIN PANEL</strong>
                        <small><?= e(SCHOOL_NAME) ?></small>
                    </span>
                </a>
                <button type="button" class="btn-close d-lg-none" id="adminSidebarClose" aria-label="Tutup Menu"></button>
            </div>

            <div class="admin-sidebar-body">
                <div class="admin-sidebar-label">Overview</div>
                <ul class="admin-sidebar-nav">
                    <?php
                    $isDashboardActive = in_array($currentRoute, ['admin', 'admin/dashboard', ''], true);
                    ?>
                    <li>
                        <a href="<?= url('admin') ?>" class="admin-nav-link <?= $isDashboardActive ? 'active' : '' ?>">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </ul>

                <div class="admin-sidebar-label">Manajemen</div>
                <ul class="admin-sidebar-nav">
                    <?php foreach ($adminNav as $item): ?>
                        <?php if ($item['route'] === 'admin') continue; ?>
                        <?php
                        $isActive = in_array($currentRoute, $item['match'], true)
                            || strpos($currentRoute, $item['route']) === 0;
                        ?>
                        <li>
                            <a href="<?= url($item['route']) ?>" class="admin-nav-link <?= $isActive ? 'active' : '' ?>">
                                <i class="bi bi-<?= $item['icon'] ?>"></i>
                                <span><?= e($item['label']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="admin-sidebar-label">Sistem</div>
                <ul class="admin-sidebar-nav">
                    <li>
                        <a href="<?= url('') ?>" class="admin-nav-link" target="_blank">
                            <i class="bi bi-globe2"></i>
                            <span>Lihat Situs</span>
                        </a>
                    </li>
                    <li>
                        <form method="POST" action="<?= url('logout') ?>" class="admin-logout-form">
                            <?= csrfField() ?>
                            <button type="submit" class="admin-nav-link text-danger">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

            <div class="admin-sidebar-footer">
                <div class="admin-avatar-box">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div class="admin-avatar-info">
                    <span class="admin-avatar-name"><?= e($adminUser['nama'] ?? 'Administrator') ?></span>
                    <span class="admin-avatar-role">Administrator</span>
                </div>
            </div>
        </aside>

        <!-- Mobile Sidebar Backdrop Overlay -->
        <div class="admin-sidebar-backdrop" id="adminSidebarBackdrop"></div>

        <!-- Main Wrapper -->
        <div class="admin-main">
            <!-- Dedicated Admin Topbar -->
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <button type="button" class="admin-mobile-toggle" id="adminMobileToggle" aria-label="Buka Menu">
                        <i class="bi bi-list"></i>
                    </button>
                    <h1 class="admin-topbar-title">
                        <span>Lost &amp; Found</span>
                        <span class="admin-status-pill"><i class="bi bi-check-circle-fill me-1"></i>Sistem Online</span>
                    </h1>
                </div>

                <div class="admin-topbar-actions">
                    <a href="<?= url('') ?>" class="admin-btn-site" target="_blank" title="Buka Halaman Pengguna">
                        <i class="bi bi-globe2"></i>
                        <span>Lihat Situs</span>
                    </a>

                    <div class="admin-topbar-user">
                        <span class="admin-topbar-avatar">
                            <i class="bi bi-person-fill"></i>
                        </span>
                        <span class="admin-topbar-user-name"><?= e($adminUser['nama'] ?? 'Admin') ?></span>
                    </div>

                    <form method="POST" action="<?= url('logout') ?>" class="admin-logout-form">
                        <?= csrfField() ?>
                        <button type="submit" class="admin-btn-logout" title="Keluar dari Panel Admin">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Admin Content Container -->
            <main class="admin-content">
                <?php if ($flash = getFlash()): ?>
                    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($flash['type'] === 'success'): ?>
                                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                            <?php elseif ($flash['type'] === 'danger'): ?>
                                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                            <?php elseif ($flash['type'] === 'warning'): ?>
                                <i class="bi bi-exclamation-circle-fill fs-5 text-warning"></i>
                            <?php else: ?>
                                <i class="bi bi-info-circle-fill fs-5 text-info"></i>
                            <?php endif; ?>
                            <div><?= e($flash['message']) ?></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                <?php endif; ?>

                <?php
                // Render the specific view inside the admin layout
                require APP_PATH . "/views/{$viewPath}.php";
                ?>
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Admin Panel JavaScript -->
    <script src="<?= asset('js/admin.js') ?>?v=<?= $adminJsVersion ?>"></script>
</body>
</html>
