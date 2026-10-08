
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card card-custom p-4 p-md-5 border-0">
                <div class="text-center mb-4">
                    <div class="stat-icon-wrapper mx-auto mb-3"
                        style="background-color: var(--pastel-green); color: #1e5a32; width: 60px; height: 60px;">
                        <i class="bi bi-person-plus fs-3"></i>
                    </div>

                    <h3 class="fw-bold text-navy mb-1">
                        Daftar Akun Siswa
                    </h3>

                    <p class="text-muted small">
                        Buat akun untuk melapor atau mengklaim barang di <?= SCHOOL_NAME ?>
                    </p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger border-0 rounded-3 small py-2 px-3 mb-4">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= url('register') ?>" method="POST" id="registerForm">
                    <?= csrfField() ?>

                    <div class="row g-3">

                        <!-- NAMA -->
                        <div class="col-12">
                            <label for="nama" class="form-label">
                                Nama Lengkap Siswa
                            </label>

                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person text-muted"></i>
                                </span>

                                <input
                                    type="text"
                                    class="form-control border-start-0 ps-0"
                                    id="nama"
                                    name="nama"
                                    value="<?= e($old['nama'] ?? '') ?>"
                                    placeholder="Contoh: Budi Santoso"
                                    required
                                    autofocus
                                >
                            </div>
                        </div>

                        <!-- NIS -->
                        <div class="col-md-6">
                            <label for="nis" class="form-label">
                                NIS (Nomor Induk Siswa)
                            </label>

                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-card-text text-muted"></i>
                                </span>

                                <input
                                    type="text"
                                    class="form-control border-start-0 ps-0"
                                    id="nis"
                                    name="nis"
                                    value="<?= e($old['nis'] ?? '') ?>"
                                    placeholder="Contoh: 22231001"
                                    required
                                >
                            </div>
                        </div>

                        <!-- KELAS -->
                        <div class="col-md-6">
                            <label for="kelas" class="form-label">
                                Kelas & Jurusan
                            </label>

                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-building text-muted"></i>
                                </span>

                                <input
                                    type="text"
                                    class="form-control border-start-0 ps-0"
                                    id="kelas"
                                    name="kelas"
                                    value="<?= e($old['kelas'] ?? '') ?>"
                                    placeholder="Contoh: XII RPL 1"
                                    required
                                >
                            </div>
                        </div>

                        <!-- EMAIL -->
                        <div class="col-12">
                            <label for="email" class="form-label">
                                Alamat Email (Gmail)
                            </label>

                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>

                                <input
                                    type="email"
                                    class="form-control border-start-0 ps-0"
                                    id="email"
                                    name="email"
                                    value="<?= e($old['email'] ?? '') ?>"
                                    placeholder="contoh: budi.santoso@gmail.com"
                                    required
                                >
                            </div>
                        </div>

                        <!-- NOMOR TELEPON -->
                        <div class="col-12">
                            <label for="no_telepon" class="form-label">
                                Nomor Telepon / WhatsApp
                            </label>

                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-telephone text-muted"></i>
                                </span>

                                <input
                                    type="tel"
                                    class="form-control border-start-0 ps-0"
                                    id="no_telepon"
                                    name="no_telepon"
                                    value="<?= e($old['no_telepon'] ?? '') ?>"
                                    placeholder="Contoh: 081234567890"
                                    inputmode="numeric"
                                    pattern="[0-9]{12}"
                                    minlength="12"
                                    maxlength="12"
                                    required
                                >
                            </div>

                            <small class="text-muted">
                                Nomor telepon harus tepat 12 digit angka.
                            </small>
                        </div>

                        <!-- PASSWORD -->
                        <div class="col-12">
                            <label for="password" class="form-label">
                                Kata Sandi (Password)
                            </label>

                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>

                                <input
                                    type="password"
                                    class="form-control border-start-0 ps-0"
                                    id="password"
                                    name="password"
                                    placeholder="Minimal 6 karakter"
                                    required
                                    minlength="6"
                                >
                            </div>
                        </div>

                        <!-- BUTTON -->
                        <div class="col-12 mt-4">
                            <button
                                type="submit"
                                class="btn btn-navy btn-rounded w-100 py-2 fw-semibold mb-3"
                            >
                                Daftarkan Akun Siswa
                            </button>
                        </div>

                    </div>

                    <div class="text-center small text-muted">
                        Sudah memiliki akun?
                        <a
                            href="<?= url('login') ?>"
                            class="text-primary fw-semibold"
                        >
                            Masuk di Sini
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registerForm');
    const phone = document.getElementById('no_telepon');

    phone.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 12);
    });

    form.addEventListener('submit', function (event) {
        const nomor = phone.value.trim();

        if (!/^\d{12}$/.test(nomor)) {
            event.preventDefault();

            alert('Nomor telepon harus tepat 12 digit angka.');

            phone.focus();
        }
    });
});
</script>
