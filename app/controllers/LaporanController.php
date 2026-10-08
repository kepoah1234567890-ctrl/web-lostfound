<?php
/**
 * LaporanController
 * Lost & Found SMK Informatika Sumedang
 */

require_once APP_PATH . '/models/LaporanHilang.php';
require_once APP_PATH . '/models/Barang.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Aktivitas.php';

class LaporanController {
    private LaporanHilang $laporanModel;
    private Barang $barangModel;
    private User $userModel;
    private Aktivitas $aktivitasModel;

    public function __construct() {
        $this->laporanModel = new LaporanHilang();
        $this->barangModel = new Barang();
        $this->userModel = new User();
        $this->aktivitasModel = new Aktivitas();
    }

    /**
     * Public list of Lost Reports (Barang Hilang)
     */
    public function index(): void {
        $search   = trim($_GET['search'] ?? '');
        $kategori = trim($_GET['kategori'] ?? '');
        $status   = trim($_GET['status'] ?? '');
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $limit    = 9;
        $offset   = ($page - 1) * $limit;

        $filters = [];
        if (!empty($search))   $filters['search']   = $search;
        if (!empty($kategori)) $filters['kategori'] = $kategori;
        if (!empty($status))   $filters['status']   = $status;

        $totalItems = $this->laporanModel->count($filters);
        $totalPages = (int)ceil($totalItems / $limit);
        $items      = $this->laporanModel->getAll($filters, $limit, $offset);
        $categories = $this->laporanModel->getCategories();

        renderView('laporan/index', [
            'title'       => 'Daftar Barang Hilang - Lost & Found',
            'items'       => $items,
            'categories'  => $categories,
            'filters'     => [
                'search'   => $search,
                'kategori' => $kategori,
                'status'   => $status,
            ],
            'pagination'  => [
                'page'       => $page,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
            ]
        ]);
    }

