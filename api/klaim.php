<?php
/**
 * REST API Klaim Barang
 * Lost & Found SMK Informatika Sumedang
 */

define('IS_API', true);
require_once dirname(__DIR__) . '/legacy-config/config.php';
require_once APP_PATH . '/models/Klaim.php';
require_once APP_PATH . '/models/Barang.php';

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

$klaimModel = new Klaim();
$barangModel = new Barang();
$pdo = Database::getConnection();
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
            $klaim = $klaimModel->getById($id);
            if ($klaim) {
                if (!$isAdmin && (int)$klaim['user_id'] !== (int)$currentUser['id']) {
                    jsonResponse(false, 'Akses klaim ditolak', null, 403);
                }
                jsonResponse(true, 'Data klaim berhasil diambil', $klaim, 200);
            } else {
                jsonResponse(false, 'Data klaim tidak ditemukan', null, 404);
            }
        } else {
            $filters = [];
            if (!empty($_GET['status']))    $filters['status']    = trim($_GET['status']);
            if (!empty($_GET['barang_id'])) $filters['barang_id'] = (int)$_GET['barang_id'];
            if (!$isAdmin) {
                if (isset($_GET['user_id']) && (int)$_GET['user_id'] !== (int)$currentUser['id']) {
                    jsonResponse(false, 'Akses klaim ditolak', null, 403);
                }
                $filters['user_id'] = (int)$currentUser['id'];
            } elseif (!empty($_GET['user_id'])) {
                $filters['user_id'] = (int)$_GET['user_id'];
            }
            if (!empty($_GET['search']))    $filters['search']    = trim($_GET['search']);

            $items = $klaimModel->getAll($filters);
            jsonResponse(true, 'Daftar klaim berhasil diambil', $items, 200);
        }
        break;

    case 'POST':
        $user_id     = (int)$currentUser['id'];
        $barang_id   = isset($inputData['barang_id']) ? (int)$inputData['barang_id'] : 0;
        $ciri_barang = trim($inputData['ciri_barang'] ?? '');

        if (!$user_id) {
            jsonResponse(false, 'User belum login atau user_id tidak disertakan', null, 401);
        }

        if ($barang_id <= 0 || empty($ciri_barang)) {
            jsonResponse(false, 'Field barang_id dan ciri_barang wajib diisi', null, 400);
        }

        $barang = $barangModel->getById($barang_id);
        if (!$barang) {
            jsonResponse(false, 'Barang yang ingin diklaim tidak ditemukan', null, 404);
        }

        try {
            $pdo->beginTransaction();

            $itemLock = $pdo->prepare('SELECT status FROM barang WHERE id = ? FOR UPDATE');
            $itemLock->execute([$barang_id]);
            $itemStatus = $itemLock->fetchColumn();

            if ($itemStatus === false) {
                $pdo->rollBack();
                jsonResponse(false, 'Barang yang ingin diklaim tidak ditemukan', null, 404);
            }

            if (in_array($itemStatus, ['diklaim', 'dikembalikan'], true)) {
                $pdo->rollBack();
                jsonResponse(false, 'Barang ini sudah diklaim atau dikembalikan kepada pemiliknya', null, 409);
            }

            if ($klaimModel->hasUserClaimed($barang_id, $user_id)) {
                $pdo->rollBack();
                jsonResponse(false, 'Anda sudah pernah mengajukan klaim untuk barang ini', null, 409);
            }

            $bukti = null;
            if (isset($_FILES['bukti_kepemilikan']) && $_FILES['bukti_kepemilikan']['error'] !== UPLOAD_ERR_NO_FILE) {
                $bukti = handleFileUpload($_FILES['bukti_kepemilikan']);
            }

            $newKlaimId = $klaimModel->create([
                'barang_id'         => $barang_id,
                'user_id'           => $user_id,
                'ciri_barang'       => $ciri_barang,
                'bukti_kepemilikan' => $bukti,
                'status'            => 'menunggu'
            ]);

            if ($itemStatus === 'tersedia' && !$barangModel->updateStatus($barang_id, 'menunggu_klaim')) {
                throw new RuntimeException('Status barang gagal diperbarui.');
            }

            $pdo->commit();

            $created = $klaimModel->getById($newKlaimId);
            jsonResponse(true, 'Klaim berhasil diajukan', $created, 201);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonResponse(false, 'Gagal mengajukan klaim: ' . $e->getMessage(), null, 500);
        }
        break;

    case 'PUT':
        $id = (int)($_GET['id'] ?? $inputData['id'] ?? 0);
        if ($id <= 0) {
            jsonResponse(false, 'Parameter ID klaim diperlukan (?id=X)', null, 400);
        }

        $klaim = $klaimModel->getById($id);
        if (!$klaim) {
            jsonResponse(false, 'Klaim dengan ID ' . $id . ' tidak ditemukan', null, 404);
        }

        if (!$isAdmin) {
            jsonResponse(false, 'Verifikasi klaim hanya dapat dilakukan Admin', null, 403);
        }

        $status        = trim($inputData['status'] ?? $klaim['status']);
        $catatan_admin = isset($inputData['catatan_admin']) ? trim($inputData['catatan_admin']) : $klaim['catatan_admin'];

        if (!in_array($status, ['menunggu', 'disetujui', 'ditolak'])) {
            jsonResponse(false, 'Status klaim harus: menunggu, disetujui, atau ditolak', null, 400);
        }

        if ($klaim['status'] === 'disetujui' && $status !== 'disetujui') {
            jsonResponse(false, 'Klaim yang sudah disetujui tidak dapat dibatalkan melalui API', null, 409);
        }

        try {
            $pdo->beginTransaction();

            $itemLock = $pdo->prepare('SELECT status FROM barang WHERE id = ? FOR UPDATE');
            $itemLock->execute([(int)$klaim['barang_id']]);
            $itemStatus = $itemLock->fetchColumn();

            if ($itemStatus === false) {
                $pdo->rollBack();
                jsonResponse(false, 'Barang untuk klaim ini tidak ditemukan', null, 404);
            }

            $claimLock = $pdo->prepare('SELECT id FROM klaim WHERE id = ? FOR UPDATE');
            $claimLock->execute([$id]);

            if (!$claimLock->fetchColumn()) {
                $pdo->rollBack();
                jsonResponse(false, 'Klaim tidak ditemukan', null, 404);
            }

            $klaim = $klaimModel->getById($id);

            if (!$klaim) {
                $pdo->rollBack();
                jsonResponse(false, 'Klaim tidak ditemukan', null, 404);
            }

            if ($klaim['status'] === 'disetujui' && $status !== 'disetujui') {
                $pdo->rollBack();
                jsonResponse(false, 'Klaim yang sudah disetujui tidak dapat dibatalkan melalui API', null, 409);
            }

            if (
                $status === 'disetujui' &&
                $klaim['status'] !== 'disetujui' &&
                in_array($itemStatus, ['diklaim', 'dikembalikan'], true)
            ) {
                $pdo->rollBack();
                jsonResponse(false, 'Barang ini sudah memiliki klaim yang disetujui', null, 409);
            }

            $approvedClaim = $pdo->prepare(
                "SELECT id FROM klaim WHERE barang_id = ? AND status = 'disetujui' AND id <> ? LIMIT 1"
            );
            $approvedClaim->execute([(int)$klaim['barang_id'], $id]);

            if ($status === 'disetujui' && $approvedClaim->fetchColumn()) {
                $pdo->rollBack();
                jsonResponse(false, 'Barang ini sudah memiliki klaim yang disetujui', null, 409);
            }

            if (!$klaimModel->updateStatus($id, $status, $catatan_admin)) {
                throw new RuntimeException('Status klaim gagal diperbarui.');
            }

            if ($status === 'disetujui') {
                $rejectOtherClaims = $pdo->prepare(
                    "UPDATE klaim SET status = 'ditolak', catatan_admin = 'Klaim lain telah disetujui untuk barang ini.' WHERE barang_id = ? AND id <> ? AND status = 'menunggu'"
                );
                $rejectOtherClaims->execute([(int)$klaim['barang_id'], $id]);

                if (!$barangModel->updateStatus($klaim['barang_id'], 'diklaim')) {
                    throw new RuntimeException('Status barang gagal diperbarui.');
                }
            } elseif (
                $status === 'menunggu' &&
                $klaim['status'] === 'ditolak' &&
                $itemStatus === 'tersedia'
            ) {
                if (!$barangModel->updateStatus($klaim['barang_id'], 'menunggu_klaim')) {
                    throw new RuntimeException('Status barang gagal diperbarui.');
                }
            } elseif ($status === 'ditolak' && $itemStatus === 'menunggu_klaim') {
                $activeClaims = $pdo->prepare(
                    "SELECT COUNT(*) FROM klaim WHERE barang_id = ? AND status IN ('menunggu', 'disetujui')"
                );
                $activeClaims->execute([(int)$klaim['barang_id']]);

                if ((int)$activeClaims->fetchColumn() === 0) {
                    if (!$barangModel->updateStatus($klaim['barang_id'], 'tersedia')) {
                        throw new RuntimeException('Status barang gagal dipulihkan.');
                    }
                }
            }

            $pdo->commit();

            $updated = $klaimModel->getById($id);
            jsonResponse(true, 'Status klaim berhasil diperbarui', $updated, 200);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonResponse(false, 'Gagal memperbarui status klaim: ' . $e->getMessage(), null, 500);
        }
        break;

    case 'DELETE':
        $id = (int)($_GET['id'] ?? $inputData['id'] ?? 0);
        if ($id <= 0) {
            jsonResponse(false, 'Parameter ID klaim diperlukan (?id=X)', null, 400);
        }

        $klaim = $klaimModel->getById($id);
        if (!$klaim) {
            jsonResponse(false, 'Klaim tidak ditemukan', null, 404);
        }

        try {
            $pdo->beginTransaction();

            $itemLock = $pdo->prepare('SELECT status FROM barang WHERE id = ? FOR UPDATE');
            $itemLock->execute([(int)$klaim['barang_id']]);
            $itemStatus = $itemLock->fetchColumn();

            if ($itemStatus === false) {
                $pdo->rollBack();
                jsonResponse(false, 'Barang untuk klaim ini tidak ditemukan', null, 404);
            }

            $claimLock = $pdo->prepare('SELECT id FROM klaim WHERE id = ? FOR UPDATE');
            $claimLock->execute([$id]);

            if (!$claimLock->fetchColumn()) {
                $pdo->rollBack();
                jsonResponse(false, 'Klaim tidak ditemukan', null, 404);
            }

            $klaim = $klaimModel->getById($id);

            if (!$klaim) {
                $pdo->rollBack();
                jsonResponse(false, 'Klaim tidak ditemukan', null, 404);
            }

            if (!$isAdmin && (int)$klaim['user_id'] !== (int)$currentUser['id']) {
                $pdo->rollBack();
                jsonResponse(false, 'Akses klaim ditolak', null, 403);
            }

            if ($klaim['status'] === 'disetujui') {
                $pdo->rollBack();
                jsonResponse(false, 'Klaim yang sudah disetujui tidak dapat dihapus', null, 409);
            }

            if (!$klaimModel->delete($id)) {
                throw new RuntimeException('Klaim gagal dihapus.');
            }

            if ($itemStatus === 'menunggu_klaim') {
                $activeClaims = $pdo->prepare(
                    "SELECT COUNT(*) FROM klaim WHERE barang_id = ? AND status IN ('menunggu', 'disetujui')"
                );
                $activeClaims->execute([(int)$klaim['barang_id']]);

                if ((int)$activeClaims->fetchColumn() === 0) {
                    if (!$barangModel->updateStatus($klaim['barang_id'], 'tersedia')) {
                        throw new RuntimeException('Status barang gagal dipulihkan.');
                    }
                }
            }

            $pdo->commit();
            jsonResponse(true, 'Klaim berhasil dihapus', null, 200);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonResponse(false, 'Gagal menghapus klaim: ' . $e->getMessage(), null, 500);
        }
        break;

    default:
        jsonResponse(false, 'Metode HTTP tidak didukung', null, 405);
        break;
}
