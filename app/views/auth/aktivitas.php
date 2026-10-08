<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1">Aktivitas Saya</h2>
            <p class="text-muted mb-0">Riwayat aktivitas yang dilakukan oleh akun ini.</p>
        </div>
        <span class="badge badge-pastel-blue px-3 py-2"><?= number_format($totalAktivitas) ?> aktivitas</span>
    </div>

    <div class="card card-custom border-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-custom mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Aktivitas</th>
                        <th>Detail</th>
                        <th>Sumber</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($aktivitasList)): ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">Belum ada aktivitas yang tercatat.</td></tr>
                    <?php else: ?>
                        <?php foreach ($aktivitasList as $activity): ?>
                            <?php
                                $metadata = json_decode($activity['metadata'] ?? '', true);
                                $channel = is_array($metadata) ? ($metadata['channel'] ?? '-') : '-';
                            ?>
                            <tr>
                                <td><small class="text-muted"><?= formatTanggalIndo($activity['created_at'], true) ?></small></td>
                                <td><span class="badge badge-pastel-blue"><?= e($activity['aktivitas']) ?></span></td>
                                <td class="small"><?= e($activity['deskripsi'] ?? '-') ?></td>
                                <td><span class="badge badge-pastel-green"><?= e(ucfirst((string)$channel)) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
