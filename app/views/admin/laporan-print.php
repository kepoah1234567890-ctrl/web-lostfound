<div class="admin-page-wrap">
    <div class="admin-report-sheet p-4">
        <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-3">
            <img src="<?= asset('images/ifsu-logo.png') ?>" alt="Logo SMK Informatika Sumedang" style="width:74px;height:74px;object-fit:contain;">
            <div class="flex-grow-1">
                <h2 class="fw-bold text-navy mb-1">REKAP LAPORAN BARANG HILANG</h2>
                <div class="fw-semibold">Lost &amp; Found — SMK Informatika Sumedang</div>
                <div class="small text-muted">Dokumen rekapitulasi untuk pemantauan dan pelaporan kepada pimpinan.</div>
            </div>
            <div class="text-end small text-muted">
                Dicetak<br><strong><?= e($generatedAt) ?></strong>
            </div>
        </div>

        <div class="row g-2 mb-4">
            <div class="col-3"><div class="border rounded-3 p-2"><small>Total Laporan</small><div class="fs-4 fw-bold text-navy"><?= count($laporanList) ?></div></div></div>
            <div class="col-3"><div class="border rounded-3 p-2"><small>Menunggu</small><div class="fs-4 fw-bold"><?= $statusCounts['menunggu'] ?></div></div></div>
            <div class="col-3"><div class="border rounded-3 p-2"><small>Ditemukan</small><div class="fs-4 fw-bold text-success"><?= $statusCounts['ditemukan'] ?></div></div></div>
            <div class="col-3"><div class="border rounded-3 p-2"><small>Selesai</small><div class="fs-4 fw-bold text-navy"><?= $statusCounts['selesai'] ?></div></div></div>
        </div>

        <div class="mb-3 small">
            <strong>Periode:</strong>
            <?= !empty($filters['date_from']) ? e(formatTanggalIndo($filters['date_from'])) : 'Semua tanggal' ?>
            &nbsp; s/d &nbsp;
            <?= !empty($filters['date_to']) ? e(formatTanggalIndo($filters['date_to'])) : 'Semua tanggal' ?>
            <?php if (!empty($filters['status'])): ?> · <strong>Status:</strong> <?= e(ucfirst($filters['status'])) ?><?php endif; ?>
            <?php if (!empty($filters['search'])): ?> · <strong>Pencarian:</strong> <?= e($filters['search']) ?><?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle small">
                <thead>
                    <tr><th>No</th><th>Foto</th><th>Barang</th><th>Pelapor</th><th>Kelas/NIS</th><th>Lokasi Terakhir</th><th>Tanggal Hilang</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php if (!$laporanList): ?>
                    <tr><td colspan="8" class="text-center py-4">Tidak ada data.</td></tr>
                <?php else: foreach ($laporanList as $i => $lap): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><img src="<?= e(uploadUrl($lap['foto'] ?? null)) ?>" class="admin-report-photo" alt="Foto barang"></td>
                        <td><strong><?= e($lap['nama_barang']) ?></strong><br><?= e($lap['kategori'] ?? '-') ?> / <?= e($lap['warna'] ?? '-') ?><br><span class="text-muted"><?= e(mb_strimwidth($lap['deskripsi'] ?? '', 0, 70, '...')) ?></span></td>
                        <td><?= e($lap['pelapor_nama'] ?? '-') ?></td>
                        <td><?= e($lap['pelapor_kelas'] ?? '-') ?><br><?= e($lap['pelapor_nis'] ?? '-') ?></td>
                        <td><?= e($lap['lokasi_terakhir'] ?? '-') ?></td>
                        <td><?= !empty($lap['tanggal_hilang']) ? e(formatTanggalIndo($lap['tanggal_hilang'])) : '-' ?></td>
                        <td><?= e(ucfirst($lap['status'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <div class="row mt-5 pt-3">
            <div class="col-8 small text-muted">Catatan: data pada dokumen ini diambil langsung dari database Lost &amp; Found pada saat laporan dicetak.</div>
            <div class="col-4 text-center">
                <div>Mengetahui,</div><div class="fw-bold mt-5">____________________________</div><div>Pimpinan / Penanggung Jawab</div>
            </div>
        </div>
    </div>
</div>
<script>window.addEventListener('load', function(){ setTimeout(function(){ window.print(); }, 500); });</script>
