<?php
/**
 * BarangController
 * Lost & Found SMK Informatika Sumedang
 */

require_once APP_PATH . '/models/Barang.php';
require_once APP_PATH . '/models/LaporanHilang.php';
require_once APP_PATH . '/models/Pengembalian.php';
require_once APP_PATH . '/models/Klaim.php';
require_once APP_PATH . '/models/User.php';

class BarangController {
    private Barang $barangModel;
    private LaporanHilang $laporanModel;
    private Pengembalian $pengembalianModel;
    private Klaim $klaimModel;
    private User $userModel;

    public function __construct() {
        $this->barangModel = new Barang();
        $this->laporanModel = new LaporanHilang();
        $this->pengembalianModel = new Pengembalian();
        $this->klaimModel = new Klaim();
        $this->userModel = new User();
    }

    /**
     * Homepage
     */
    public function home(): void {
        $statsBarang = $this->barangModel->getStats();
        $statsLaporan = $this->laporanModel->getStats();
        $statsPengembalian = $this->pengembalianModel->countAll();
        $stats = [
            'total_ditemukan'    => $statsBarang['total'],
            'total_hilang'       => $statsLaporan['total'],
            'total_dikembalikan' => $statsPengembalian,
            'total_tersedia'     => $statsBarang['tersedia'],
        ];

        // 6 Barang ditemukan terbaru
        $latestBarang = $this->barangModel->getAll([], 6, 0);

        // 3 Laporan hilang terbaru
        $latestLaporan = $this->laporanModel->getAll([], 3, 0);

        renderView('home/index', [
            'title'         => 'Beranda - Lost & Found SMK Informatika Sumedang',
            'stats'         => $stats,
            'latestBarang'  => $latestBarang,
            'latestLaporan' => $latestLaporan,
        ]);
    }

    /**
     * Catalog of Found Items (Barang Ditemukan)
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

        $totalItems = $this->barangModel->count($filters);
        $totalPages = (int)ceil($totalItems / $limit);
        $items      = $this->barangModel->getAll($filters, $limit, $offset);
        $categories = $this->barangModel->getCategories();

        renderView('barang/index', [
            'title'       => 'Barang Ditemukan - Lost & Found',
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
     * Detail Barang Ditemukan
     */
    public function detail(int $id): void {
        $item = $this->barangModel->getById($id);

        if (!$item) {
            setFlash('danger', 'Barang tidak ditemukan.');
            legacyRedirect('Location: ' . url('barang'));
            exit;
        }

        // Cek apakah user saat ini sudah pernah mengajukan klaim untuk barang ini
        $alreadyClaimed = false;
        if (isLoggedIn()) {
            $alreadyClaimed = $this->klaimModel->hasUserClaimed($id, (int)legacyAuth()['id']);
        }

        $adminContact = $this->userModel->getAdminContact();
        $matchingLostReports = $this->laporanModel->getPotentialMatchesForFound($item);

        renderView('barang/detail', [
            'title'               => htmlspecialchars($item['nama_barang']) . ' - Detail Barang',
            'item'                => $item,
            'alreadyClaimed'      => $alreadyClaimed,
            'adminContact'        => $adminContact,
            'matchingLostReports' => $matchingLostReports,
        ]);
    }

    /**
     * Create Barang Ditemukan (Admin / Siswa Pelapor)
     */
    public function create(): void {
        requireLogin();

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama_barang      = trim($_POST['nama_barang'] ?? '');
            $kategori         = trim($_POST['kategori'] ?? '');
            $warna            = trim($_POST['warna'] ?? '');
            $deskripsi        = trim($_POST['deskripsi'] ?? '');
            $lokasi_ditemukan = trim($_POST['lokasi_ditemukan'] ?? '');
            $tanggal_ditemukan = trim($_POST['tanggal_ditemukan'] ?? '');

            // Validasi Wajib
            if (empty($nama_barang) || empty($kategori) || empty($lokasi_ditemukan) || empty($tanggal_ditemukan)) {
                $error = 'Nama barang, kategori, lokasi ditemukan, dan tanggal ditemukan wajib diisi.';
            } else {
                try {
                    $fotoName = null;
                    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $fotoName = handleFileUpload($_FILES['foto']);
                    }

                    $this->barangModel->create([
                        'nama_barang'       => $nama_barang,
                        'kategori'          => $kategori,
                        'warna'             => $warna,
                        'deskripsi'         => $deskripsi,
                        'lokasi_ditemukan'  => $lokasi_ditemukan,
                        'tanggal_ditemukan' => $tanggal_ditemukan,
                        'foto'              => $fotoName,
                        'status'            => 'tersedia',
                        'ditemukan_oleh'    => (int)legacyAuth()['id'],
                    ]);

                    setFlash('success', 'Barang berhasil ditambahkan.');
                    if (isAdmin()) {
                        legacyRedirect('Location: ' . url('admin/barang'));
                    } else {
                        legacyRedirect('Location: ' . url('barang'));
                    }
                    exit;
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        renderView('barang/create', [
            'title' => 'Tambah Barang Ditemukan',
            'error' => $error,
            'old'   => $_POST
        ]);
    }

    /**
     * Edit Barang (Admin Only)
     */
    public function edit(int $id): void {
        requireAdmin();

        $item = $this->barangModel->getById($id);
        if (!$item) {
            setFlash('danger', 'Data barang tidak ditemukan.');
            legacyRedirect('Location: ' . url('admin/barang'));
            exit;
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama_barang       = trim($_POST['nama_barang'] ?? '');
            $kategori          = trim($_POST['kategori'] ?? '');
            $warna             = trim($_POST['warna'] ?? '');
            $deskripsi         = trim($_POST['deskripsi'] ?? '');
            $lokasi_ditemukan  = trim($_POST['lokasi_ditemukan'] ?? '');
            $tanggal_ditemukan = trim($_POST['tanggal_ditemukan'] ?? '');
            $status            = trim($_POST['status'] ?? 'tersedia');

            if (empty($nama_barang) || empty($kategori) || empty($lokasi_ditemukan) || empty($tanggal_ditemukan)) {
                $error = 'Nama barang, kategori, lokasi ditemukan, dan tanggal ditemukan wajib diisi.';
            } else {
                try {
                    $dataToUpdate = [
                        'nama_barang'       => $nama_barang,
                        'kategori'          => $kategori,
                        'warna'             => $warna,
                        'deskripsi'         => $deskripsi,
                        'lokasi_ditemukan'  => $lokasi_ditemukan,
                        'tanggal_ditemukan' => $tanggal_ditemukan,
                        'status'            => $status,
                    ];

                    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $fotoName = handleFileUpload($_FILES['foto'], $item['foto']);
                        $dataToUpdate['foto'] = $fotoName;
                    }

                    $this->barangModel->update($id, $dataToUpdate);
                    setFlash('success', 'Data barang berhasil diperbarui.');
                    legacyRedirect('Location: ' . url('admin/barang'));
                    exit;
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        renderView('barang/edit', [
            'title' => 'Edit Barang Ditemukan - ' . htmlspecialchars($item['nama_barang']),
            'item'  => $item,
            'error' => $error
        ]);
    }

    /**
     * Delete Barang (Admin Only)
     */
    public function delete(int $id): void {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $item = $this->barangModel->getById($id);
            if ($item) {
                $this->barangModel->delete($id);
                setFlash('success', 'Barang berhasil dihapus.');
            } else {
                setFlash('danger', 'Barang tidak ditemukan.');
            }
        }

        legacyRedirect('Location: ' . url('admin/barang'));
        exit;
    }
}
