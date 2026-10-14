import './bootstrap';

const originalFetch = window.fetch;
window.fetch = async function() {
    const response = await originalFetch.apply(this, arguments);
    if (response.status === 401) {
        // Jangan tampilkan alert jika berada di halaman login
        if (window.location.pathname !== '/login') {
            alert('Sesi Anda telah habis (lebih dari 4 jam). Silakan login kembali.');
            sessionStorage.removeItem('staff_token');
            window.location.href = '/login';
        }
    }
    return response;
};

// Shared modal toggle for [data-modal-open] / [data-modal-close].
(function () {
    if (window.__modalReady) return;
    window.__modalReady = true;

    window.setModal = function (id, show) {
        const modal = document.getElementById(id);
        if (!modal) return;
        const panel = modal.querySelector('[data-modal-panel]');

        modal.classList.toggle('opacity-0', !show);
        modal.classList.toggle('pointer-events-none', !show);
        modal.classList.toggle('opacity-100', show);

        if (panel) {
            panel.classList.toggle('scale-95', !show);
            panel.classList.toggle('translate-y-2', !show);
            panel.classList.toggle('scale-100', show);
            panel.classList.toggle('translate-y-0', show);
        }

        document.body.classList.toggle('overflow-hidden', show);
    };

    document.addEventListener('click', function (event) {
        const open = event.target.closest('[data-modal-open]');
        const close = event.target.closest('[data-modal-close]');

        if (open) return window.setModal(open.dataset.modalOpen, true);
        if (close) return window.setModal(close.dataset.modalClose, false);
        if (event.target.matches('[id^="modal"]')) window.setModal(event.target.id, false);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('[id^="modal"].opacity-100')
            .forEach((modal) => window.setModal(modal.id, false));
    });
})();
