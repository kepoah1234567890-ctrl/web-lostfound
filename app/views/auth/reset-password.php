<div class="container py-5">
  <div class="row justify-content-center"><div class="col-lg-5 col-md-8">
    <div class="card card-custom p-4 p-md-5 border-0">
      <div class="text-center mb-4"><div class="stat-icon-wrapper mx-auto mb-3" style="background-color:var(--pastel-green);color:var(--navy);width:60px;height:60px"><i class="bi bi-shield-lock fs-3"></i></div>
        <h3 class="fw-bold text-navy mb-1">Buat Password Baru</h3><p class="text-muted small">Gunakan password baru untuk akun Lost &amp; Found kamu.</p></div>
      <?php if (!empty($error)): ?><div class="alert alert-danger border-0 rounded-3 small"><?= e($error) ?></div><?php endif; ?>
      <?php if (!empty($success)): ?><div class="alert alert-success border-0 rounded-3 small"><?= e($success) ?></div><a href="<?= url('login') ?>" class="btn btn-navy btn-rounded w-100 py-2 fw-semibold">Kembali ke Login</a>
      <?php else: ?>
      <form method="POST" action="<?= url('reset-password') ?>?token=<?= e($token ?? '') ?>">
        <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
        <label class="form-label">Password Baru</label><input type="password" name="password" class="form-control mb-3" minlength="6" required>
        <label class="form-label">Konfirmasi Password</label><input type="password" name="password_confirmation" class="form-control mb-4" minlength="6" required>
        <button class="btn btn-navy btn-rounded w-100 py-2 fw-semibold">Simpan Password</button>
      </form><?php endif; ?>
    </div>
  </div></div>
</div>
