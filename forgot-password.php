<?php

require_once __DIR__ . '/legacy-config/config.php';
require_once APP_PATH . '/models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['forgot_password_csrf'])) {
    $_SESSION['forgot_password_csrf'] = bin2hex(
        random_bytes(32)
    );
}

$csrfToken = $_SESSION['forgot_password_csrf'];

$error = '';
$email = '';

/*
|--------------------------------------------------------------------------
| RATE LIMIT SEDERHANA
|--------------------------------------------------------------------------
| Maksimal 5 percobaan dalam 15 menit per session.
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['forgot_attempts'])) {
    $_SESSION['forgot_attempts'] = 0;
    $_SESSION['forgot_started_at'] = time();
}

if (
    time() - (int)$_SESSION['forgot_started_at']
    >= 15 * 60
) {
    $_SESSION['forgot_attempts'] = 0;
    $_SESSION['forgot_started_at'] = time();
}

/*
|--------------------------------------------------------------------------
| PROSES FORM
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
     * Cek CSRF
     */
    $postedCsrf = $_POST['csrf_token'] ?? '';

    if (
        $postedCsrf === ''
        || !hash_equals(
            $csrfToken,
            $postedCsrf
        )
    ) {
        $error = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    }

    /*
     * Rate limit
     */
    elseif ($_SESSION['forgot_attempts'] >= 5) {

        $error =
            'Terlalu banyak percobaan. '
            . 'Silakan coba lagi dalam 15 menit.';
    }

    else {

        $_SESSION['forgot_attempts']++;

        $email = strtolower(
            trim(
                $_POST['email'] ?? ''
            )
        );

        /*
         * Validasi email
         */
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = 'Masukkan alamat email yang valid.';

        } else {

            try {

                $userModel = new User();

                $user = $userModel->findByEmail($email);

                if (!$user) {

                    /*
                     * Jangan beri informasi terlalu detail
                     * tentang apakah akun ada atau tidak.
                     */
                    $error =
                        'Email tidak ditemukan. '
                        . 'Pastikan email yang dimasukkan benar.';

                } else {

                    /*
                     * Buat token reset khusus session.
                     *
                     * Token tidak dikirim lewat URL.
                     * Token disimpan di session.
                     */
                    $resetToken = bin2hex(
                        random_bytes(32)
                    );

                    $_SESSION['password_reset'] = [
                        'token' => hash(
                            'sha256',
                            $resetToken
                        ),
                        'user_id' => (int)$user['id'],
                        'email' => $user['email'],
                        'expires' => time() + (10 * 60),
                    ];

                    /*
                     * Regenerasi session ID
                     */
                    session_regenerate_id(true);

                    /*
                     * Langsung masuk ke halaman reset password.
                     */
                    legacyRedirect(
                        'Location: ' . url(
                            'reset-password.php?token='
                            . urlencode($resetToken)
                        )
                    );

                    exit;
                }

            } catch (Throwable $e) {

                error_log(
                    'Forgot password error: '
                    . $e->getMessage()
                );

                $error =
                    'Terjadi kesalahan. '
                    . 'Silakan coba lagi.';
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Lupa Password - <?= e(APP_NAME) ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            background:
                linear-gradient(
                    135deg,
                    #f8fafc,
                    #eef4ff
                );

            font-family:
                Arial,
                Helvetica,
                sans-serif;
        }

        .reset-card {
            width: 100%;
            max-width: 450px;

            background: #ffffff;

            border-radius: 24px;

            box-shadow:
                0 20px 60px
                rgba(15, 23, 42, .10);

            border: 1px solid #e5e7eb;
        }

        .icon-box {
            width: 70px;
            height: 70px;

            margin: 0 auto 20px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 20px;

            background: #dbeafe;

            font-size: 30px;
        }

        .form-control {
            min-height: 52px;

            border-radius: 12px;

            border: 1px solid #dbe3ef;
        }

        .form-control:focus {
            border-color: #2563eb;

            box-shadow:
                0 0 0 4px
                rgba(37, 99, 235, .10);
        }

        .btn-main {
            min-height: 52px;

            border: 0;

            border-radius: 12px;

            background: #172554;

            color: #ffffff;

            font-weight: 600;
        }

        .btn-main:hover {
            background: #1e3a8a;

            color: #ffffff;
        }

        .small-info {
            color: #64748b;

            font-size: 13px;

            line-height: 1.6;
        }

    </style>

</head>

<body>

<div class="reset-card">

    <div class="p-4 p-md-5">

        <div class="text-center mb-4">

            <div class="icon-box">
                🔐
            </div>

            <h3 class="fw-bold mb-2">
                Lupa Password?
            </h3>

            <p class="text-muted mb-0">
                Masukkan email akun kamu
                untuk membuat password baru.
            </p>

        </div>

        <?php if ($error): ?>

            <div class="alert alert-danger border-0 rounded-3">
                <i class="bi bi-exclamation-circle me-2"></i>

                <?= e($error) ?>
            </div>

        <?php endif; ?>

        <form
            method="POST"
            autocomplete="off"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($csrfToken) ?>"
            >

            <div class="mb-4">

                <label
                    for="email"
                    class="form-label fw-semibold"
                >
                    Email Akun
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="nama@gmail.com"
                    value="<?= e($email) ?>"
                    autocomplete="email"
                    required
                    autofocus
                >

            </div>

            <button
                type="submit"
                class="btn btn-main w-100"
            >
                Lanjut Reset Password
            </button>

        </form>

        <div class="text-center mt-4">

            <a
                href="<?= url('login') ?>"
                class="text-decoration-none fw-semibold"
            >
                ← Kembali ke Login
            </a>

        </div>

        <div class="text-center mt-4">

            <p class="small-info mb-0">
                Link reset hanya berlaku
                selama 10 menit dan hanya dapat
                digunakan satu kali.
            </p>

        </div>

    </div>

</div>

</body>

</html>