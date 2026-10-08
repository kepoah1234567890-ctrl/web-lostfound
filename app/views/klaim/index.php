<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom p-4 p-md-5 border-0">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="stat-icon-wrapper" style="background-color: var(--pastel-purple); color: #472b69;">
                        <i class="bi bi-shield-lock-fill fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold text-navy mb-0">Ajukan Klaim Kepemilikan Barang</h3>
                        <p class="text-muted small mb-0">Isi bukti dan ciri spesifik untuk memvalidasi kepemilikan Anda.</p>
                    </div>
                </div>

                <!-- Info Box Barang yang Diklaim -->
                <div class="p-3 bg-light rounded-4 mb-4 d-flex align-items-center gap-3">
                    <img src="<?= uploadUrl($barang['foto']) ?>" alt="" style="width: 70px; height: 70px; object-fit: cover;" class="rounded-3 border">
                    <div>
                        <span class="badge badge-pastel-purple"><?= e($barang['kategori']) ?></span>
                        <h5 class="fw-bold text-navy mb-1 mt-1"><?= e($barang['nama_barang']) ?></h5>
                        <small class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Ditemukan di <?= e($barang['lokasi_ditemukan']) ?> pada <?= formatTanggalIndo($barang['tanggal_ditemukan']) ?></small>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger border-0 rounded-3 small py-2 px-3 mb-4">
                        <i class="bi bi-exclamation-circle-fill me-2"></i> <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= url('klaim') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <input type="hidden" name="barang_id" value="<?= $barang['id'] ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="ciri_barang" class="form-label">
                                Ciri-Ciri Spesifik / Rahasia Barang <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="ciri_barang" name="ciri_barang" rows="4" placeholder="Jelaskan secara spesifik ciri-ciri unik yang membuktikan bahwa barang ini milik Anda (misal: isi dompet, goresan tertentu, isi kotak pensil, wallpaper HP, gantungan kunci, dsb)..." required autofocus><?= e($old['ciri_barang'] ?? '') ?></textarea>
                            <small class="text-muted d-block mt-1">Ciri-ciri ini akan dicocokkan oleh petugas/admin kesiswaan.</small>
                        </div>

                        <div class="col-12">
                            <label for="bukti_kepemilikan" class="form-label">
                                Upload Foto Bukti Kepemilikan (Opsional)
                            </label>
                            <input type="file" class="form-control" id="bukti_kepemilikan" name="bukti_kepemilikan" accept="image/jpeg,image/png,image/webp" data-preview="preview-klaim">
                            <small class="text-muted d-block mt-1">Dapat berupa foto Anda bersama barang tersebut, struk/kuitansi, atau kotak kemasan (Maks 2MB).</small>
                            <div class="image-preview-container mt-2" id="preview-klaim" style="display: none;"></div>
                        </div>

                        <div class="col-12 mt-4 d-flex gap-2 justify-content-end">
                            <a href="<?= url('barang/detail?id=' . $barang['id']) ?>" class="btn btn-outline-secondary btn-rounded px-4">Batal</a>
                            <button type="submit" class="btn btn-pastel-green btn-rounded px-4 fw-bold">
                                <i class="bi bi-send-check me-1"></i> Ajukan Klaim
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
