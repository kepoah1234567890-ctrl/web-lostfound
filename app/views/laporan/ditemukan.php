<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom p-4 p-md-5 border-0">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="stat-icon-wrapper" style="background-color: var(--pastel-green); color: #1e5a32;">
                        <i class="bi bi-hand-thumbs-up fs-4"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold text-navy mb-0">Laporkan Barang yang Ditemukan</h3>
                        <p class="text-muted small mb-0">Bantu teman atau warga sekolah menemukan barangnya yang tercecer.</p>
                    </div>
                </div>

                <?php
                    $adminPhone = preg_replace('/\D+/', '', (string)($adminContact['no_telepon'] ?? ''));
                    if ($adminPhone !== '' && str_starts_with($adminPhone, '0')) {
                        $adminPhone = '62' . substr($adminPhone, 1);
                    }
                ?>

                <div class="alert alert-info border-0 rounded-4 mb-4">
                    <div class="fw-bold mb-1"><i class="bi bi-info-circle me-1"></i> Setelah menemukan barang</div>
                    <div class="small">
                        Laporkan barang ke sistem, lalu kamu boleh menghubungi pemilik jika ada laporan yang cocok.
                        Kamu juga tetap bisa menghubungi Admin dan menyerahkan barang ke Admin/Ruang Guru. Semua proses tetap tercatat di sistem.
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <?php if (!empty($adminContact['email'])): ?>
                            <a href="mailto:<?= e($adminContact['email']) ?>" class="btn btn-outline-primary btn-sm btn-rounded">
                                <i class="bi bi-envelope me-1"></i> Hubungi Admin
                            </a>
                        <?php endif; ?>
                        <?php if ($adminPhone !== ''): ?>
                            <a href="https://wa.me/<?= e($adminPhone) ?>" target="_blank" rel="noopener" class="btn btn-success btn-sm btn-rounded">
                                <i class="bi bi-whatsapp me-1"></i> WhatsApp Admin
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger border-0 rounded-3 small py-2 px-3 mb-4">
                        <i class="bi bi-exclamation-circle-fill me-2"></i> <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= url('laporkan-ditemukan') ?>" method="POST" enctype="multipart/form-data">
                <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nama_barang" class="form-label">Nama Barang yang Ditemukan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" value="<?= e($old['nama_barang'] ?? '') ?>" placeholder="Contoh: Kotak Kacamata Hitam" required autofocus>
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
                            <label for="warna" class="form-label">Warna Barang</label>
                            <input type="text" class="form-control" id="warna" name="warna" value="<?= e($old['warna'] ?? '') ?>" placeholder="Contoh: Hitam / Silver">
                        </div>

                        <div class="col-md-6">
                            <label for="tanggal_ditemukan" class="form-label">Tanggal Ditemukan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_ditemukan" name="tanggal_ditemukan" value="<?= e($old['tanggal_ditemukan'] ?? date('Y-m-d')) ?>" required>
                        </div>

                        <div class="col-12">
                            <label for="lokasi_ditemukan" class="form-label">Lokasi Ditemukan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lokasi_ditemukan" name="lokasi_ditemukan" value="<?= e($old['lokasi_ditemukan'] ?? '') ?>" placeholder="Contoh: Di bawah meja kantin dekat koperasi" required>
                        </div>

                        <div class="col-12">
                            <label for="deskripsi" class="form-label">Deskripsi Barang & Tempat Pengamanan</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Sebutkan kondisi saat ditemukan, atau apakah barang saat ini sudah diserahkan ke Ruang TU / Guru Piket..."><?= e($old['deskripsi'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label for="foto" class="form-label">Foto Barang (Sangat Dianjurkan)</label>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" data-preview="preview-lapor-ditemukan">
                            <small class="text-muted d-block mt-1">Upload foto barang agar pemilik cepat mengenali (Maks 2MB).</small>
                            <div class="image-preview-container mt-2" id="preview-lapor-ditemukan" style="display: none;"></div>
                        </div>

                        <div class="col-12 mt-4 d-flex gap-2 justify-content-end">
                            <a href="<?= url('') ?>" class="btn btn-outline-secondary btn-rounded px-4">Batal</a>
                            <button type="submit" class="btn btn-pastel-green btn-rounded px-4 fw-bold">
                                <i class="bi bi-check-circle me-1"></i> Publikasikan Barang Temuan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
