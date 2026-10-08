</main>

<footer class="footer-custom mt-5">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-lg-5 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="brand-icon" style="width: 32px; height: 32px; font-size: 1rem;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <span class="fw-bold fs-5 text-navy">Lost & Found</span>
                </div>
                <p class="text-muted small mb-3">
                    Layanan resmi pengelolaan barang temuan dan kehilangan di lingkungan <strong><?= SCHOOL_NAME ?></strong>. Mewujudkan lingkungan sekolah yang jujur, peduli, dan amanah.
                </p>
                <div class="d-flex gap-2">
                    <span class="badge badge-pastel-blue"><i class="bi bi-shield-check me-1"></i> Terpercaya</span>
                    <span class="badge badge-pastel-green"><i class="bi bi-person-check me-1"></i> Siswa & Staf</span>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold mb-3 text-navy">Navigasi Cepat</h6>
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                    <li><a href="<?= url('') ?>" class="text-muted"><i class="bi bi-chevron-right me-1 text-primary"></i> Beranda</a></li>
                    <li><a href="<?= url('barang') ?>" class="text-muted"><i class="bi bi-chevron-right me-1 text-primary"></i> Barang Ditemukan</a></li>
                    <li><a href="<?= url('laporan') ?>" class="text-muted"><i class="bi bi-chevron-right me-1 text-primary"></i> Laporan Kehilangan</a></li>
                    <li><a href="<?= url('laporkan-hilang') ?>" class="text-muted"><i class="bi bi-chevron-right me-1 text-primary"></i> Lapor Barang Hilang</a></li>
                    <li><a href="<?= url('laporkan-ditemukan') ?>" class="text-muted"><i class="bi bi-chevron-right me-1 text-primary"></i> Lapor Barang Ditemukan</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-12">
                <h6 class="fw-bold mb-3 text-navy">Pusat Layanan Kesiswaan</h6>
                <p class="text-muted small mb-2">
                    <i class="bi bi-geo-alt-fill me-2 text-danger"></i> Ruang Kesiswaan & Tata Usaha SMK Informatika Sumedang
                </p>
                <p class="text-muted small mb-2">
                    <i class="bi bi-clock-fill me-2 text-warning"></i> Senin - Jumat: 07.00 - 15.30 WIB
                </p>
                <p class="text-muted small mb-0">
                    <i class="bi bi-envelope-fill me-2 text-info"></i> lostfound.smkinformatika@gmail.com
                </p>
            </div>
        </div>

        <div class="border-top pt-3 text-center text-muted small">
            &copy; <?= date('Y') ?> <strong>Lost & Found</strong> - <?= SCHOOL_NAME ?>. Hak Cipta Dilindungi.
        </div>
    </div>
</footer>

<!-- Bootstrap 5 Bundle JS (Popper included) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom App JS -->
<script src="<?= asset('js/app.js') ?>"></script>

<!-- Smooth Scroll + Scroll Reveal -->
<script src="<?= asset('js/scroll-reveal.js') ?>"></script>
</body>
</html>
