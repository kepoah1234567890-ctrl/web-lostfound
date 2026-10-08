<?php

require_once __DIR__ . '/legacy-config/config.php';
require_once APP_PATH . '/models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

/*
|--------------------------------------------------------------------------
| CEK TOKEN
|--------------------------------------------------------------------------
*/

$rawToken = trim(
    (string)($_GET['token'] ?? '')
);

/*
 * Kalau token tidak ada,
 * jangan izinkan membuka halaman reset.
 */
if ($rawToken === '') {

    $error =
        'Link reset password tidak valid.';

} else {

    /*
     * Ambil data reset dari session
     */
    $resetData =
        $_SESSION['password_reset'] ?? null;

    if (!is_array($resetData)) {

        $error =
            'Sesi reset password tidak ditemukan. '
            . 'Silakan mulai dari halaman Lupa Password.';

    } else {

        /*
         * Cek waktu expired
         */
        if (
            empty($resetData['expires'])
            || time() > (int)$resetData['expires']
        ) {

            unset(
                $_SESSION['password_reset']
            );

            $error =
                'Sesi reset password sudah kedaluwarsa. '
                . 'Silakan ulangi proses Lupa Password.';

        } else {

            /*
             * Cocokkan token
             */
            $tokenHash = hash(
                'sha256',
                $rawToken
            );

            if (
                empty($resetData['token'])
                || !hash_equals(
                    (string)$resetData['token'],
                    $tokenHash
                )
            ) {

                $error =
                    'Link reset password tidak valid.';

            } else {

                /*
                 * Token valid.
                 *
                 * Buat CSRF token.
                 */
                if (
                    empty(
                        $_SESSION[
                            'reset_password_csrf'
                        ]
                    )
                ) {

                    $_SESSION[
                        'reset_password_csrf'
                    ] = bin2hex(
                        random_bytes(32)
                    );
                }

                $csrfToken =
                    $_SESSION[
                        'reset_password_csrf'
                    ];

                /*
                 |--------------------------------------------------------------------------
                 | PROSES PASSWORD BARU
                 |--------------------------------------------------------------------------
                 */

                if (
                    $_SERVER['REQUEST_METHOD']
                    === 'POST'
                ) {

                    $postedCsrf =
                        $_POST['csrf_token']
                        ?? '';

                    /*
                     * CSRF
                     */
                    if (
                        $postedCsrf === ''
                        || !hash_equals(
                            $csrfToken,
                            $postedCsrf
                        )
                    ) {

                        $error =
                            'Permintaan tidak valid. '
                            . 'Silakan muat ulang halaman.';

                    } else {

                        $password =
                            (string)(
                                $_POST['password']
                                ?? ''
                            );

                        $passwordConfirmation =
                            (string)(
                                $_POST[
                                    'password_confirmation'
                                ]
                                ?? ''
                            );

                        /*
                         * Validasi password
                         */
                        if (
                            strlen($password) < 8
                        ) {

                            $error =
                                'Password minimal 8 karakter.';

                        } elseif (
                            $password
                            !==
                            $passwordConfirmation
                        ) {

                            $error =
                                'Konfirmasi password tidak sama.';

                        } else {

                            try {

                                $userModel =
                                    new User();

                                $userId =
                                    (int)(
                                        $resetData[
                                            'user_id'
                                        ]
                                    );

                                $user =
                                    $userModel->findById(
                                        $userId
                                    );

                                if (!$user) {

                                    $error =
                                        'Akun tidak ditemukan.';

                                } else {

                                    /*
                                     * Simpan password baru
                                     */
                                    $updated =
                                        $userModel
                                        ->resetPassword(
                                            $userId,
                                            $password
                                        );

                                    if (!$updated) {

                                        $error =
                                            'Password gagal diperbarui.';

                                    } else {

                                        /*
                                         * Hapus seluruh data
                                         * reset agar token sekali pakai.
                                         */
                                        unset(
                                            $_SESSION[
                                                'password_reset'
                                            ]
                                        );

                                        unset(
                                            $_SESSION[
                                                'reset_password_csrf'
                                            ]
                                        );

                                        /*
                                         * Hapus rate limit lupa password.
                                         */
                                        unset(
                                            $_SESSION[
                                                'forgot_attempts'
                                            ]
                                        );

                                        /*
                                         * Invalidasi login session lama.
                                         *
                                         * Session user dihapus supaya
                                         * harus login lagi menggunakan
                                         * password baru.
                                         */
                                        unset(
                                            $_SESSION['user']
                                        );

                                        /*
                                         * Buat session ID baru.
                                         */
                                        session_regenerate_id(
                                            true
                                        );

                                        /*
                                         * Flash message
                                         */
                                        setFlash(
                                            'success',
                                            'Password berhasil diubah. Silakan login menggunakan password baru.'
                                        );

                                        /*
                                         * Kembali ke login
                                         */
                                        legacyRedirect(
                                            'Location: '
                                            . url('login')
                                        );

                                        exit;
                                    }
                                }

                            } catch (Throwable $e) {

                                error_log(
                                    'Reset password error: '
                                    . $e->getMessage()
                                );

                                $error =
                                    'Terjadi kesalahan saat '
                                    . 'mengubah password.';
                            }
                        }
                    }
                }
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| CSRF FALLBACK
|--------------------------------------------------------------------------
*/

$csrfToken =
    $_SESSION[
        'reset_password_csrf'
    ] ?? '';

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
        Reset Password - <?= e(APP_NAME) ?>
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

            border: 1px solid #e5e7eb;

            box-shadow:
                0 20px 60px
                rgba(15, 23, 42, .10);
        }

        .icon-box {
            width: 70px;
            height: 70px;

            margin: 0 auto 20px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 20px;

            background: #dcfce7;

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

            color: #fff;

            font-weight: 600;
        }

        .btn-main:hover {
            background: #1e3a8a;

            color: #fff;
        }

        .requirements {
            background: #f8fafc;

            border-radius: 12px;

            padding: 12px 15px;

            font-size: 13px;

            color: #64748b;
        }

    </style>

</head>

<body>

<div class="reset-card">

    <div class="p-4 p-md-5">

        <div class="text-center mb-4">

            <div class="icon-box">
                🔑
            </div>

            <h3 class="fw-bold mb-2">
                Buat Password Baru
            </h3>

            <p class="text-muted mb-0">
                Masukkan password baru
                untuk akun kamu.
            </p>

        </div>

        <?php if ($error): ?>

            <div class="alert alert-danger border-0 rounded-3">

                <?= e($error) ?>

            </div>

        <?php else: ?>

            <form
                method="POST"
                autocomplete="off"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($csrfToken) ?>"
                >

                <div class="mb-3">

                    <label
                        for="password"
                        class="form-label fw-semibold"
                    >
                        Password Baru
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Minimal 8 karakter"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                </div>

                <div class="mb-4">

                    <label
                        for="password_confirmation"
                        class="form-label fw-semibold"
                    >
                        Konfirmasi Password
                    </label>

                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control"
                        placeholder="Ulangi password baru"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                </div>

                <div class="requirements mb-4">

                    🔒 Password minimal
                    <strong>8 karakter</strong>.

                    <br>

                    Pastikan password baru
                    berbeda dari password lama.

                </div>

                <button
                    type="submit"
                    class="btn btn-main w-100"
                >
                    Simpan Password Baru
                </button>

            </form>

        <?php endif; ?>

        <div class="text-center mt-4">

            <a
                href="<?= url('login') ?>"
                class="text-decoration-none fw-semibold"
            >
                ← Kembali ke Login
            </a>

        </div>

    </div>

</div>

</body>

</html>