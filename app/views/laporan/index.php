
<div class="container py-5">

    <!-- HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-navy mb-1">Daftar Barang Hilang</h2>
            <p class="text-muted small mb-0">
                Daftar barang yang dilaporkan hilang oleh siswa <?= SCHOOL_NAME ?>.
            </p>
        </div>

        <?php if (isLoggedIn()): ?>
            <a href="<?= url('laporkan-hilang') ?>" class="btn btn-navy btn-rounded">
                <i class="bi bi-plus-lg me-1"></i>
                Laporkan Barang Hilang
            </a>
        <?php endif; ?>
    </div>


    <!-- FILTER & SEARCH -->
    <div class="card card-custom p-3 mb-4 border-0">
        <form action="<?= url('laporan') ?>" method="GET" class="row g-2 align-items-center">

            <div class="col-lg-5 col-md-12">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>

                    <input
                        type="text"
                        name="search"
                        class="form-control border-start-0 ps-0"
                        placeholder="Cari barang hilang, warna, lokasi..."
                        value="<?= e($filters['search'] ?? '') ?>"
                    >
                </div>
            </div>


            <div class="col-lg-3 col-md-5">
                <select
                    name="kategori"
                    class="form-select"
                    onchange="this.form.submit()"
                >
                    <option value="">Semua Kategori</option>

                    <?php foreach ($categories as $cat): ?>
                        <option
                            value="<?= e($cat) ?>"
                            <?= ($filters['kategori'] ?? '') === $cat ? 'selected' : '' ?>
                        >
                            <?= e($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>


            <div class="col-lg-2 col-md-4">
                <select
                    name="status"
                    class="form-select"
                    onchange="this.form.submit()"
                >
                    <option value="">Semua Status</option>

                    <option
                        value="menunggu"
                        <?= ($filters['status'] ?? '') === 'menunggu' ? 'selected' : '' ?>
                    >
                        Menunggu
                    </option>

                    <option
                        value="diverifikasi"
                        <?= ($filters['status'] ?? '') === 'diverifikasi' ? 'selected' : '' ?>
                    >
                        Diverifikasi
                    </option>

                    <option
                        value="ditemukan"
                        <?= ($filters['status'] ?? '') === 'ditemukan' ? 'selected' : '' ?>
                    >
                        Ditemukan
                    </option>

                    <option
                        value="selesai"
                        <?= ($filters['status'] ?? '') === 'selesai' ? 'selected' : '' ?>
                    >
                        Selesai
                    </option>
                </select>
            </div>


            <div class="col-lg-2 col-md-3 d-flex gap-2">

                <button
                    type="submit"
                    class="btn btn-pastel-blue btn-rounded w-100 fw-semibold"
                >
                    Terapkan
                </button>

                <?php if (
                    !empty($filters['search']) ||
                    !empty($filters['kategori']) ||
                    !empty($filters['status'])
                ): ?>

                    <a
                        href="<?= url('laporan') ?>"
                        class="btn btn-outline-secondary btn-rounded"
                        title="Reset Filter"
                    >
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>

                <?php endif; ?>

            </div>

        </form>
    </div>


    <!-- DAFTAR BARANG -->
    <?php if (empty($items)): ?>

        <div class="card card-custom p-5 text-center border-0 my-4">

            <div
                class="stat-icon-wrapper mx-auto mb-3"
                style="
                    background-color: var(--pastel-yellow);
                    color: #6a530f;
                    width: 64px;
                    height: 64px;
                "
            >
                <i class="bi bi-search fs-3"></i>
            </div>

            <h4 class="fw-bold text-navy mb-2">
                Laporan Tidak Ditemukan
            </h4>

            <p
                class="text-muted small mx-auto"
                style="max-width: 480px;"
            >
                Tidak ada laporan kehilangan yang cocok dengan filter
                atau kata kunci Anda.
            </p>

            <div class="mt-2">
                <a
                    href="<?= url('laporan') ?>"
                    class="btn btn-pastel-blue btn-rounded"
                >
                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                    Tampilkan Semua Laporan
                </a>
            </div>

        </div>

    <?php else: ?>

        <div class="row g-4">

            <?php foreach ($items as $item): ?>

                <div class="col-lg-4 col-md-6">

                    <div
                        class="card card-custom h-100 d-flex flex-column overflow-hidden"
                    >

                        <!-- ============================= -->
                        <!-- FOTO BARANG -->
                        <!-- ============================= -->

                        <?php if (!empty($item['foto'])): ?>

                            <div
                                class="position-relative bg-light"
                                style="height: 230px;"
                            >

                                <img
                                    src="<?= uploadUrl($item['foto']) ?>"
                                    alt="<?= e($item['nama_barang']) ?>"
                                    class="w-100 h-100"
                                    style="object-fit: cover;"
                                    loading="lazy"
                                    onerror="
                                        this.style.display='none';
                                        this.parentElement.querySelector('.photo-error').style.display='flex';
                                    "
                                >

                                <div
                                    class="photo-error d-none align-items-center justify-content-center h-100"
                                >
                                    <div class="text-center text-muted">
                                        <i class="bi bi-image fs-1 d-block mb-2"></i>
                                        <small>Foto tidak dapat dimuat</small>
                                    </div>
                                </div>

                            </div>

                        <?php else: ?>

                            <div
                                class="d-flex align-items-center justify-content-center bg-light"
                                style="height: 230px;"
                            >

                                <div class="text-center text-muted">

                                    <div
                                        class="stat-icon-wrapper mx-auto mb-2"
                                        style="
                                            background: var(--pastel-purple);
                                            color: #472b69;
                                            width: 64px;
                                            height: 64px;
                                        "
                                    >
                                        <i class="bi bi-image fs-3"></i>
                                    </div>

                                    <small>
                                        Tidak ada foto
                                    </small>

                                </div>

                            </div>

                        <?php endif; ?>


                        <!-- ============================= -->
                        <!-- INFORMASI -->
                        <!-- ============================= -->

                        <div class="p-4 flex-grow-1">

                            <!-- KATEGORI + STATUS -->

                            <div
                                class="d-flex justify-content-between align-items-start mb-3 gap-2"
                            >

                                <span class="badge badge-pastel-purple">

                                    <i class="bi bi-tag-fill me-1"></i>

                                    <?= e($item['kategori']) ?>

                                </span>

                                <?= getStatusBadge($item['status']) ?>

                            </div>


                            <!-- NAMA BARANG -->

                            <h5 class="fw-bold text-navy mb-2">
                                <?= e($item['nama_barang']) ?>
                            </h5>


                            <!-- WARNA -->

                            <?php if (!empty($item['warna'])): ?>

                                <div class="text-muted small mb-2">

                                    <i class="bi bi-palette-fill me-1"></i>

                                    <strong>Warna:</strong>
                                    <?= e($item['warna']) ?>

                                </div>

                            <?php endif; ?>


                            <!-- DESKRIPSI -->

                            <p class="text-muted small mb-3">

                                <?= e(
                                    mb_strimwidth(
                                        $item['deskripsi'] ?? 'Tidak ada deskripsi rinci.',
                                        0,
                                        110,
                                        '...'
                                    )
                                ) ?>

                            </p>


                            <!-- DETAIL -->

                            <div
                                class="p-3 bg-light rounded-4 small d-flex flex-column gap-2 text-muted"
                            >

                                <!-- LOKASI -->

                                <div>

                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>

                                    <strong>Lokasi Terakhir:</strong>

                                    <?= e(
                                        $item['lokasi_terakhir'] ?? '-'
                                    ) ?>

                                </div>


                                <!-- TANGGAL -->

                                <div>

                                    <i class="bi bi-calendar3 text-primary me-1"></i>

                                    <strong>Tanggal Hilang:</strong>

                                    <?= formatTanggalIndo(
                                        $item['tanggal_hilang']
                                    ) ?>

                                </div>


                                <!-- PEMILIK -->

                                <div>

                                    <i class="bi bi-person-fill text-secondary me-1"></i>

                                    <strong>Pemilik:</strong>

                                    <?= e(
                                        $item['pelapor_nama'] ?? '-'
                                    ) ?>

                                </div>


                                <!-- KELAS -->

                                <div>

                                    <i class="bi bi-mortarboard-fill text-secondary me-1"></i>

                                    <strong>Kelas:</strong>

                                    <?= e(
                                        $item['pelapor_kelas'] ?? '-'
                                    ) ?>

                                </div>

                            </div>

                        </div>


                        <!-- ============================= -->
                        <!-- ACTION -->
                        <!-- ============================= -->

                        <div
                            class="card-footer bg-white border-top p-3 text-center"
                        >

                            <?php
                                $ownerPhone = preg_replace('/\D+/', '', (string)($item['pelapor_telepon'] ?? ''));
                                if ($ownerPhone !== '' && str_starts_with($ownerPhone, '0')) {
                                    $ownerPhone = '62' . substr($ownerPhone, 1);
                                }
                            ?>

                            <div class="d-flex flex-wrap gap-2">
                                <a
                                    href="<?= url('laporkan-ditemukan') ?>"
                                    class="btn btn-pastel-green btn-rounded btn-sm flex-grow-1 fw-semibold"
                                >
                                    <i class="bi bi-hand-thumbs-up me-1"></i> Saya Menemukan Barang Ini
                                </a>

                                <?php if (isLoggedIn() && $ownerPhone !== ''): ?>
                                    <a
                                        href="https://wa.me/<?= e($ownerPhone) ?>?text=<?= rawurlencode('Halo, saya melihat laporan kehilangan Anda di Lost & Found. Saya mungkin menemukan barang yang sesuai. Mari koordinasi dengan Admin/Ruang Guru.') ?>"
                                        target="_blank"
                                        rel="noopener"
                                        class="btn btn-success btn-rounded btn-sm"
                                        title="Hubungi pemilik"
                                    >
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                <?php endif; ?>
                            </div>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>


        <!-- ============================= -->
        <!-- PAGINATION -->
        <!-- ============================= -->

        <?php if (($pagination['totalPages'] ?? 0) > 1): ?>

            <nav
                class="mt-5"
                aria-label="Navigasi Halaman"
            >

                <ul class="pagination justify-content-center gap-1">

                    <?php if ($pagination['page'] > 1): ?>

                        <li class="page-item">

                            <a
                                class="page-link rounded-circle"
                                href="<?= url(
                                    'laporan?page=' .
                                    ($pagination['page'] - 1) .

                                    (!empty($filters['search'])
                                        ? '&search=' . urlencode($filters['search'])
                                        : '') .

                                    (!empty($filters['kategori'])
                                        ? '&kategori=' . urlencode($filters['kategori'])
                                        : '') .

                                    (!empty($filters['status'])
                                        ? '&status=' . urlencode($filters['status'])
                                        : '')
                                ) ?>"
                                aria-label="Sebelumnya"
                            >
                                <i class="bi bi-chevron-left"></i>
                            </a>

                        </li>

                    <?php endif; ?>


                    <?php for (
                        $i = 1;
                        $i <= $pagination['totalPages'];
                        $i++
                    ): ?>

                        <li
                            class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>"
                        >

                            <a
                                class="page-link rounded-circle"
                                href="<?= url(
                                    'laporan?page=' .
                                    $i .

                                    (!empty($filters['search'])
                                        ? '&search=' . urlencode($filters['search'])
                                        : '') .

                                    (!empty($filters['kategori'])
                                        ? '&kategori=' . urlencode($filters['kategori'])
                                        : '') .

                                    (!empty($filters['status'])
                                        ? '&status=' . urlencode($filters['status'])
                                        : '')
                                ) ?>"
                            >
                                <?= $i ?>
                            </a>

                        </li>

                    <?php endfor; ?>


                    <?php if (
                        $pagination['page'] <
                        $pagination['totalPages']
                    ): ?>

                        <li class="page-item">

                            <a
                                class="page-link rounded-circle"
                                href="<?= url(
                                    'laporan?page=' .
                                    ($pagination['page'] + 1) .

                                    (!empty($filters['search'])
                                        ? '&search=' . urlencode($filters['search'])
                                        : '') .

                                    (!empty($filters['kategori'])
                                        ? '&kategori=' . urlencode($filters['kategori'])
                                        : '') .

                                    (!empty($filters['status'])
                                        ? '&status=' . urlencode($filters['status'])
                                        : '')
                                ) ?>"
                                aria-label="Selanjutnya"
                            >
                                <i class="bi bi-chevron-right"></i>
                            </a>

                        </li>

                    <?php endif; ?>

                </ul>

            </nav>

        <?php endif; ?>

    <?php endif; ?>

</div>

