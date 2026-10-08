<div class="container py-5">
  <div class="row justify-content-center"><div class="col-lg-5 col-md-8">
    <div class="card card-custom p-4 p-md-5 border-0">
      <div class="text-center mb-4"><div class="stat-icon-wrapper mx-auto mb-3" style="background-color:var(--pastel-yellow);color:var(--navy);width:60px;height:60px"><i class="bi bi-key fs-3"></i></div>
        <h3 class="fw-bold text-navy mb-1">Lupa Password?</h3><p class="text-muted small">Masukkan email yang terdaftar. Link reset akan dikirim ke email kamu.</p></div>
      <?php if (!empty($error)): ?><div class="alert alert-danger border-0 rounded-3 small"><?= e($error) ?></div><?php endif; ?>
      <?php if (!empty($message)): ?><div class="alert alert-success border-0 rounded-3 small"><?= e($message) ?></div><?php endif; ?>
      <form method="POST" action="<?= url('forgot-password') ?>">
        <label class="form-label">Alamat Email</label><div class="input-group mb-4"><span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span><input type="email" name="email" class="form-control border-start-0 ps-0" value="<?= e($oldEmail ?? '') ?>" required></div>
        <button class="btn btn-navy btn-rounded w-100 py-2 fw-semibold">Kirim Link Reset</button>
      </form>
      <div class="text-center small mt-4"><a href="<?= url('login') ?>">← Kembali ke Login</a></div>
    </div>
  </div></div>
</div>
