<?php
/**
 * AdminController
 * Lost & Found SMK Informatika Sumedang
 */

require_once APP_PATH . '/models/Barang.php';
require_once APP_PATH . '/models/LaporanHilang.php';
require_once APP_PATH . '/models/Klaim.php';
require_once APP_PATH . '/models/Pengembalian.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Aktivitas.php';

class AdminController {
    private Barang $barangModel;
    private LaporanHilang $laporanModel;
    private Klaim $klaimModel;
    private Pengembalian $pengembalianModel;
    private User $userModel;
    private Aktivitas $aktivitasModel;

    public function __construct() {
        requireAdmin();
        $this->barangModel = new Barang();
        $this->laporanModel = new LaporanHilang();
        $this->klaimModel = new Klaim();
        $this->pengembalianModel = new Pengembalian();
        $this->userModel = new User();
        $this->aktivitasModel = new Aktivitas();
    }

    /**
     * Admin Dashboard with Statistics & Recent Activities
     */
    public function users(): void {
        $search = trim($_GET['search'] ?? '');
        $users = $this->userModel->getAll();
        if ($search !== '') {
            $users = array_values(array_filter($users, function ($u) use ($search) {
                $haystack = strtolower(($u['nama'] ?? '') . ' ' . ($u['nis'] ?? '') . ' ' . ($u['email'] ?? '') . ' ' . ($u['no_telepon'] ?? ''));
                return strpos($haystack, strtolower($search)) !== false;
            }));
        }
        renderAdminView('admin/users', [
            'title' => 'Kelola User - Admin',
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function updateUserContact(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            legacyRedirect('Location: ' . url('admin/users'));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $nis = trim($_POST['nis'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $noTelepon = trim($_POST['no_telepon'] ?? '');
        $kelas = trim($_POST['kelas'] ?? '');
        $user = $this->userModel->findById($id);

        if (!$user || $user['role'] !== 'siswa') {
            setFlash('danger', 'User siswa tidak ditemukan.');
        } elseif ($nama === '' || $nis === '' || $kelas === '') {
            setFlash('danger', 'Nama, NIS, dan kelas wajib diisi.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('danger', 'Format email tidak valid.');
        } elseif ($noTelepon === '') {
            setFlash('danger', 'Nomor telepon wajib diisi.');
        } elseif ($this->userModel->findByEmailExceptId($email, $id)) {
            setFlash('danger', 'Email tersebut sudah digunakan akun lain.');
        } elseif ($this->userModel->findByNisExceptId($nis, $id)) {
            setFlash('danger', 'NIS tersebut sudah digunakan akun lain.');
        } elseif ($this->userModel->updateAdminUser($id, $nama, $nis, $email, $noTelepon, $kelas)) {
            $this->aktivitasModel->recordSafe([
                'user_id' => (int)legacyAuth()['id'],
                'target_user_id' => $id,
                'aktivitas' => 'admin_mengedit_user',
                'deskripsi' => 'Admin memperbarui data user "' . $nama . '".',
                'metadata' => ['actor_role'=>'admin']
            ]);
            setFlash('success', 'Data user berhasil diperbarui oleh Admin.');
        } else {
            setFlash('danger', 'Gagal memperbarui data user.');
        }

        legacyRedirect('Location: ' . url('admin/users'));
        exit;
    }

    public function resetUserPassword(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            legacyRedirect('Location: ' . url('admin/users')); exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        $user = $this->userModel->findById($id);
        if (!$user || $user['role'] !== 'siswa') {
            setFlash('danger', 'User siswa tidak ditemukan.');
            legacyRedirect('Location: ' . url('admin/users')); exit;
        }
        $temporaryPassword = 'LF-' . strtoupper(bin2hex(random_bytes(4)));
        if ($this->userModel->resetPasswordToTemporary($id, $temporaryPassword)) {
            $_SESSION['temporary_password_result'] = [
                'nama' => $user['nama'],
                'password' => $temporaryPassword,
            ];
            setFlash('success', 'Password sementara berhasil dibuat untuk ' . $user['nama'] . '.');
        } else {
            setFlash('danger', 'Gagal mereset password user.');
        }
        legacyRedirect('Location: ' . url('admin/users')); exit;
    }

    public function createUser(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            legacyRedirect('Location: ' . url('admin/users'));
            exit;
        }

        $nama = trim((string)($_POST['nama'] ?? ''));
        $nis = trim((string)($_POST['nis'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $noTelepon = trim((string)($_POST['no_telepon'] ?? ''));
        $kelas = trim((string)($_POST['kelas'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirmation'] ?? '');

        if ($nama === '' || $nis === '' || $email === '' || $noTelepon === '' || $kelas === '') {
            setFlash('danger', 'Semua field wajib diisi.');
            legacyRedirect('Location: ' . url('admin/users'));
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('danger', 'Format email tidak valid.');
            legacyRedirect('Location: ' . url('admin/users'));
            exit;
        }

        if (!preg_match('/^\d{12}$/', $noTelepon)) {
            setFlash('danger', 'Nomor telepon harus 12 digit angka.');
            legacyRedirect('Location: ' . url('admin/users'));
            exit;
        }

        if (strlen($password) < 8) {
            setFlash('danger', 'Password minimal 8 karakter.');
            legacyRedirect('Location: ' . url('admin/users'));
            exit;
        }

        if ($password !== $passwordConfirm) {
            setFlash('danger', 'Konfirmasi password tidak cocok.');
            legacyRedirect('Location: ' . url('admin/users'));
            exit;
        }

        if ($this->userModel->findByEmail($email)) {
            setFlash('danger', 'Email tersebut sudah dipakai user lain.');
            legacyRedirect('Location: ' . url('admin/users'));
            exit;
        }

        if ($this->userModel->findByNis($nis)) {
            setFlash('danger', 'NIS tersebut sudah dipakai user lain.');
            legacyRedirect('Location: ' . url('admin/users'));
            exit;
        }

        $userId = $this->userModel->create([
            'nama' => $nama,
            'nis' => $nis,
            'email' => $email,
            'no_telepon' => $noTelepon,
            'password' => $password,
            'kelas' => $kelas,
            'role' => 'siswa',
        ]);

        if ($userId <= 0) {
            setFlash('danger', 'Gagal menambahkan user baru.');
            legacyRedirect('Location: ' . url('admin/users'));
            exit;
        }

        $this->aktivitasModel->recordSafe([
            'user_id' => (int)legacyAuth()['id'],
            'target_user_id' => $userId,
            'aktivitas' => 'admin_membuat_user',
            'deskripsi' => 'Admin menambahkan user baru "' . $nama . '" dan menetapkan password sesuai permintaan.',
            'metadata' => ['actor_role' => 'admin', 'channel' => 'web', 'source' => 'admin-panel'],
        ]);

        $_SESSION['new_user_password_result'] = [
            'nama' => $nama,
            'password' => $password,
        ];
        setFlash('success', 'User berhasil ditambahkan. Password sudah diatur sesuai permintaan admin.');
        legacyRedirect('Location: ' . url('admin/users'));
        exit;
    }

    public function updateUserPassword(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            legacyRedirect('Location: ' . url('admin/users'));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirmation'] ?? '');
        $user = $this->userModel->findById($id);

        if (!$user || $user['role'] !== 'siswa') {
            setFlash('danger', 'User siswa tidak ditemukan.');
            legacyRedirect('Location: ' . url('admin/users'));
            exit;
        }

        if (strlen($password) < 8) {
            setFlash('danger', 'Password minimal 8 karakter.');
            legacyRedirect('Location: ' . url('admin/users'));
            exit;
        }

        if ($password !== $passwordConfirm) {
            setFlash('danger', 'Konfirmasi password tidak cocok.');
            legacyRedirect('Location: ' . url('admin/users'));
            exit;
        }

        if ($this->userModel->setPassword($id, $password)) {
            $this->aktivitasModel->recordSafe([
                'user_id' => (int)legacyAuth()['id'],
                'target_user_id' => $id,
                'aktivitas' => 'admin_mengubah_password_user',
                'deskripsi' => 'Admin mengganti password user "' . $user['nama'] . '" sesuai permintaan.',
                'metadata' => ['actor_role' => 'admin', 'channel' => 'web', 'source' => 'admin-panel'],
            ]);
            $_SESSION['updated_user_password_result'] = [
                'nama' => $user['nama'],
                'password' => $password,
            ];
            setFlash('success', 'Password user berhasil diubah sesuai permintaan admin.');
        } else {
            setFlash('danger', 'Gagal mengubah password user.');
        }

        legacyRedirect('Location: ' . url('admin/users'));
        exit;
    }

    public function dashboard(): void {
        $statsBarang = $this->barangModel->getStats();
        $statsLaporan = $this->laporanModel->getStats();
        $statsKlaim = $this->klaimModel->getStats();
        $totalPengembalian = $this->pengembalianModel->countAll();

        $stats = [
            'total_barang'      => $statsBarang['total'],
            'barang_tersedia'   => $statsBarang['tersedia'],
            'barang_hilang'     => $statsLaporan['total'],
            'menunggu_klaim'    => $statsKlaim['menunggu'],
            'sudah_dikembalikan'=> $totalPengembalian,
            'total_user'        => $this->userModel->countStudents(),
        ];

        // Recent tables
        $recentBarang  = $this->barangModel->getAll([], 5, 0);
        $recentLaporan = $this->laporanModel->getAll([], 5, 0);
        $recentKlaim   = $this->klaimModel->getAll([], 5, 0);
        $userActivityFilters = ['actor_role' => 'siswa'];
        $recentAktivitas = $this->aktivitasModel->getAll($userActivityFilters, 8, 0);
        $laporanStatusCounts = [
            'menunggu' => (int)$statsLaporan['menunggu'],
            'diverifikasi' => (int)$statsLaporan['diverifikasi'],
            'ditemukan' => (int)$statsLaporan['ditemukan'],
            'selesai' => (int)$statsLaporan['selesai'],
        ];

        renderAdminView('admin/dashboard', [
            'title'           => 'Dashboard Admin - Lost & Found SMK Informatika Sumedang',
            'stats'           => $stats,
            'recentBarang'    => $recentBarang,
            'recentLaporan'   => $recentLaporan,
            'recentKlaim'     => $recentKlaim,
            'recentAktivitas' => $recentAktivitas,
            'activityCount'   => $this->aktivitasModel->count($userActivityFilters),
            'laporanStatusCounts' => $laporanStatusCounts,
        ]);
    }

    /**
     * Riwayat seluruh aktivitas yang tercatat di sistem.
     * Aktivitas user-to-user tetap tampil di sini walaupun Admin tidak terlibat langsung.
     */
    public function aktivitas(): void {
        $search = trim($_GET['search'] ?? '');
        $aktivitas = trim($_GET['aktivitas'] ?? '');
        $channel = trim($_GET['channel'] ?? '');

        $filters = ['actor_role' => 'siswa'];
        if ($search !== '') $filters['search'] = $search;
        if ($aktivitas !== '') $filters['aktivitas'] = $aktivitas;
        if ($channel !== '') $filters['channel'] = $channel;

        $activityGroups = $this->aktivitasModel->getAccountSummaries($filters);
        $totalAktivitas = $this->aktivitasModel->count($filters);
        $activityTypes = $this->aktivitasModel->getActivityTypes();

        renderAdminView('admin/aktivitas', [
            'title' => 'Aktivitas User - Admin',
            'activityGroups' => $activityGroups,
            'totalAktivitas' => $totalAktivitas,
            'activityTypes' => $activityTypes,
            'filters' => $filters
        ]);
    }

    /**
     * Admin Manage Barang (Barang Ditemukan)
     */
    public function barang(): void {
        $search   = trim($_GET['search'] ?? '');
        $status   = trim($_GET['status'] ?? '');
        $kategori = trim($_GET['kategori'] ?? '');

        $filters = [];
        if (!empty($search))   $filters['search'] = $search;
        if (!empty($status))   $filters['status'] = $status;
        if (!empty($kategori)) $filters['kategori'] = $kategori;

        $items = $this->barangModel->getAll($filters);
        $categories = $this->barangModel->getCategories();

        // Cari laporan kehilangan yang berpotensi cocok dengan setiap barang temuan.
        // Data pelapor sudah dilengkapi email, NIS, kelas, dan nomor telepon
        // oleh LaporanHilang::getPotentialMatchesForFound().
        foreach ($items as &$item) {
            $item['potential_owners'] = $this->laporanModel->getPotentialMatchesForFound($item);
        }
        unset($item);

        renderAdminView('admin/barang', [
            'title'      => 'Kelola Barang Ditemukan - Admin',
            'items'      => $items,
            'categories' => $categories,
            'filters'    => $filters
        ]);
    }

    /**
     * Admin-only edit barang. Menggunakan form edit yang sama, tetapi seluruh
     * proses tetap berada di jalur Admin dan dicatat ke audit.
     */
    public function editBarang(int $id): void {
        requireAdmin();
        $item = $this->barangModel->getById($id);
        if (!$item) {
            setFlash('danger', 'Data barang tidak ditemukan.');
            legacyRedirect('Location: ' . url('admin/barang'));
            exit;
        }
        $item['id'] = (int)($item['id'] ?? $id);

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama = trim($_POST['nama_barang'] ?? '');
            $kategori = trim($_POST['kategori'] ?? '');
            $warna = trim($_POST['warna'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $lokasi = trim($_POST['lokasi_ditemukan'] ?? '');
            $tanggal = trim($_POST['tanggal_ditemukan'] ?? '');
            $status = trim($_POST['status'] ?? 'tersedia');
            $allowed = ['tersedia','menunggu_klaim','diklaim','dikembalikan'];

            if ($nama === '' || $kategori === '' || $lokasi === '' || $tanggal === '') {
                $error = 'Nama barang, kategori, lokasi ditemukan, dan tanggal ditemukan wajib diisi.';
            } elseif (!in_array($status, $allowed, true)) {
                $error = 'Status barang tidak valid.';
            } else {
                try {
                    $data = [
                        'nama_barang' => $nama,
                        'kategori' => $kategori,
                        'warna' => $warna,
                        'deskripsi' => $deskripsi,
                        'lokasi_ditemukan' => $lokasi,
                        'tanggal_ditemukan' => $tanggal,
                        'status' => $status,
                    ];
                    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $data['foto'] = handleFileUpload($_FILES['foto'], $item['foto'] ?? null);
                    }
                    $this->barangModel->update($id, $data);
                    $this->aktivitasModel->recordSafe([
                        'user_id' => (int)legacyAuth()['id'],
                        'barang_id' => $id,
                        'aktivitas' => 'admin_mengedit_barang',
                        'deskripsi' => 'Admin mengedit data barang "' . $nama . '".',
                        'metadata' => ['actor_role'=>'admin','status_baru'=>$status]
                    ]);
                    setFlash('success', 'Data barang berhasil diperbarui.');
                    legacyRedirect('Location: ' . url('admin/barang'));
                    exit;
                } catch (Throwable $e) {
                    $error = $e->getMessage();
                }
            }
        }

        renderAdminView('barang/edit', [
            'title' => 'Edit Barang Ditemukan - ' . htmlspecialchars($item['nama_barang']),
            'item' => $item,
            'error' => $error,
        ]);
    }

    public function deleteBarang(int $id): void {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            legacyRedirect('Location: ' . url('admin/barang'));
            exit;
        }
        $item = $this->barangModel->getById($id);
        if (!$item) {
            setFlash('danger', 'Barang tidak ditemukan.');
        } else {
            try {
                $this->barangModel->delete($id);
                $this->aktivitasModel->recordSafe([
                    'user_id' => (int)legacyAuth()['id'],
                    'barang_id' => $id,
                    'aktivitas' => 'admin_menghapus_barang',
                    'deskripsi' => 'Admin menghapus barang "' . ($item['nama_barang'] ?? '-') . '".',
                    'metadata' => ['actor_role'=>'admin']
                ]);
                setFlash('success', 'Barang berhasil dihapus.');
            } catch (Throwable $e) {
                setFlash('danger', 'Barang gagal dihapus: ' . $e->getMessage());
            }
        }
        legacyRedirect('Location: ' . url('admin/barang'));
        exit;
    }

    /**
     * Update Status Barang via POST
     */
    public function updateBarangStatus(): void {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            legacyRedirect('Location: ' . url('admin/barang'));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $validStatuses = ['tersedia', 'menunggu_klaim', 'diklaim', 'dikembalikan'];

        if (!in_array($status, $validStatuses, true)) {
            setFlash('danger', 'Status tidak valid.');
            legacyRedirect('Location: ' . url('admin/barang'));
            exit;
        }

        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();
            $itemLock = $pdo->prepare('SELECT status FROM barang WHERE id = ? FOR UPDATE');
            $itemLock->execute([$id]);
            $currentStatus = $itemLock->fetchColumn();

            if ($currentStatus === false) {
                $pdo->rollBack();
                setFlash('danger', 'Barang tidak ditemukan.');
                legacyRedirect('Location: ' . url('admin/barang'));
                exit;
            }

            $claimCounts = $pdo->prepare(
                "SELECT
                    SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) AS pending_count,
                    SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) AS approved_count
                 FROM klaim WHERE barang_id = ?"
            );
            $claimCounts->execute([$id]);
            $counts = $claimCounts->fetch(PDO::FETCH_ASSOC) ?: [];
            $pendingClaims = (int)($counts['pending_count'] ?? 0);
            $approvedClaims = (int)($counts['approved_count'] ?? 0);

            $statusIsValidForClaims = match ($status) {
                'tersedia' => $pendingClaims === 0 && $approvedClaims === 0,
                'menunggu_klaim' => $pendingClaims > 0,
                'diklaim', 'dikembalikan' => $approvedClaims > 0,
            };

            if (!$statusIsValidForClaims) {
                $pdo->rollBack();
                setFlash('danger', 'Status barang tidak sesuai dengan status klaim aktif.');
                legacyRedirect('Location: ' . url('admin/barang'));
                exit;
            }

            if (!$this->barangModel->updateStatus($id, $status)) {
                throw new RuntimeException('Status barang gagal diperbarui.');
            }

            $pdo->commit();

            $this->aktivitasModel->recordSafe([
                'user_id' => (int)legacyAuth()['id'],
                'barang_id' => $id,
                'aktivitas' => 'admin_mengubah_status_barang',
                'deskripsi' => 'Admin mengubah status barang menjadi ' . $status . '.',
                'metadata' => ['actor_role' => 'admin', 'status_baru' => $status]
            ]);
            setFlash('success', 'Status barang berhasil diperbarui menjadi ' . ucfirst($status) . '.');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            setFlash('danger', 'Status barang gagal diperbarui: ' . $e->getMessage());
        }

        legacyRedirect('Location: ' . url('admin/barang'));
        exit;
    }

    /**
     * Catat bahwa Admin menghubungi calon pemilik barang.
     * Dipanggil dari tombol Telepon / WhatsApp / Email pada halaman Admin Barang.
     */
    public function logOwnerContact(): void {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method tidak diizinkan.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $targetUserId = (int)($_POST['target_user_id'] ?? 0);
        $laporanId = (int)($_POST['laporan_hilang_id'] ?? 0);
        $barangId = (int)($_POST['barang_id'] ?? 0);
        $channel = trim($_POST['channel'] ?? 'kontak');

        $allowedChannels = ['telepon', 'whatsapp', 'email', 'kontak'];
        if (!in_array($channel, $allowedChannels, true)) {
            $channel = 'kontak';
        }

        $owner = $this->userModel->findById($targetUserId);
        $laporan = $laporanId > 0 ? $this->laporanModel->getById($laporanId) : null;
        $barang = $barangId > 0 ? $this->barangModel->getById($barangId) : null;

        if (!$owner || ($owner['role'] ?? '') !== 'siswa') {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Data calon pemilik tidak ditemukan.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO aktivitas_user (
                    user_id, target_user_id, laporan_hilang_id, barang_id,
                    aktivitas, deskripsi, metadata
                ) VALUES (
                    :user_id, :target_user_id, :laporan_hilang_id, :barang_id,
                    :aktivitas, :deskripsi, :metadata
                )
            ");

            $metadata = json_encode([
                'channel' => $channel,
                'actor_role' => 'admin',
                'owner_nama' => $owner['nama'] ?? null,
                'owner_telepon' => $owner['no_telepon'] ?? null,
            ], JSON_UNESCAPED_UNICODE);

            $deskripsi = 'Admin menghubungi calon pemilik melalui ' . strtoupper($channel);
            if ($barang) {
                $deskripsi .= ' untuk barang "' . ($barang['nama_barang'] ?? '-') . '"';
            }

            $stmt->execute([
                'user_id' => (int)legacyAuth()['id'],
                'target_user_id' => $targetUserId,
                'laporan_hilang_id' => $laporan ? $laporanId : null,
                'barang_id' => $barang ? $barangId : null,
                'aktivitas' => 'admin_menghubungi_pemilik',
                'deskripsi' => $deskripsi,
                'metadata' => $metadata,
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Aktivitas kontak Admin berhasil dicatat.'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Gagal mencatat aktivitas kontak Admin.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * Admin Manage Laporan Hilang
     */
    public function laporan(): void {
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');

        $filters = [];
        if (!empty($search)) $filters['search'] = $search;
        if (!empty($status)) $filters['status'] = $status;
        if ($dateFrom !== '') $filters['date_from'] = $dateFrom;
        if ($dateTo !== '') $filters['date_to'] = $dateTo;

        $laporanList = $this->laporanModel->getAll($filters);
        $total = count($laporanList);
        $statusCounts = ['menunggu'=>0,'diverifikasi'=>0,'ditemukan'=>0,'selesai'=>0];
        foreach ($laporanList as $row) {
            if (isset($statusCounts[$row['status']])) $statusCounts[$row['status']]++;
        }

        renderAdminView('admin/laporan', [
            'title'        => 'Laporan Barang Hilang - Admin',
            'laporanList'  => $laporanList,
            'filters'      => $filters,
            'reportTotal'  => $total,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function laporanPrint(): void {
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');
        $filters = [];
        if ($search !== '') $filters['search'] = $search;
        if ($status !== '') $filters['status'] = $status;
        if ($dateFrom !== '') $filters['date_from'] = $dateFrom;
        if ($dateTo !== '') $filters['date_to'] = $dateTo;

        $laporanList = $this->laporanModel->getAll($filters);
        $statusCounts = ['menunggu'=>0,'diverifikasi'=>0,'ditemukan'=>0,'selesai'=>0];
        foreach ($laporanList as $row) {
            if (isset($statusCounts[$row['status']])) $statusCounts[$row['status']]++;
        }

        renderAdminPrintView('admin/laporan-print', [
            'title' => 'Rekap Laporan Barang Hilang',
            'laporanList' => $laporanList,
            'filters' => $filters,
            'statusCounts' => $statusCounts,
            'generatedAt' => date('d/m/Y H:i'),
        ]);
    }

    /**
     * Verifikasi & Ubah Status Laporan Hilang
     */
    public function updateLaporanStatus(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $status = trim($_POST['status'] ?? '');

            $validStatuses = ['menunggu', 'diverifikasi', 'ditemukan', 'selesai'];
            if (!in_array($status, $validStatuses, true)) {
                setFlash('danger', 'Status laporan tidak valid.');
            } else {
                $laporan = $this->laporanModel->getById($id);
                if (!$laporan) {
                    setFlash('danger', 'Laporan tidak ditemukan atau sudah dihapus.');
                } elseif (!$this->laporanModel->updateStatus($id, $status)) {
                    setFlash('danger', 'Status laporan gagal diperbarui.');
                } else {
                    $this->aktivitasModel->recordSafe([
                        'user_id' => (int)legacyAuth()['id'],
                        'target_user_id' => $laporan['user_id'],
                        'laporan_hilang_id' => $id,
                        'aktivitas' => 'admin_mengubah_status_laporan',
                        'deskripsi' => 'Admin mengubah status laporan "' . $laporan['nama_barang'] . '" menjadi ' . $status . '.',
                        'metadata' => ['actor_role' => 'admin', 'status_baru' => $status]
                    ]);
                    setFlash('success', 'Status laporan berhasil diubah menjadi ' . ucfirst($status) . '.');
                }
            }
        }
        legacyRedirect('Location: ' . url('admin/laporan'));
        exit;
    }

    public function deleteLaporan(int $id): void {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            legacyRedirect('Location: ' . url('admin/laporan'));
            exit;
        }
        $laporan = $this->laporanModel->getById($id);
        if (!$laporan) {
            setFlash('danger', 'Laporan tidak ditemukan.');
        } else {
            try {
                $this->laporanModel->delete($id);
                $this->aktivitasModel->recordSafe([
                    'user_id' => (int)legacyAuth()['id'],
                    'target_user_id' => $laporan['user_id'] ?? null,
                    'laporan_hilang_id' => $id,
                    'aktivitas' => 'admin_menghapus_laporan',
                    'deskripsi' => 'Admin menghapus laporan barang hilang "' . ($laporan['nama_barang'] ?? '-') . '".',
                    'metadata' => ['actor_role'=>'admin']
                ]);
                setFlash('success', 'Laporan hilang berhasil dihapus.');
            } catch (Throwable $e) {
                setFlash('danger', 'Laporan gagal dihapus: ' . $e->getMessage());
            }
        }
        legacyRedirect('Location: ' . url('admin/laporan'));
        exit;
    }

    /**
     * Admin Manage Klaim
     */
    public function klaim(): void {
        $status = trim($_GET['status'] ?? '');
        $filters = [];
        if (!empty($status)) $filters['status'] = $status;

        $klaimList = $this->klaimModel->getAll($filters);

        renderAdminView('admin/klaim', [
            'title'     => 'Kelola Verifikasi Klaim - Admin',
            'klaimList' => $klaimList,
            'filters'   => $filters
        ]);
    }

    public function deleteKlaim(int $id): void {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            legacyRedirect('Location: ' . url('admin/klaim'));
            exit;
        }
        $klaim = $this->klaimModel->getById($id);
        if (!$klaim) {
            setFlash('danger', 'Data klaim tidak ditemukan.');
        } elseif ($klaim['status'] === 'disetujui') {
            setFlash('danger', 'Klaim yang sudah disetujui tidak dapat dihapus.');
        } else {
            $pdo = Database::getConnection();
            try {
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
                    legacyRedirect('Location: ' . url('admin/klaim'));
                    exit;
                }

                if (!$this->klaimModel->delete($id)) {
                    throw new RuntimeException('Klaim gagal dihapus.');
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
                $this->aktivitasModel->recordSafe([
                    'user_id' => (int)legacyAuth()['id'],
                    'target_user_id' => $klaim['user_id'] ?? null,
                    'barang_id' => $klaim['barang_id'] ?? null,
                    'klaim_id' => $id,
                    'aktivitas' => 'admin_menghapus_klaim',
                    'deskripsi' => 'Admin menghapus klaim barang "' . ($klaim['nama_barang'] ?? '-') . '".',
                    'metadata' => ['actor_role'=>'admin']
                ]);
                setFlash('success', 'Klaim berhasil dihapus.');
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                setFlash('danger', 'Klaim gagal dihapus: ' . $e->getMessage());
            }
        }
        legacyRedirect('Location: ' . url('admin/klaim'));
        exit;
    }

    /**
     * Verifikasi Klaim (Setujui / Tolak / Beri Catatan)
     */
    public function verifyKlaim(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            legacyRedirect('Location: ' . url('admin/klaim'));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $catatanAdmin = trim($_POST['catatan_admin'] ?? '');

        if (!in_array($status, ['disetujui', 'ditolak', 'menunggu'], true)) {
            setFlash('danger', 'Status verifikasi klaim tidak valid.');
            legacyRedirect('Location: ' . url('admin/klaim'));
            exit;
        }

        $klaim = $this->klaimModel->getById($id);
        if (!$klaim) {
            setFlash('danger', 'Data klaim tidak ditemukan.');
            legacyRedirect('Location: ' . url('admin/klaim'));
            exit;
        }

        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $itemLock = $pdo->prepare('SELECT status FROM barang WHERE id = ? FOR UPDATE');
            $itemLock->execute([(int)$klaim['barang_id']]);
            $itemStatus = $itemLock->fetchColumn();

            $claimLock = $pdo->prepare('SELECT id FROM klaim WHERE id = ? FOR UPDATE');
            $claimLock->execute([$id]);
            $lockedClaimExists = $claimLock->fetchColumn();

            if ($itemStatus === false || !$lockedClaimExists) {
                $pdo->rollBack();
                setFlash('danger', 'Barang atau klaim sudah tidak tersedia.');
                legacyRedirect('Location: ' . url('admin/klaim'));
                exit;
            }

            $klaim = $this->klaimModel->getById($id);

            if ($klaim['status'] === 'disetujui' && $status !== 'disetujui') {
                $pdo->rollBack();
                setFlash('danger', 'Klaim yang sudah disetujui tidak dapat dibatalkan.');
                legacyRedirect('Location: ' . url('admin/klaim'));
                exit;
            }

            if (
                $status === 'disetujui' &&
                $klaim['status'] !== 'disetujui' &&
                in_array($itemStatus, ['diklaim', 'dikembalikan'], true)
            ) {
                $pdo->rollBack();
                setFlash('danger', 'Barang ini sudah memiliki klaim yang disetujui.');
                legacyRedirect('Location: ' . url('admin/klaim'));
                exit;
            }

            $approvedClaim = $pdo->prepare(
                "SELECT id FROM klaim WHERE barang_id = ? AND status = 'disetujui' AND id <> ? LIMIT 1"
            );
            $approvedClaim->execute([(int)$klaim['barang_id'], $id]);

            if ($status === 'disetujui' && $approvedClaim->fetchColumn()) {
                $pdo->rollBack();
                setFlash('danger', 'Barang ini sudah memiliki klaim yang disetujui.');
                legacyRedirect('Location: ' . url('admin/klaim'));
                exit;
            }

            if (!$this->klaimModel->updateStatus($id, $status, $catatanAdmin)) {
                throw new RuntimeException('Status klaim gagal diperbarui.');
            }

            if ($status === 'disetujui') {
                $rejectOtherClaims = $pdo->prepare(
                    "UPDATE klaim SET status = 'ditolak', catatan_admin = 'Klaim lain telah disetujui untuk barang ini.' WHERE barang_id = ? AND id <> ? AND status = 'menunggu'"
                );
                $rejectOtherClaims->execute([(int)$klaim['barang_id'], $id]);

                if (!$this->barangModel->updateStatus((int)$klaim['barang_id'], 'diklaim')) {
                    throw new RuntimeException('Status barang gagal diperbarui.');
                }
            } elseif (
                $status === 'menunggu' &&
                $klaim['status'] === 'ditolak' &&
                $itemStatus === 'tersedia'
            ) {
                $this->barangModel->updateStatus((int)$klaim['barang_id'], 'menunggu_klaim');
            } elseif ($status === 'ditolak' && $itemStatus === 'menunggu_klaim') {
                $activeClaims = $pdo->prepare(
                    "SELECT COUNT(*) FROM klaim WHERE barang_id = ? AND status IN ('menunggu', 'disetujui')"
                );
                $activeClaims->execute([(int)$klaim['barang_id']]);

                if ((int)$activeClaims->fetchColumn() === 0) {
                    $this->barangModel->updateStatus((int)$klaim['barang_id'], 'tersedia');
                }
            }

            $pdo->commit();

            $this->aktivitasModel->recordSafe([
                'user_id' => (int)legacyAuth()['id'],
                'target_user_id' => $klaim['user_id'] ?? null,
                'barang_id' => $klaim['barang_id'] ?? null,
                'klaim_id' => $id,
                'aktivitas' => 'admin_memverifikasi_klaim',
                'deskripsi' => 'Admin mengubah status klaim barang "' . ($klaim['nama_barang'] ?? '-') . '" menjadi ' . $status . '.',
                'metadata' => ['actor_role' => 'admin', 'status_baru' => $status, 'catatan_admin' => $catatanAdmin]
            ]);

            setFlash(
                $status === 'ditolak' ? 'info' : 'success',
                $status === 'disetujui'
                    ? 'Klaim disetujui! Barang kini berstatus Diklaim.'
                    : ($status === 'ditolak' ? 'Klaim telah ditolak.' : 'Status klaim kembali menunggu.')
            );
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            setFlash('danger', 'Status klaim gagal diperbarui: ' . $e->getMessage());
        }

        legacyRedirect('Location: ' . url('admin/klaim'));
        exit;
    }

    /**
     * Admin Pengembalian & Riwayat Pengembalian
     */
    public function pengembalian(): void {
        // Ambil klaim yang statusnya disetujui dan barangnya belum dikembalikan
        $allKlaimDisetujui = $this->klaimModel->getAll(['status' => 'disetujui']);
        $klaimSiapSerahTerima = array_filter($allKlaimDisetujui, function($k) {
            return $k['barang_status'] !== 'dikembalikan';
        });

        // Riwayat pengembalian yang sudah tercatat
        $riwayatPengembalian = $this->pengembalianModel->getAll();

        renderAdminView('admin/pengembalian', [
            'title'                => 'Serah Terima & Riwayat Pengembalian - Admin',
            'klaimSiap'            => $klaimSiapSerahTerima,
            'riwayatPengembalian'  => $riwayatPengembalian
        ]);
    }

    /**
     * Proses Serah Terima Pengembalian
     */
    public function prosesPengembalian(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $klaimId = (int)($_POST['klaim_id'] ?? 0);
            $catatan = trim($_POST['catatan'] ?? '');

            $klaim = $this->klaimModel->getById($klaimId);
            if (!$klaim || $klaim['status'] !== 'disetujui') {
                setFlash('danger', 'Data klaim tidak valid atau belum disetujui.');
                legacyRedirect('Location: ' . url('admin/pengembalian'));
                exit;
            }

            if ($this->pengembalianModel->existsByKlaimId($klaimId)) {
                setFlash('warning', 'Klaim ini sudah memiliki catatan pengembalian.');
                legacyRedirect('Location: ' . url('admin/pengembalian'));
                exit;
            }

            try {
                // Buat record pengembalian
                $this->pengembalianModel->create([
                    'barang_id'            => $klaim['barang_id'],
                    'klaim_id'             => $klaimId,
                    'admin_id'             => (int)legacyAuth()['id'],
                    'tanggal_dikembalikan' => date('Y-m-d H:i:s'),
                    'catatan'              => $catatan ?: 'Barang telah diserahkan langsung kepada pemilik yang sah.'
                ]);

                $this->aktivitasModel->record([
                    'user_id' => (int)legacyAuth()['id'],
                    'target_user_id' => $klaim['user_id'] ?? null,
                    'barang_id' => $klaim['barang_id'] ?? null,
                    'klaim_id' => $klaimId,
                    'aktivitas' => 'serah_terima_barang',
                    'deskripsi' => 'Admin mencatat serah terima barang "' . ($klaim['nama_barang'] ?? '-') . '" kepada ' . ($klaim['pengklaim_nama'] ?? 'pemilik') . '.',
                    'metadata' => ['actor_role' => 'admin', 'catatan' => $catatan]
                ]);

                // Ubah status barang menjadi 'dikembalikan'
                $this->barangModel->updateStatus($klaim['barang_id'], 'dikembalikan');

                setFlash('success', 'Serah terima barang berhasil dicatat! Status barang kini "Dikembalikan".');
            } catch (Exception $e) {
                setFlash('danger', 'Gagal memproses pengembalian: ' . $e->getMessage());
            }
        }
        legacyRedirect('Location: ' . url('admin/pengembalian'));
        exit;
    }
}
