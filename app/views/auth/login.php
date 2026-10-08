<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            <div class="card card-custom p-4 p-md-5 border-0">
                <div class="text-center mb-4">
                    <div class="stat-icon-wrapper mx-auto mb-3" style="background-color: var(--pastel-blue); color: var(--navy); width: 60px; height: 60px;">
                        <i class="bi bi-box-arrow-in-right fs-3"></i>
                    </div>
                    <h3 class="fw-bold text-navy mb-1">Selamat Datang</h3>
                    <p class="text-muted small">Masuk ke akun Lost & Found <?= SCHOOL_NAME ?></p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger border-0 rounded-3 small py-2 px-3 mb-4">
                        <i class="bi bi-exclamation-circle-fill me-2"></i> <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= url('login') ?>" method="POST">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Alamat Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" value="<?= e($oldEmail ?? '') ?>" placeholder="contoh: nama.siswa@gmail.com" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Masukkan kata sandi" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-navy btn-rounded w-100 py-2 fw-semibold mb-3">
                        Masuk ke Akun
                    </button>

                    <div class="text-center mb-3">
                        <a href="<?= url('forgot-password') ?>" class="small text-decoration-none">
                            Lupa Password?
                        </a>
                    </div>

                    <div class="text-center small text-muted">
                        Belum punya akun siswa? <a href="<?= url('register') ?>" class="text-primary fw-semibold">Daftar Akun Baru</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