    /**
     * Form Lapor Barang Hilang (Siswa)
     */
    public function hilang(): void {
        requireLogin();

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama_barang     = trim($_POST['nama_barang'] ?? '');
            $kategori        = trim($_POST['kategori'] ?? '');
            $warna           = trim($_POST['warna'] ?? '');
            $deskripsi       = trim($_POST['deskripsi'] ?? '');
            $lokasi_terakhir = trim($_POST['lokasi_terakhir'] ?? '');
            $tanggal_hilang  = trim($_POST['tanggal_hilang'] ?? '');

            if (empty($nama_barang) || empty($kategori) || empty($tanggal_hilang)) {
                $error = 'Nama barang, kategori, dan tanggal hilang wajib diisi.';
            } else {
                try {
                    $fotoName = null;
                    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $fotoName = handleFileUpload($_FILES['foto']);
                    }

                    $laporanId = $this->laporanModel->create([
                        'user_id'         => (int)legacyAuth()['id'],
                        'nama_barang'     => $nama_barang,
                        'kategori'        => $kategori,
                        'warna'           => $warna,
                        'deskripsi'       => $deskripsi,
                        'lokasi_terakhir' => $lokasi_terakhir,
                        'tanggal_hilang'  => $tanggal_hilang,
                        'foto'            => $fotoName,
                        'status'          => 'menunggu'
                    ]);

                    $this->aktivitasModel->recordSafe([
                        'user_id' => (int)legacyAuth()['id'],
                        'laporan_hilang_id' => $laporanId,
                        'aktivitas' => 'membuat_laporan_hilang',
                        'deskripsi' => 'Membuat laporan kehilangan untuk ' . $nama_barang . '.',
                        'metadata' => ['channel' => 'web'],
                    ]);

                    setFlash('success', 'Laporan barang hilang berhasil dikirim. Menunggu verifikasi dari admin/petugas.');
                    legacyRedirect('Location: ' . url('laporan/riwayat'));
                    exit;
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        renderView('laporan/hilang', [
            'title' => 'Laporkan Barang Hilang - Lost & Found',
            'error' => $error,
            'old'   => $_POST
        ]);
    }

    /**
     * Form Lapor Barang Ditemukan (Siswa)
     */
    public function ditemukan(): void {
        requireLogin();

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama_barang       = trim($_POST['nama_barang'] ?? '');
            $kategori          = trim($_POST['kategori'] ?? '');
            $warna             = trim($_POST['warna'] ?? '');
            $deskripsi         = trim($_POST['deskripsi'] ?? '');
            $lokasi_ditemukan  = trim($_POST['lokasi_ditemukan'] ?? '');
            $tanggal_ditemukan = trim($_POST['tanggal_ditemukan'] ?? '');

            if (empty($nama_barang) || empty($kategori) || empty($lokasi_ditemukan) || empty($tanggal_ditemukan)) {
                $error = 'Nama barang, kategori, lokasi ditemukan, dan tanggal ditemukan wajib diisi.';
            } else {
                try {
                    $fotoName = null;
                    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $fotoName = handleFileUpload($_FILES['foto']);
                    }

                    $barangId = $this->barangModel->create([
                        'nama_barang'       => $nama_barang,
                        'kategori'          => $kategori,
                        'warna'             => $warna,
                        'deskripsi'         => $deskripsi,
                        'lokasi_ditemukan'  => $lokasi_ditemukan,
                        'tanggal_ditemukan' => $tanggal_ditemukan,
                        'foto'              => $fotoName,
                        'status'            => 'tersedia',
                        'ditemukan_oleh'    => (int)legacyAuth()['id']
                    ]);

                    $this->aktivitasModel->recordSafe([
                        'user_id' => (int)legacyAuth()['id'],
                        'barang_id' => $barangId,
                        'aktivitas' => 'melaporkan_barang_ditemukan',
                        'deskripsi' => 'Melaporkan barang ditemukan: ' . $nama_barang . '.',
                        'metadata' => ['channel' => 'web'],
                    ]);

                    setFlash('success', 'Terima kasih! Laporan barang yang Anda temukan berhasil disimpan dan telah dipublikasikan.');
                    legacyRedirect('Location: ' . url('barang'));
                    exit;
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        renderView('laporan/ditemukan', [
            'title'        => 'Laporkan Barang Ditemukan - Lost & Found',
            'error'        => $error,
            'old'          => $_POST,
            'adminContact' => $this->userModel->getAdminContact(),
        ]);
    }

    /**
     * Riwayat Laporan Kehilangan Milik Siswa yang Login
     */
    public function riwayat(): void {
        requireLogin();

        $userId = (int)legacyAuth()['id'];
        $laporanList = $this->laporanModel->getByUserId($userId);

        renderView('laporan/riwayat', [
            'title'       => 'Riwayat Laporan Kehilangan Saya',
            'laporanList' => $laporanList
        ]);
    }

    /**
     * Delete Laporan Hilang (Oleh Pemilik atau Admin)
     */
    public function delete(int $id): void {
        requireLogin();

        $laporan = $this->laporanModel->getById($id);
        if (!$laporan) {
            setFlash('danger', 'Laporan tidak ditemukan.');
            legacyRedirect('Location: ' . url('laporan/riwayat'));
            exit;
        }

        // Cek otorisasi
        if (!isAdmin() && $laporan['user_id'] != legacyAuth()['id']) {
            setFlash('danger', 'Anda tidak memiliki izin menghapus laporan ini.');
            legacyRedirect('Location: ' . url('laporan/riwayat'));
            exit;
        }

        $this->laporanModel->delete($id);
        if (!isAdmin()) {
            $this->aktivitasModel->recordSafe([
                'user_id' => (int)legacyAuth()['id'],
                'aktivitas' => 'menghapus_laporan_hilang',
                'deskripsi' => 'Menghapus laporan kehilangan: ' . $laporan['nama_barang'] . '.',
                'metadata' => ['channel' => 'web'],
            ]);
        }
        setFlash('success', 'Laporan berhasil dihapus.');

        if (isAdmin()) {
            legacyRedirect('Location: ' . url('admin/laporan'));
        } else {
            legacyRedirect('Location: ' . url('laporan/riwayat'));
        }
        exit;
    }
}
