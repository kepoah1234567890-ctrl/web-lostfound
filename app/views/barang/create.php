<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom p-4 p-md-5 border-0">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="stat-icon-wrapper" style="background-color: var(--pastel-green); color: #1e5a32;">
                        <i class="bi bi-box2-heart fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold text-navy mb-0">Tambah Data Barang Ditemukan</h3>
                        <p class="text-muted small mb-0">Publikasikan barang yang Anda temukan di lingkungan <?= SCHOOL_NAME ?>.</p>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger border-0 rounded-3 small py-2 px-3 mb-4">
                        <i class="bi bi-exclamation-circle-fill me-2"></i> <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= url('barang/tambah') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nama_barang" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" value="<?= e($old['nama_barang'] ?? '') ?>" placeholder="Contoh: Tas Ransel Hitam Eiger" required>
                        </div>

                        <div class="col-md-4">
                            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kategori" name="kategori" list="kategoriList" value="<?= e($old['kategori'] ?? '') ?>" placeholder="Pilih atau ketik kategori" required>
                            <datalist id="kategoriList">
                                <option value="Tas">
                                <option value="Botol Minum">
                                <option value="Pakaian / Jaket">
                                <option value="Alat Tulis">
                                <option value="Kunci">
                                <option value="Elektronik">
                                <option value="Dompet">
                                <option value="Aksesoris">
                                <option value="Buku / Dokumen">
                            </datalist>
                        </div>

                        <div class="col-md-6">
                            <label for="warna" class="form-label">Warna Utama</label>
                            <input type="text" class="form-control" id="warna" name="warna" value="<?= e($old['warna'] ?? '') ?>" placeholder="Contoh: Hitam, Biru Navy">
                        </div>

                        <div class="col-md-6">
                            <label for="tanggal_ditemukan" class="form-label">Tanggal Ditemukan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_ditemukan" name="tanggal_ditemukan" value="<?= e($old['tanggal_ditemukan'] ?? date('Y-m-d')) ?>" required>
                        </div>

                        <div class="col-12">
                            <label for="lokasi_ditemukan" class="form-label">Lokasi Ditemukan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lokasi_ditemukan" name="lokasi_ditemukan" value="<?= e($old['lokasi_ditemukan'] ?? '') ?>" placeholder="Contoh: Lab Komputer RPL 2 Meja Belakang" required>
                        </div>

                        <div class="col-12">
                            <label for="deskripsi" class="form-label">Deskripsi & Kondisi Barang</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Tuliskan ciri khusus, merk, kelengkapan, atau kondisi barang saat ditemukan..."><?= e($old['deskripsi'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label for="foto" class="form-label">Upload Foto Barang (Opsional)</label>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" data-preview="preview-box">
                            <small class="text-muted d-block mt-1">Format didukung: JPG, JPEG, PNG, WEBP. Maksimal ukuran: 2MB.</small>
                            
                            <div class="image-preview-container mt-2" id="preview-box" style="display: none;"></div>
                        </div>

                        <div class="col-12 mt-4 d-flex gap-2 justify-content-end">
                            <a href="<?= url('barang') ?>" class="btn btn-outline-secondary btn-rounded px-4">Batal</a>
                            <button type="submit" class="btn btn-navy btn-rounded px-4 fw-semibold">
                                <i class="bi bi-check-lg me-1"></i> Simpan Barang
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
