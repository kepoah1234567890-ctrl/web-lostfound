/**
 * Lost & Found IFSU - Smooth Scroll & Reveal
 * Dipakai untuk halaman publik dan Admin Panel.
 */
(function () {
    'use strict';

    function initSmoothUI() {
        document.documentElement.classList.add('smooth-scroll-enabled');

        // Semua elemen utama dibuat reveal otomatis.
        const selectors = [
            'main > *',
            'section',
            '.hero-section',
            '.card-custom',
            '.stat-card',
            '.step-card',
            '.admin-stat-card',
            '.admin-sidebar-card',
            '.admin-report-sheet',
            '.admin-chart-row',
            '.table-responsive',
            '.alert',
            '.form-section',
            '.auth-card',
            '.item-card',
            '.barang-card',
            '.claim-card',
            '.report-card',
            '.profile-card'
        ];

        const elements = Array.from(document.querySelectorAll(selectors.join(',')));
        const unique = [];
        const seen = new Set();

        elements.forEach(function (el) {
            if (seen.has(el)) return;
            seen.add(el);

            // Jangan menyembunyikan elemen yang sangat kecil atau elemen di dalam modal.
            if (el.closest('.modal')) return;
            if (el.classList.contains('scroll-reveal')) return;

            unique.push(el);
        });

        unique.forEach(function (element, index) {
            element.classList.add('scroll-reveal');

            // Arah masuk dibuat dinamis berdasarkan posisi elemen di layar.
            // Jadi tidak semua elemen selalu muncul dari bawah.
            const rect = element.getBoundingClientRect();
            const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
            const centerX = rect.left + (rect.width / 2);
            const cycle = index % 6;

            let direction;
            if (centerX < viewportWidth * 0.30) {
                direction = 'left';
            } else if (centerX > viewportWidth * 0.70) {
                direction = 'right';
            } else if (cycle === 0) {
                direction = 'top';
            } else if (cycle === 1) {
                direction = 'right';
            } else if (cycle === 2) {
                direction = 'left';
            } else if (cycle === 3) {
                direction = 'zoom';
            } else if (cycle === 4) {
                direction = 'bottom';
            } else {
                direction = 'top';
            }

            element.classList.add('reveal-' + direction);
            element.style.setProperty('--reveal-delay', Math.min((index % 5) * 65, 260) + 'ms');
        });

        if (!('IntersectionObserver' in window)) {
            unique.forEach(function (element) {
                element.classList.add('show');
            });
        } else {
            const observer = new IntersectionObserver(function (entries, obs) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('show');
                    obs.unobserve(entry.target);
                });
            }, {
                threshold: 0.08,
                rootMargin: '0px 0px -55px 0px'
            });

            unique.forEach(function (element) {
                observer.observe(element);
            });
        }

        // Smooth untuk anchor internal (#panduan-klaim, dll).
        document.querySelectorAll('a[href^="#"]').forEach(function (link) {
            link.addEventListener('click', function (event) {
                const id = link.getAttribute('href');
                if (!id || id === '#') return;
                const target = document.querySelector(id);
                if (!target) return;

                event.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });

        // Reveal tambahan saat halaman selesai dimuat agar bagian paling atas terasa halus.
        requestAnimationFrame(function () {
            document.body.classList.add('page-loaded');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSmoothUI, { once: true });
    } else {
        initSmoothUI();
    }
})();
