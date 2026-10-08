<?php
/**
 * Front Controller & Router
 * Lost & Found SMK Informatika Sumedang
 */

require_once dirname(__DIR__) . '/legacy-config/config.php';

// Autoload / Include Controllers
require_once APP_PATH . '/controllers/AuthController.php';
require_once APP_PATH . '/controllers/BarangController.php';
require_once APP_PATH . '/controllers/LaporanController.php';
require_once APP_PATH . '/controllers/KlaimController.php';
require_once APP_PATH . '/controllers/AdminController.php';

// Resolve route path
function getRequestPath(): string {
    // 1. Check if 'url' query parameter is present (from .htaccess rewrite)
    if (isset($_GET['url']) && !empty($_GET['url'])) {
        $u = trim($_GET['url'], '/');
        if ($u === 'public' || $u === 'index.php' || $u === 'web_lostfound' || $u === 'home') {
            return '';
        }
        return $u;
    }

    // 2. Check PATH_INFO
    if (!empty($_SERVER['PATH_INFO'])) {
        return trim($_SERVER['PATH_INFO'], '/');
    }

    // 3. Parse REQUEST_URI
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH) ?? '/';
    $path = str_replace('\\', '/', $path);
    $path = trim($path, '/');

    // Strip project name if it starts with it
    $projectName = basename(ROOT_PATH);
    if (!empty($projectName) && strpos($path, $projectName) === 0) {
        $path = substr($path, strlen($projectName));
        $path = trim($path, '/');
    }

    // Strip 'public' if in path
    if (strpos($path, 'public') === 0) {
        $path = substr($path, strlen('public'));
        $path = trim($path, '/');
    }

    if ($path === 'index.php' || $path === 'index.html' || $path === 'home') {
        $path = '';
    }

    return trim($path, '/');
}

$route = getRequestPath();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    abort_unless(
        isValidCsrfToken($_POST['_csrf_token'] ?? null),
        419,
        'Formulir kedaluwarsa atau tidak valid. Muat ulang halaman, lalu coba kembali.'
    );
}

// Initialize Controllers
$authController    = new AuthController();
$barangController  = new BarangController();
$laporanController = new LaporanController();
$klaimController   = new KlaimController();

// Route Dispatching
switch ($route) {
    // Home
    case '':
    case 'home':
    case 'index':
    case 'index.php':
        $barangController->home();
        break;

    // Auth
    case 'login':
        $authController->login();
        break;

    case 'register':
        $authController->register();
        break;

    case 'profil':
    case 'profile':
        $authController->profile();
        break;

    case 'aktivitas/riwayat':
        $authController->aktivitas();
        break;

    case 'admin/users/impersonate':
        $authController->startImpersonation();
        break;

    case 'impersonation/stop':
        $authController->stopImpersonation();
        break;

    case 'logout':
        $authController->logout();
        break;

    // Barang Ditemukan
    case 'barang':
        $barangController->index();
        break;

    case 'barang/detail':
        $id = (int)($_GET['id'] ?? 0);
        $barangController->detail($id);
        break;

    case 'aktivitas/contact':
        $barangController->logContact();
        break;

    case 'barang/tambah':
        $barangController->create();
        break;

    case 'barang/edit':
        $id = (int)($_GET['id'] ?? 0);
        $barangController->edit($id);
        break;

    case 'barang/delete':
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $barangController->delete($id);
        break;

    // Pelaporan
    case 'laporkan-hilang':
        $laporanController->hilang();
        break;

    case 'laporkan-ditemukan':
        $laporanController->ditemukan();
        break;

    case 'laporan':
    case 'barang-hilang':
        $laporanController->index();
        break;

    case 'laporan/riwayat':
        $laporanController->riwayat();
        break;

    case 'laporan/delete':
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $laporanController->delete($id);
        break;

    // Klaim
    case 'klaim':
        $klaimController->index();
        break;

    case 'klaim/riwayat':
        $klaimController->riwayat();
        break;

    case 'klaim/delete':
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $klaimController->delete($id);
        break;

    // Admin Routes
    case 'admin':
    case 'admin/dashboard':
        $adminController = new AdminController();
        $adminController->dashboard();
        break;

    case 'admin/users':
        $adminController = new AdminController();
        $adminController->users();
        break;

    case 'admin/users/create':
        $adminController = new AdminController();
        $adminController->createUser();
        break;

    case 'admin/users/update':
        $adminController = new AdminController();
        $adminController->updateUserContact();
        break;

    case 'admin/users/password':
        $adminController = new AdminController();
        $adminController->updateUserPassword();
        break;

    case 'admin/users/reset-password':
        $adminController = new AdminController();
        $adminController->resetUserPassword();
        break;

    case 'admin/barang':
        $adminController = new AdminController();
        $adminController->barang();
        break;

    case 'admin/aktivitas':
        $adminController = new AdminController();
        $adminController->aktivitas();
        break;

    case 'admin/barang/status':
        $adminController = new AdminController();
        $adminController->updateBarangStatus();
        break;

    case 'admin/barang/edit':
        $adminController = new AdminController();
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $adminController->editBarang($id);
        break;

    case 'admin/barang/delete':
        $adminController = new AdminController();
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $adminController->deleteBarang($id);
        break;

    case 'admin/aktivitas/contact':
        $adminController = new AdminController();
        $adminController->logOwnerContact();
        break;

    case 'admin/laporan':
        $adminController = new AdminController();
        $adminController->laporan();
        break;

    case 'admin/laporan/print':
        $adminController = new AdminController();
        $adminController->laporanPrint();
        break;

    case 'admin/laporan/status':
        $adminController = new AdminController();
        $adminController->updateLaporanStatus();
        break;

    case 'admin/laporan/delete':
        $adminController = new AdminController();
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $adminController->deleteLaporan($id);
        break;

    case 'admin/klaim':
        $adminController = new AdminController();
        $adminController->klaim();
        break;

    case 'admin/klaim/verifikasi':
        $adminController = new AdminController();
        $adminController->verifyKlaim();
        break;

    case 'admin/klaim/delete':
        $adminController = new AdminController();
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $adminController->deleteKlaim($id);
        break;

    case 'admin/pengembalian':
        $adminController = new AdminController();
        $adminController->pengembalian();
        break;

    case 'admin/pengembalian/proses':
        $adminController = new AdminController();
        $adminController->prosesPengembalian();
        break;

    // 404 Not Found
    default:
        http_response_code(404);
        renderView('layouts/404', [
            'title' => 'Halaman Tidak Ditemukan - Lost & Found'
        ]);
        break;
}
