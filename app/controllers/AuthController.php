
<?php
/**
 * AuthController
 * Lost & Found SMK Informatika Sumedang
 */

require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Aktivitas.php';

class AuthController
{
    private User $userModel;
    private Aktivitas $aktivitasModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->aktivitasModel = new Aktivitas();
    }

    public function login(): void
    {
        if (isLoggedIn()) {
            legacyRedirect('Location: ' . (isAdmin() ? url('admin') : url('')));
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = 'Email dan kata sandi wajib diisi.';
            } else {
                $user = $this->userModel->findByEmail($email);

                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);

                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'nama' => $user['nama'],
                        'nis' => $user['nis'],
                        'email' => $user['email'],
                        'no_telepon' => $user['no_telepon'] ?? '',
                        'kelas' => $user['kelas'],
                        'role' => $user['role'],
                    ];

                    $this->aktivitasModel->recordSafe([
                        'user_id' => (int)$user['id'],
                        'aktivitas' => 'login',
                        'deskripsi' => 'Berhasil masuk melalui website.',
                        'metadata' => ['channel' => 'web'],
                    ]);

                    setFlash(
                        'success',
                        'Selamat datang kembali, ' . $user['nama'] . '!'
                    );

                    if ($user['role'] === 'admin') {
                        legacyRedirect('Location: ' . url('admin'));
                    } else {
                        legacyRedirect('Location: ' . url(''));
                    }

                    exit;
                } else {
                    $error = 'Kombinasi email atau kata sandi tidak sesuai.';
                }
            }
        }

        renderView('auth/login', [
            'title' => 'Masuk - Lost & Found SMK Informatika Sumedang',
            'error' => $error,
            'oldEmail' => $_POST['email'] ?? '',
        ]);
    }

    public function register(): void
    {
        if (isLoggedIn()) {
            legacyRedirect('Location: ' . url(''));
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama = trim($_POST['nama'] ?? '');
            $nis = trim($_POST['nis'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $no_telepon = trim($_POST['no_telepon'] ?? '');
            $password = $_POST['password'] ?? '';
            $kelas = trim($_POST['kelas'] ?? '');

            /*
             * Validasi input wajib
             */
            if (
                empty($nama) ||
                empty($nis) ||
                empty($email) ||
                empty($no_telepon) ||
                empty($password) ||
                empty($kelas)
            ) {
                $error = 'Semua data (Nama, NIS, Email, Nomor Telepon, Kelas, dan Password) wajib diisi.';
            }

            /*
             * Validasi email
             */
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Format email tidak valid.';
            }

            /*
             * Validasi nomor telepon:
             * harus tepat 12 digit dan hanya angka
             */
            elseif (!preg_match('/^\d{12}$/', $no_telepon)) {
                $error = 'Nomor telepon harus tepat 12 digit angka. Contoh: 081234567890.';
            }

            /*
             * Validasi password
             */
            elseif (strlen($password) < 6) {
                $error = 'Kata sandi minimal 6 karakter.';
            }

            /*
             * Cek NIS
             */
            elseif ($this->userModel->findByNis($nis)) {
                $error = 'NIS ' . htmlspecialchars($nis) . ' sudah terdaftar.';
            }

            /*
             * Cek email
             */
            elseif ($this->userModel->findByEmail($email)) {
                $error = 'Email ' . htmlspecialchars($email) . ' sudah terdaftar.';
            }

            /*
             * Simpan akun
             */
            else {
                try {
                    $userId = $this->userModel->create([
                        'nama' => $nama,
                        'nis' => $nis,
                        'email' => $email,
                        'no_telepon' => $no_telepon,
                        'password' => $password,
                        'kelas' => $kelas,
                        'role' => 'siswa',
                    ]);

                    /*
                     * Otomatis login setelah register
                     */
                    $_SESSION['user'] = [
                        'id' => $userId,
                        'nama' => $nama,
                        'nis' => $nis,
                        'email' => $email,
                        'no_telepon' => $no_telepon,
                        'kelas' => $kelas,
                        'role' => 'siswa',
                    ];

                    $this->aktivitasModel->recordSafe([
                        'user_id' => $userId,
                        'aktivitas' => 'register',
                        'deskripsi' => 'Membuat akun melalui website.',
                        'metadata' => ['channel' => 'web'],
                    ]);

                    setFlash(
                        'success',
                        'Pendaftaran akun berhasil! Selamat datang, ' . $nama . '.'
                    );

                    legacyRedirect('Location: ' . url(''));
                    exit;
                } catch (Exception $e) {
                    $error = 'Gagal mendaftarkan akun: ' . $e->getMessage();
                }
            }
        }

        renderView('auth/register', [
            'title' => 'Daftar Akun Siswa - Lost & Found',
            'error' => $error,
            'old' => $_POST,
        ]);
    }

    public function profile(): void
    {
        requireLogin();

        $userId = (int) legacyAuth()['id'];
        $user = $this->userModel->findById($userId);
        $error = null;

        if (!$user) {
            unset($_SESSION['user']);
            session_destroy();
            session_start();
            setFlash('danger', 'Data akun tidak ditemukan.');
            legacyRedirect('Location: ' . url('login'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $noTelepon = trim($_POST['no_telepon'] ?? '');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Format email tidak valid.';
            }

            /*
             * Profile juga wajib 12 digit
             */
            elseif (!preg_match('/^\d{12}$/', $noTelepon)) {
                $error = 'Nomor telepon harus tepat 12 digit angka. Contoh: 081234567890.';
            }

            elseif ($this->userModel->findByEmailExceptId($email, $userId)) {
                $error = 'Email tersebut sudah digunakan akun lain.';
            }

            else {
                try {
                    $this->userModel->updateContact(
                        $userId,
                        $email,
                        $noTelepon
                    );

                    $_SESSION['user']['email'] = $email;
                    $_SESSION['user']['no_telepon'] = $noTelepon;

                    $this->aktivitasModel->recordSafe([
                        'user_id' => $userId,
                        'aktivitas' => 'edit_profil',
                        'deskripsi' => 'Memperbarui email atau nomor telepon melalui website.',
                        'metadata' => ['channel' => 'web'],
                    ]);

                    setFlash(
                        'success',
                        'Email dan nomor telepon berhasil diperbarui. NIS tetap tidak dapat diubah.'
                    );

                    legacyRedirect('Location: ' . url('profil'));
                    exit;
                } catch (Throwable $e) {
                    $error = 'Gagal memperbarui profil: ' . $e->getMessage();
                }
            }

            $user['email'] = $email;
            $user['no_telepon'] = $noTelepon;
        }

        renderView('auth/profile', [
            'title' => 'Profil Saya - Lost & Found SMK Informatika Sumedang',
            'user' => $user,
            'error' => $error,
        ]);
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            legacyRedirect('Location: ' . url(''));
            exit;
        }

        if (isLoggedIn()) {
            $this->aktivitasModel->recordSafe([
                'user_id' => (int)legacyAuth()['id'],
                'aktivitas' => 'logout',
                'deskripsi' => 'Keluar dari website.',
                'metadata' => ['channel' => 'web'],
            ]);
        }

        unset($_SESSION['user']);

        session_destroy();
        session_start();

        setFlash(
            'info',
            'Anda telah berhasil keluar dari akun.'
        );

        legacyRedirect('Location: ' . url('login'));
        exit;
    }

    public function aktivitas(): void
    {
        requireLogin();

        $filters = ['user_id' => (int)legacyAuth()['id']];

        renderView('auth/aktivitas', [
            'title' => 'Aktivitas Saya - Lost & Found',
            'aktivitasList' => $this->aktivitasModel->getAll($filters, 200, 0),
            'totalAktivitas' => $this->aktivitasModel->count($filters),
        ]);
    }

    public function startImpersonation(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isValidCsrfToken($_POST['_csrf_token'] ?? null)) {
            http_response_code(403);
            setFlash('danger', 'Permintaan tidak valid. Silakan coba lagi.');
            legacyRedirect('Location: ' . url('admin/users'));
            exit;
        }

        $targetId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
        $target = $targetId && $targetId > 0 ? $this->userModel->findById((int)$targetId) : null;

        if (!$target || ($target['role'] ?? '') !== 'siswa') {
            setFlash('danger', 'Akun user tidak ditemukan.');
            legacyRedirect('Location: ' . url('admin/users'));
            exit;
        }

        $admin = $this->userModel->findById((int)legacyAuth()['id']);
        if (!$admin || ($admin['role'] ?? '') !== 'admin') {
            setFlash('danger', 'Sesi admin tidak lagi valid.');
            legacyRedirect('Location: ' . url('login'));
            exit;
        }

        unset($admin['password'], $target['password']);
        $this->aktivitasModel->recordSafe([
            'user_id' => (int)$admin['id'],
            'target_user_id' => (int)$target['id'],
            'aktivitas' => 'admin_masuk_sebagai_user',
            'deskripsi' => 'Admin membuka sesi sebagai user ' . $target['nama'] . '.',
            'metadata' => ['actor_role' => 'admin', 'channel' => 'web'],
        ]);

        session_regenerate_id(true);
        $_SESSION['impersonation'] = [
            'admin_user' => $admin,
            'target_user_id' => (int)$target['id'],
            'started_at' => date(DATE_ATOM),
        ];
        $_SESSION['user'] = $target;
        $_SESSION['user_id'] = (int)$target['id'];
        $_SESSION['user_role'] = 'siswa';
        $_SESSION['logged_in'] = true;

        legacyRedirect('Location: ' . url(''));
        exit;
    }

    public function stopImpersonation(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isImpersonating() || !isValidCsrfToken($_POST['_csrf_token'] ?? null)) {
            http_response_code(403);
            legacyRedirect('Location: ' . url(''));
            exit;
        }

        $target = legacyAuth();
        $admin = impersonationAdmin();
        $admin = $admin ? $this->userModel->findById((int)$admin['id']) : null;

        if (!$target || !$admin || ($admin['role'] ?? '') !== 'admin') {
            $_SESSION = [];
            session_destroy();
            legacyRedirect('Location: ' . url('login'));
            exit;
        }

        unset($admin['password']);
        $this->aktivitasModel->recordSafe([
            'user_id' => (int)$admin['id'],
            'target_user_id' => (int)$target['id'],
            'aktivitas' => 'admin_keluar_dari_user',
            'deskripsi' => 'Admin mengakhiri sesi sebagai user ' . ($target['nama'] ?? '') . '.',
            'metadata' => ['actor_role' => 'admin', 'channel' => 'web'],
        ]);

        session_regenerate_id(true);
        unset($_SESSION['impersonation']);
        $_SESSION['user'] = $admin;
        $_SESSION['user_id'] = (int)$admin['id'];
        $_SESSION['user_role'] = 'admin';
        $_SESSION['logged_in'] = true;

        setFlash('success', 'Kembali ke akun admin.');
        legacyRedirect('Location: ' . url('admin/users'));
        exit;
    }
}
