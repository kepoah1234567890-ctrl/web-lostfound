<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom p-4 p-md-5 border-0">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="stat-icon-wrapper" style="background-color: var(--pastel-yellow); color: #6a530f;">
                        <i class="bi bi-pencil-square fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold text-navy mb-0">Edit Data Barang Ditemukan</h3>
                        <p class="text-muted small mb-0">Perbarui informasi barang #<?= $item['id'] ?> - <?= e($item['nama_barang']) ?></p>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger border-0 rounded-3 small py-2 px-3 mb-4">
                        <i class="bi bi-exclamation-circle-fill me-2"></i> <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= url('admin/barang/edit?id=' . $item['id']) ?>" method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nama_barang" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" value="<?= e($item['nama_barang']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kategori" name="kategori" list="kategoriList" value="<?= e($item['kategori']) ?>" required>
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

                        <div class="col-md-4">
                            <label for="warna" class="form-label">Warna Utama</label>
                            <input type="text" class="form-control" id="warna" name="warna" value="<?= e($item['warna']) ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="tanggal_ditemukan" class="form-label">Tanggal Ditemukan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_ditemukan" name="tanggal_ditemukan" value="<?= e($item['tanggal_ditemukan']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label for="status" class="form-label">Status Barang <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status">
                                <option value="tersedia" <?= $item['status'] === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                                <option value="menunggu_klaim" <?= $item['status'] === 'menunggu_klaim' ? 'selected' : '' ?>>Menunggu Klaim</option>
                                <option value="diklaim" <?= $item['status'] === 'diklaim' ? 'selected' : '' ?>>Diklaim</option>
                                <option value="dikembalikan" <?= $item['status'] === 'dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="lokasi_ditemukan" class="form-label">Lokasi Ditemukan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lokasi_ditemukan" name="lokasi_ditemukan" value="<?= e($item['lokasi_ditemukan']) ?>" required>
                        </div>

                        <div class="col-12">
                            <label for="deskripsi" class="form-label">Deskripsi & Kondisi Barang</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= e($item['deskripsi']) ?></textarea>
                        </div>

                        <div class="col-12">
                            <label for="foto" class="form-label">Ganti Foto Barang (Opsional)</label>
                            <?php if (!empty($item['foto'])): ?>
                                <div class="mb-2">
                                    <small class="text-muted d-block mb-1">Foto saat ini:</small>
                                    <img src="<?= uploadUrl($item['foto']) ?>" alt="Foto" style="max-height: 100px;" class="rounded border">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" data-preview="preview-box">
                            <small class="text-muted d-block mt-1">Kosongkan jika tidak ingin mengubah foto. Format: JPG, PNG, WEBP (Maks 2MB).</small>
                            <div class="image-preview-container mt-2" id="preview-box" style="display: none;"></div>
                        </div>

                        <div class="col-12 mt-4 d-flex gap-2 justify-content-end">
                            <a href="<?= url('admin/barang') ?>" class="btn btn-outline-secondary btn-rounded px-4">Batal</a>
                            <button type="submit" class="btn btn-navy btn-rounded px-4 fw-semibold">
                                <i class="bi bi-save me-1"></i> Perbarui Barang
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
