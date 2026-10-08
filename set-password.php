<?php

require_once __DIR__ . '/legacy-config/config.php';
require_once APP_PATH . '/models/User.php';

if (!isLoggedIn()) {
    legacyRedirect(
        'Location: ' . url('login')
    );
    exit;
}

$userId =
    (int)$_SESSION['user']['id'];

$userModel = new User();

$user =
    $userModel->findById(
        $userId
    );

if (!$user) {
    session_destroy();

    legacyRedirect(
        'Location: ' . url('login')
    );

    exit;
}

$error = '';
$success = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && !isValidCsrfToken($_POST['_csrf_token'] ?? null)
) {
    $error = 'Permintaan tidak valid. Silakan muat ulang halaman.';
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isValidCsrfToken($_POST['_csrf_token'] ?? null)
) {
    $password =
        $_POST['password'] ?? '';

    $confirm =
        $_POST['password_confirmation']
        ?? '';

    if (strlen($password) < 6) {

        $error =
            'Password minimal 6 karakter.';

    } elseif ($password !== $confirm) {

        $error =
            'Konfirmasi password tidak sama.';

    } else {

        try {

            $userModel->setPassword(
                $userId,
                $password
            );

            /*
             * Refresh session user
             */
            $updatedUser =
                $userModel->findById(
                    $userId
                );

            $_SESSION['user'] = [
                'id' =>
                    (int)$updatedUser['id'],

                'nama' =>
                    $updatedUser['nama'],

                'nis' =>
                    $updatedUser['nis'],

                'email' =>
                    $updatedUser['email'],

                'kelas' =>
                    $updatedUser['kelas'],

                'role' =>
                    $updatedUser['role'],
            ];

            setFlash(
                'success',
                'Password berhasil dibuat.'
            );

            if (
                $updatedUser['role']
                === 'admin'
            ) {

                legacyRedirect(
                    'Location: '
                    . url('admin')
                );

            } else {

                header(
                    'Location: '
                    . url('')
                );
            }

            exit;

        } catch (Throwable $e) {

            $error =
                'Gagal menyimpan password.';
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

    <title>Buat Password - Lost & Found</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body
    style="
        background:#f8fafc;
        min-height:100vh;
        display:flex;
        align-items:center;
        justify-content:center;
    "
>

<div
    class="card shadow-sm border-0"
    style="
        width:100%;
        max-width:460px;
        border-radius:20px;
    "
>

    <div class="card-body p-4 p-md-5">

        <div class="text-center mb-4">

            <div
                style="
                    width:64px;
                    height:64px;
                    margin:auto;
                    margin-bottom:15px;
                    border-radius:18px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    background:#dbeafe;
                    color:#172554;
                    font-size:28px;
                "
            >
                🔐
            </div>

            <h3 class="fw-bold">
                Buat Password
            </h3>

            <p class="text-muted mb-0">
                Akun Google kamu berhasil dibuat.
            </p>

            <small class="text-muted">
                <?= e($user['email']) ?>
            </small>

        </div>

        <?php if ($error): ?>

            <div class="alert alert-danger">
                <?= e($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST">
            <?= csrfField() ?>

            <div class="mb-3">

                <label class="form-label">
                    Password Baru
                </label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    minlength="6"
                    required
                >

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Konfirmasi Password
                </label>

                <input
                    type="password"
                    name="password_confirmation"
                    class="form-control"
                    minlength="6"
                    required
                >

            </div>

            <button
                type="submit"
                class="btn btn-primary w-100 py-2"
            >
                Simpan Password
            </button>

        </form>

    </div>

</div>

</body>
</html>
