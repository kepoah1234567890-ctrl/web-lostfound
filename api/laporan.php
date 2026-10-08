<?php
/**
 * REST API Laporan Hilang
 * Lost & Found SMK Informatika Sumedang
 */

define('IS_API', true);
require_once dirname(__DIR__) . '/legacy-config/config.php';
require_once APP_PATH . '/models/LaporanHilang.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$currentUser = apiRequireLogin();
$isAdmin = ($currentUser['role'] ?? '') === 'admin';

$laporanModel = new LaporanHilang();
$method = $_SERVER['REQUEST_METHOD'];

function jsonResponse(bool $success, string $message, $data = null, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$rawInput = file_get_contents('php://input');
$inputData = json_decode($rawInput, true) ?: $_POST;

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $laporan = $laporanModel->getById($id);
            if ($laporan) {
                if (!$isAdmin && (int)$laporan['user_id'] !== (int)$currentUser['id']) {
                    jsonResponse(false, 'Akses laporan ditolak', null, 403);
                }
                jsonResponse(true, 'Data laporan hilang berhasil diambil', $laporan, 200);
            } else {
                jsonResponse(false, 'Laporan hilang tidak ditemukan', null, 404);
            }
        } else {
            $filters = [];
            if (!empty($_GET['search']))   $filters['search']   = trim($_GET['search']);
            if (!empty($_GET['kategori'])) $filters['kategori'] = trim($_GET['kategori']);
            if (!empty($_GET['status']))   $filters['status']   = trim($_GET['status']);
            if (!$isAdmin) {
                if (isset($_GET['user_id']) && (int)$_GET['user_id'] !== (int)$currentUser['id']) {
                    jsonResponse(false, 'Akses laporan ditolak', null, 403);
                }
                $filters['user_id'] = (int)$currentUser['id'];
            } elseif (!empty($_GET['user_id'])) {
                $filters['user_id'] = (int)$_GET['user_id'];
            }

            $items = $laporanModel->getAll($filters);
            jsonResponse(true, 'Daftar laporan hilang berhasil diambil', $items, 200);
        }
        break;

    case 'POST':
        $user_id         = (int)$currentUser['id'];
        $nama_barang     = trim($inputData['nama_barang'] ?? '');
        $kategori        = trim($inputData['kategori'] ?? '');
        $warna           = trim($inputData['warna'] ?? '');
        $deskripsi       = trim($inputData['deskripsi'] ?? '');
        $lokasi_terakhir = trim($inputData['lokasi_terakhir'] ?? '');
        $tanggal_hilang  = trim($inputData['tanggal_hilang'] ?? '');
        $status          = 'menunggu';

        if (!$user_id) {
            jsonResponse(false, 'User ID pelapor diperlukan (harus login atau sertakan user_id)', null, 401);
        }

        if (empty($nama_barang) || empty($kategori) || empty($tanggal_hilang)) {
            jsonResponse(false, 'Field nama_barang, kategori, dan tanggal_hilang wajib diisi', null, 400);
        }

        try {
            $foto = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $foto = handleFileUpload($_FILES['foto']);
            }

            $newId = $laporanModel->create([
                'user_id'         => $user_id,
                'nama_barang'     => $nama_barang,
                'kategori'        => $kategori,
                'warna'           => $warna,
                'deskripsi'       => $deskripsi,
                'lokasi_terakhir' => $lokasi_terakhir,
                'tanggal_hilang'  => $tanggal_hilang,
                'foto'            => $foto,
                'status'          => $status
            ]);

            $created = $laporanModel->getById($newId);
            jsonResponse(true, 'Laporan kehilangan berhasil dibuat', $created, 201);
        } catch (Exception $e) {
            jsonResponse(false, 'Gagal membuat laporan: ' . $e->getMessage(), null, 500);
        }
        break;

    case 'PUT':
        $id = (int)($_GET['id'] ?? $inputData['id'] ?? 0);
        if ($id <= 0) {
            jsonResponse(false, 'Parameter ID laporan diperlukan (?id=X)', null, 400);
        }

        $existing = $laporanModel->getById($id);
        if (!$existing) {
            jsonResponse(false, 'Laporan dengan ID ' . $id . ' tidak ditemukan', null, 404);
        }

        if (!$isAdmin && (int)$existing['user_id'] !== (int)$currentUser['id']) {
            jsonResponse(false, 'Akses laporan ditolak', null, 403);
        }

        try {
            $dataToUpdate = [
                'nama_barang'     => $inputData['nama_barang'] ?? $existing['nama_barang'],
                'kategori'        => $inputData['kategori'] ?? $existing['kategori'],
                'warna'           => $inputData['warna'] ?? $existing['warna'],
                'deskripsi'       => $inputData['deskripsi'] ?? $existing['deskripsi'],
                'lokasi_terakhir' => $inputData['lokasi_terakhir'] ?? $existing['lokasi_terakhir'],
                'tanggal_hilang'  => $inputData['tanggal_hilang'] ?? $existing['tanggal_hilang'],
            ];

            if ($isAdmin) {
                $dataToUpdate['status'] = $inputData['status'] ?? $existing['status'];
            }

            if (isset($inputData['foto'])) {
                $dataToUpdate['foto'] = $inputData['foto'];
            }

            $laporanModel->update($id, $dataToUpdate);
            $updated = $laporanModel->getById($id);

            jsonResponse(true, 'Laporan berhasil diperbarui', $updated, 200);
        } catch (Exception $e) {
            jsonResponse(false, 'Gagal memperbarui laporan: ' . $e->getMessage(), null, 500);
        }
        break;

    case 'DELETE':
        $id = (int)($_GET['id'] ?? $inputData['id'] ?? 0);
        if ($id <= 0) {
            jsonResponse(false, 'Parameter ID laporan diperlukan (?id=X)', null, 400);
        }

        $existing = $laporanModel->getById($id);
        if (!$existing) {
            jsonResponse(false, 'Laporan dengan ID ' . $id . ' tidak ditemukan', null, 404);
        }

        if (!$isAdmin && (int)$existing['user_id'] !== (int)$currentUser['id']) {
            jsonResponse(false, 'Akses laporan ditolak', null, 403);
        }

        try {
            $laporanModel->delete($id);
            jsonResponse(true, 'Laporan berhasil dihapus', null, 200);
        } catch (Exception $e) {
            jsonResponse(false, 'Gagal menghapus laporan: ' . $e->getMessage(), null, 500);
        }
        break;

    default:
        jsonResponse(false, 'Metode HTTP tidak didukung', null, 405);
        break;
}
