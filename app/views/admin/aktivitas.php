<div class="container-fluid py-4">
    <div class="row g-4">
        

        <div class="col-12 admin-view-content">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h3 class="fw-bold text-navy mb-1">Aktivitas per Akun</h3>
                    <p class="text-muted small mb-0">Buka akun untuk melihat riwayat aktivitas Web dan Mobile.</p>
                </div>
                <span class="badge badge-pastel-blue px-3 py-2"><?= number_format(count($activityGroups)) ?> akun · <?= number_format($totalAktivitas) ?> aktivitas</span>
            </div>

            <div class="alert alert-info border-0 rounded-4 small">
                <i class="bi bi-info-circle-fill me-2"></i>
                Setiap akun memiliki riwayat sendiri. Aktivitas Web dan Mobile ditampilkan bersama di dalam akun pelakunya.
            </div>

            <div class="card card-custom p-3 mb-4 border-0">
                <form action="<?= url('admin/aktivitas') ?>" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari user, barang, aktivitas..." value="<?= e($filters['search'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="aktivitas" class="form-select">
                            <option value="">Semua Aktivitas</option>
                            <?php foreach ($activityTypes as $type): ?>
                                <option value="<?= e($type) ?>" <?= ($filters['aktivitas'] ?? '') === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="actor_role" class="form-select">
                            <option value="siswa" selected>User</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="channel" class="form-select">
                            <option value="">Semua Channel</option>
                            <option value="web" <?= ($filters['channel'] ?? '') === 'web' ? 'selected' : '' ?>>Web</option>
                            <option value="mobile" <?= ($filters['channel'] ?? '') === 'mobile' ? 'selected' : '' ?>>Mobile</option>
                            <option value="whatsapp" <?= ($filters['channel'] ?? '') === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                            <option value="telepon" <?= ($filters['channel'] ?? '') === 'telepon' ? 'selected' : '' ?>>Telepon</option>
                            <option value="email" <?= ($filters['channel'] ?? '') === 'email' ? 'selected' : '' ?>>Email</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-pastel-blue btn-rounded w-100 fw-semibold">Filter</button>
                        <?php if (!empty($filters)): ?>
                            <a href="<?= url('admin/aktivitas') ?>" class="btn btn-outline-secondary btn-rounded"><i class="bi bi-arrow-counterclockwise"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="accordion" id="activityAccounts">
                <?php if (empty($activityGroups)): ?>
                    <div class="card card-custom border-0 p-5 text-center text-muted">Belum ada aktivitas yang tercatat.</div>
                <?php else: ?>
                    <?php foreach ($activityGroups as $group): ?>
                        <?php $userId = (int)$group['user_id']; ?>
                        <?php $panelId = 'activity-account-' . (int)$userId; ?>
                        <section class="accordion-item card-custom border-0 mb-3 overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#<?= e($panelId) ?>" aria-expanded="false" aria-controls="<?= e($panelId) ?>">
                                    <span class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 w-100 me-3">
                                        <span>
                                            <span class="d-block fw-bold text-navy"><?= e($group['user_nama'] ?? 'Pengguna') ?></span>
                                            <small class="text-muted d-block"><?= e($group['user_email'] ?? '-') ?> · <?= e($group['user_kelas'] ?? '-') ?> · NIS <?= e($group['user_nis'] ?? '-') ?></small>
                                        </span>
                                        <span class="d-flex flex-wrap align-items-center gap-2">
                                            <?php $userRole = (string)($group['user_role'] ?? 'siswa'); ?>
                                            <span class="badge <?= $userRole === 'admin' ? 'badge-pastel-yellow' : 'badge-pastel-blue' ?>"><?= e(strtoupper($userRole)) ?></span>
                                            <span class="badge badge-pastel-blue"><?= number_format((int)$group['activity_count']) ?> aktivitas</span>
                                            <span class="badge badge-pastel-green">Web <?= number_format((int)$group['web_count']) ?></span>
                                            <span class="badge badge-pastel-yellow">Mobile <?= number_format((int)$group['mobile_count']) ?></span>
                                        </span>
                                    </span>
                                </button>
                            </h2>
                            <div id="<?= e($panelId) ?>" class="accordion-collapse collapse" data-bs-parent="#activityAccounts">
                                <div class="accordion-body p-0 activity-history"
                                    data-user-id="<?= $userId ?>"
                                    data-endpoint="<?= e(url('api/aktivitas.php')) ?>"
                                    data-activity="<?= e($filters['aktivitas'] ?? '') ?>"
                                    data-channel="<?= e($filters['channel'] ?? '') ?>"
                                    data-search="<?= e($filters['search'] ?? '') ?>"
                                    data-offset="0"
                                    data-total="<?= (int)$group['activity_count'] ?>">
                                    <div class="px-3 px-md-4 py-4 text-muted small">Buka akun untuk memuat riwayat.</div>
                                </div>
                            </div>
                        </section>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const histories = document.querySelectorAll('.activity-history');

    const makeElement = (tag, className, text) => {
        const element = document.createElement(tag);
        if (className) element.className = className;
        if (text) element.textContent = text;
        return element;
    };

    const renderActivity = (activity) => {
        const entry = makeElement('article', 'px-3 px-md-4 py-3 border-top');
        const header = makeElement('div', 'd-flex flex-wrap justify-content-between align-items-start gap-2 mb-2');
        const badges = makeElement('div', 'd-flex flex-wrap align-items-center gap-2');
        const activityBadge = makeElement('span', 'badge badge-pastel-blue', activity.aktivitas || 'Aktivitas');
        let metadata = activity.metadata;
        if (typeof metadata === 'string') {
            try { metadata = JSON.parse(metadata); } catch (_) { metadata = {}; }
        }
        const channel = metadata && metadata.channel ? String(metadata.channel) : 'lainnya';
        const channelClass = channel === 'mobile' ? 'badge-pastel-yellow' : 'badge-pastel-green';
        badges.append(activityBadge, makeElement('span', `badge ${channelClass}`, channel.charAt(0).toUpperCase() + channel.slice(1)));
        header.append(badges, makeElement('small', 'text-muted', activity.created_at || ''));
        entry.append(header, makeElement('div', 'small mb-2', activity.deskripsi || '-'));

        const details = makeElement('div', 'd-flex flex-wrap gap-3 small text-muted');
        if (activity.target_user_nama) {
            details.append(makeElement('span', '', `Target: ${activity.target_user_nama} · ${activity.target_user_telepon || '-'}`));
        }
        const relatedItem = activity.barang_nama || activity.laporan_nama_barang;
        if (relatedItem) details.append(makeElement('span', '', `Barang/laporan: ${relatedItem}`));
        if (details.childElementCount) entry.append(details);
        return entry;
    };

    const loadHistory = async (history) => {
        if (history.dataset.loading === 'true') return;
        history.dataset.loading = 'true';
        const offset = Number(history.dataset.offset || 0);
        const endpoint = new URL(history.dataset.endpoint, window.location.href);
        endpoint.searchParams.set('user_id', history.dataset.userId);
        endpoint.searchParams.set('limit', '50');
        endpoint.searchParams.set('offset', String(offset));
        if (history.dataset.activity) endpoint.searchParams.set('aktivitas', history.dataset.activity);
        if (history.dataset.channel) endpoint.searchParams.set('channel', history.dataset.channel);
        if (history.dataset.search) endpoint.searchParams.set('search', history.dataset.search);

        try {
            const response = await fetch(endpoint, { credentials: 'same-origin' });
            const result = await response.json();
            const payload = result && result.data;
            if (!response.ok || !result.success || !payload || !Array.isArray(payload.data)) {
                throw new Error((result && result.message) || 'Riwayat aktivitas gagal dimuat.');
            }

            if (offset === 0) history.replaceChildren();
            payload.data.forEach((activity) => history.append(renderActivity(activity)));
            const nextOffset = offset + payload.data.length;
            history.dataset.offset = String(nextOffset);
            history.dataset.total = String(payload.total);
            history.dataset.loaded = 'true';
            history.querySelector('[data-load-more]')?.remove();

            if (nextOffset < Number(payload.total)) {
                const more = makeElement('button', 'btn btn-outline-primary btn-sm m-3', 'Muat aktivitas lainnya');
                more.type = 'button';
                more.dataset.loadMore = 'true';
                more.addEventListener('click', () => loadHistory(history));
                history.append(more);
            } else if (nextOffset === 0) {
                history.append(makeElement('div', 'px-3 px-md-4 py-4 text-muted small', 'Belum ada aktivitas untuk filter ini.'));
            }
        } catch (error) {
            history.querySelector('[data-load-error]')?.remove();
            const message = makeElement('div', 'px-3 px-md-4 py-3 text-danger small', error.message || 'Riwayat aktivitas gagal dimuat.');
            message.dataset.loadError = 'true';
            const retry = makeElement('button', 'btn btn-outline-secondary btn-sm ms-3', 'Coba lagi');
            retry.type = 'button';
            retry.addEventListener('click', () => loadHistory(history));
            message.append(retry);
            history.append(message);
        } finally {
            history.dataset.loading = 'false';
        }
    };

    document.querySelectorAll('#activityAccounts .accordion-collapse').forEach((panel) => {
        panel.addEventListener('show.bs.collapse', () => {
            const history = panel.querySelector('.activity-history');
            if (history && history.dataset.loaded !== 'true') loadHistory(history);
        });
    });
})();
</script>
