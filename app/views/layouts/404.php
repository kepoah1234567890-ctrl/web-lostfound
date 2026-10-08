<div class="container py-5 my-3">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 text-center">
            <div class="card card-custom p-4 p-md-5 border-0 shadow-sm">
                <div class="stat-icon-wrapper mx-auto mb-3" style="background-color: var(--pastel-yellow); color: #6a530f; width: 72px; height: 72px; font-size: 2rem;">
                    <i class="bi bi-compass"></i>
                </div>
                
                <span class="badge badge-pastel-purple mb-2 mx-auto">Error 404</span>
                <h3 class="fw-bold text-navy mb-2">Halaman Tidak Ditemukan</h3>
                <p class="text-muted small mx-auto mb-4" style="max-width: 420px; line-height: 1.6;">
                    Halaman atau tautan yang Anda tuju tidak ditemukan atau telah dipindahkan ke alamat lain.
                </p>

                <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                    <a href="<?= url('') ?>" class="btn btn-navy btn-rounded px-4 py-2 fw-semibold">
                        <i class="bi bi-house-door me-1"></i> Ke Beranda
                    </a>
                    <a href="<?= url('barang') ?>" class="btn btn-pastel-blue btn-rounded px-4 py-2 fw-semibold">
                        <i class="bi bi-box2-heart me-1"></i> Cari Barang
                    </a>
                </div>

                <div class="border-top pt-3 small text-muted">
                    Butuh bantuan? Silakan hubungi <strong>Pusat Kesiswaan <?= SCHOOL_NAME ?></strong>.
                </div>
            </div>
        </div>
    </div>
</div>
