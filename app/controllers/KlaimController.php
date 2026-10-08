<?php
/**
 * KlaimController
 * Lost & Found SMK Informatika Sumedang
 */

require_once APP_PATH . '/models/Klaim.php';
require_once APP_PATH . '/models/Barang.php';
require_once APP_PATH . '/models/Aktivitas.php';

class KlaimController {
    private Klaim $klaimModel;
    private Barang $barangModel;
    private Aktivitas $aktivitasModel;

    public function __construct() {
        $this->klaimModel = new Klaim();
        $this->barangModel = new Barang();
        $this->aktivitasModel = new Aktivitas();
    }

    /**
     * Form & Submit Klaim Barang
     */
    public function index(): void {
        requireLogin();

        $barangId = (int)($_GET['barang_id'] ?? $_POST['barang_id'] ?? 0);
        $barang = $this->barangModel->getById($barangId);

        if (!$barang) {
            setFlash('danger', 'Barang yang ingin diklaim tidak ditemukan.');
            legacyRedirect('Location: ' . url('barang'));
            exit;
        }

        // Cek jika barang sudah diklaim atau dikembalikan
        if (in_array($barang['status'], ['diklaim', 'dikembalikan'], true)) {
            setFlash('warning', 'Barang ini sudah diklaim atau dikembalikan kepada pemilik resminya.');
            legacyRedirect('Location: ' . url('barang/detail?id=' . $barangId));
            exit;
        }

        // Cek jika user sudah pernah mengajukan klaim untuk barang ini
        if ($this->klaimModel->hasUserClaimed($barangId, (int)legacyAuth()['id'])) {
            setFlash('info', 'Anda sudah pernah mengajukan klaim untuk barang ini. Silakan pantau status di Riwayat Klaim.');
            legacyRedirect('Location: ' . url('klaim/riwayat'));
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ciri_barang = trim($_POST['ciri_barang'] ?? '');

            if (empty($ciri_barang)) {
                $error = 'Jelaskan ciri-ciri spesifik barang Anda secara detail sebagai bukti kecocokan.';
            } else {
                try {
                    $userId = (int)legacyAuth()['id'];
                    $pdo = Database::getConnection();
                    $pdo->beginTransaction();

                    $itemLock = $pdo->prepare('SELECT status FROM barang WHERE id = ? FOR UPDATE');
                    $itemLock->execute([$barangId]);
                    $itemStatus = $itemLock->fetchColumn();

                    if ($itemStatus === false) {
                        $pdo->rollBack();
                        setFlash('danger', 'Barang yang ingin diklaim tidak ditemukan.');
                        legacyRedirect('Location: ' . url('barang'));
                        exit;
                    }

                    if (in_array($itemStatus, ['diklaim', 'dikembalikan'], true)) {
                        $pdo->rollBack();
                        setFlash('warning', 'Barang ini sudah diklaim atau dikembalikan.');
                        legacyRedirect('Location: ' . url('barang/detail?id=' . $barangId));
                        exit;
                    }

                    if ($this->klaimModel->hasUserClaimed($barangId, $userId)) {
                        $pdo->rollBack();
                        setFlash('info', 'Anda sudah pernah mengajukan klaim untuk barang ini.');
                        legacyRedirect('Location: ' . url('klaim/riwayat'));
                        exit;
                    }

                    $buktiName = null;
                    if (isset($_FILES['bukti_kepemilikan']) && $_FILES['bukti_kepemilikan']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $buktiName = handleFileUpload($_FILES['bukti_kepemilikan']);
                    }

                    $klaimId = $this->klaimModel->create([
                        'barang_id'         => $barangId,
                        'user_id'           => $userId,
                        'ciri_barang'       => $ciri_barang,
                        'bukti_kepemilikan' => $buktiName,
                        'status'            => 'menunggu'
                    ]);

                    // Update status barang menjadi 'menunggu_klaim' jika sebelumnya 'tersedia'
                    if ($itemStatus === 'tersedia' && !$this->barangModel->updateStatus($barangId, 'menunggu_klaim')) {
                        throw new RuntimeException('Status barang gagal diperbarui.');
                    }

                    $pdo->commit();

                    $this->aktivitasModel->recordSafe([
                        'user_id' => $userId,
                        'barang_id' => $barangId,
                        'klaim_id' => $klaimId,
                        'aktivitas' => 'mengajukan_klaim',
                        'deskripsi' => 'Mengajukan klaim untuk barang ' . $barang['nama_barang'] . '.',
                        'metadata' => ['channel' => 'web'],
                    ]);

                    setFlash('success', 'Pengajuan klaim berhasil dikirim! Silakan tunggu verifikasi dari Admin/Petugas.');
                    legacyRedirect('Location: ' . url('klaim/riwayat'));
                    exit;
                } catch (Exception $e) {
                    if (isset($pdo) && $pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $error = $e->getMessage();
                }
            }
        }

        renderView('klaim/index', [
            'title'  => 'Ajukan Klaim Barang - ' . htmlspecialchars($barang['nama_barang']),
            'barang' => $barang,
            'error'  => $error,
            'old'    => $_POST
        ]);
    }

    /**
     * Riwayat Klaim Milik Siswa
     */
    public function riwayat(): void {
        requireLogin();

        $userId = (int)legacyAuth()['id'];
        $klaimList = $this->klaimModel->getByUserId($userId);

        renderView('klaim/riwayat', [
            'title'     => 'Riwayat Klaim Barang Saya',
            'klaimList' => $klaimList
        ]);
    }

    /**
     * Batalkan / Hapus Klaim Siswa
     */
    public function delete(int $id): void {
        requireLogin();

        $klaim = $this->klaimModel->getById($id);
        if (!$klaim) {
            setFlash('danger', 'Data klaim tidak ditemukan.');
            legacyRedirect('Location: ' . url('klaim/riwayat'));
            exit;
        }

        if (!isAdmin() && $klaim['user_id'] != legacyAuth()['id']) {
            setFlash('danger', 'Anda tidak memiliki izin menghapus klaim ini.');
            legacyRedirect('Location: ' . url('klaim/riwayat'));
            exit;
        }

        if ($klaim['status'] === 'disetujui') {
            setFlash('danger', 'Klaim yang sudah disetujui tidak dapat dihapus.');
            legacyRedirect('Location: ' . url(isAdmin() ? 'admin/klaim' : 'klaim/riwayat'));
            exit;
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        $itemLock = $pdo->prepare('SELECT status FROM barang WHERE id = ? FOR UPDATE');
        $itemLock->execute([(int)$klaim['barang_id']]);
        $itemStatus = $itemLock->fetchColumn();
        $claimLock = $pdo->prepare('SELECT id FROM klaim WHERE id = ? FOR UPDATE');
        $claimLock->execute([$id]);
        $lockedClaim = $this->klaimModel->getById($id);

        if (!$lockedClaim || $lockedClaim['status'] === 'disetujui') {
            $pdo->rollBack();
            setFlash('danger', 'Klaim sudah berubah dan tidak dapat dihapus.');
            legacyRedirect('Location: ' . url(isAdmin() ? 'admin/klaim' : 'klaim/riwayat'));
            exit;
        }

        if (!$this->klaimModel->delete($id)) {
            $pdo->rollBack();
            setFlash('danger', 'Klaim gagal dihapus.');
            legacyRedirect('Location: ' . url(isAdmin() ? 'admin/klaim' : 'klaim/riwayat'));
            exit;
        }

        if ($itemStatus === 'menunggu_klaim') {
            $activeClaims = $pdo->prepare(
                "SELECT COUNT(*) FROM klaim WHERE barang_id = ? AND status IN ('menunggu', 'disetujui')"
            );
            $activeClaims->execute([(int)$klaim['barang_id']]);

            if ((int)$activeClaims->fetchColumn() === 0) {
                $this->barangModel->updateStatus((int)$klaim['barang_id'], 'tersedia');
            }
        }

        $pdo->commit();

        if (!isAdmin()) {
            $this->aktivitasModel->recordSafe([
                'user_id' => (int)legacyAuth()['id'],
                'barang_id' => (int)$klaim['barang_id'],
                'aktivitas' => 'membatalkan_klaim',
                'deskripsi' => 'Membatalkan klaim untuk barang ' . $klaim['nama_barang'] . '.',
                'metadata' => ['channel' => 'web'],
            ]);
        }

        setFlash('success', 'Klaim berhasil dihapus.');
        if (isAdmin()) {
            legacyRedirect('Location: ' . url('admin/klaim'));
        } else {
            legacyRedirect('Location: ' . url('klaim/riwayat'));
        }
        exit;
    }
}
