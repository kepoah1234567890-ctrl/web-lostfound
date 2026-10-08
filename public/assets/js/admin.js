/**
 * Admin Panel JavaScript
 * Lost & Found SMK Informatika Sumedang
 */

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('adminSidebarBackdrop');
    const toggleBtn = document.getElementById('adminMobileToggle');
    const closeBtn = document.getElementById('adminSidebarClose');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('sidebar-open');
        if (backdrop) backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('sidebar-open');
        if (backdrop) backdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (sidebar && sidebar.classList.contains('sidebar-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            closeSidebar();
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', function () {
            closeSidebar();
        });
    }

    // Close sidebar on ESC key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });

    // Handle generic confirm delete
    document.querySelectorAll('[data-confirm-delete]').forEach(function (button) {
        button.addEventListener('click', function (e) {
            const msg = button.getAttribute('data-confirm-delete') || 'Apakah Anda yakin ingin menghapus data ini?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });
});
