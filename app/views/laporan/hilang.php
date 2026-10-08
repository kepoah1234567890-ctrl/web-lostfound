<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom p-4 p-md-5 border-0">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="stat-icon-wrapper" style="background-color: var(--pastel-pink); color: #7a2339;">
                        <i class="bi bi-exclamation-octagon fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold text-navy mb-0">Laporkan Barang Hilang</h3>
                        <p class="text-muted small mb-0">Laporkan barang Anda yang hilang agar dapat dibantu pencariannya oleh warga sekolah.</p>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger border-0 rounded-3 small py-2 px-3 mb-4">
                        <i class="bi bi-exclamation-circle-fill me-2"></i> <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= url('laporkan-hilang') ?>" method="POST" enctype="multipart/form-data">
                <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nama_barang" class="form-label">Nama Barang yang Hilang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" value="<?= e($old['nama_barang'] ?? '') ?>" placeholder="Contoh: Dompet Kulit Coklat" required autofocus>
                        </div>

                        <div class="col-md-4">
                            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kategori" name="kategori" list="kategoriList" value="<?= e($old['kategori'] ?? '') ?>" placeholder="Pilih / ketik kategori" required>
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
                            <input type="text" class="form-control" id="warna" name="warna" value="<?= e($old['warna'] ?? '') ?>" placeholder="Contoh: Coklat tua">
                        </div>

                        <div class="col-md-6">
                            <label for="tanggal_hilang" class="form-label">Perkiraan Tanggal Hilang <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_hilang" name="tanggal_hilang" value="<?= e($old['tanggal_hilang'] ?? date('Y-m-d')) ?>" required>
                        </div>

                        <div class="col-12">
                            <label for="lokasi_terakhir" class="form-label">Lokasi Terakhir Diingat</label>
                            <input type="text" class="form-control" id="lokasi_terakhir" name="lokasi_terakhir" value="<?= e($old['lokasi_terakhir'] ?? '') ?>" placeholder="Contoh: Kantin Sekolah / Kelas X DKV 1">
                        </div>

                        <div class="col-12">
                            <label for="deskripsi" class="form-label">Ciri-Ciri Detail & Isi Barang</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Tuliskan ciri khas, stiker, isi dompet/tas, goresan, atau hal pengenal lainnya..."><?= e($old['deskripsi'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label for="foto" class="form-label">Upload Foto Barang (Jika Ada)</label>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" data-preview="preview-lapor-hilang">
                            <small class="text-muted d-block mt-1">Unggah foto barang saat masih ada (Maks 2MB).</small>
                            <div class="image-preview-container mt-2" id="preview-lapor-hilang" style="display: none;"></div>
                        </div>

                        <div class="col-12 mt-4 d-flex gap-2 justify-content-end">
                            <a href="<?= url('') ?>" class="btn btn-outline-secondary btn-rounded px-4">Batal</a>
                            <button type="submit" class="btn btn-navy btn-rounded px-4 fw-semibold">
                                <i class="bi bi-send me-1"></i> Kirim Laporan Kehilangan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
