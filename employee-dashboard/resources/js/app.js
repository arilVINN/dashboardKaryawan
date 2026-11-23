import './bootstrap';

let isRedirectingToLogin = false;

function handleUnauthorizedSession() {
    if (window.location.pathname === '/login' || isRedirectingToLogin) {
        return;
    }
    isRedirectingToLogin = true;
    sessionStorage.removeItem('staff_token');
    localStorage.removeItem('staff_token');
    window.location.href = '/login';
}

window.handleUnauthorizedSession = handleUnauthorizedSession;

const originalFetch = window.fetch;
window.fetch = async function() {
    const response = await originalFetch.apply(this, arguments);
    if (response.status === 401 || response.status === 419) {
        handleUnauthorizedSession();
    }
    return response;
};

// Tangani sesi kedaluwarsa (419 / 401) pada komponen Livewire secara otomatis
document.addEventListener('livewire:init', () => {
    if (window.Livewire) {
        window.Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if (status === 419 || status === 401) {
                    if (typeof preventDefault === 'function') preventDefault();
                    handleUnauthorizedSession();
                }
            });
        });
    }
});

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

// Global Centered Page Loading Overlay for page transitions
(function () {
    let loaderOverlay = null;

    function getLoaderOverlay() {
        if (!loaderOverlay) {
            loaderOverlay = document.createElement('div');
            loaderOverlay.id = 'global-centered-loader';
            loaderOverlay.style.cssText = 'position:fixed;inset:0;z-index:99999;display:none;align-items:center;justify-content:center;background-color:rgba(15,23,42,0.25);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);transition:opacity 0.2s ease-out;';
            loaderOverlay.innerHTML = `
                <div style="display:flex;flex-direction:column;align-items:center;gap:12px;padding:24px 28px;background:rgba(255,255,255,0.95);border-radius:16px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);border:1px solid rgba(226,232,240,0.8);">
                    <svg style="width:36px;height:36px;animation:spin 1s linear infinite;color:#146C94;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span style="font-size:13.5px;font-weight:600;color:#172B36;font-family:inherit;">Memuat halaman...</span>
                </div>
            `;
            document.body.appendChild(loaderOverlay);
        }
        return loaderOverlay;
    }

    function showLoader() {
        const overlay = getLoaderOverlay();
        overlay.style.display = 'flex';
        overlay.style.opacity = '1';
    }

    function hideLoader() {
        const overlay = getLoaderOverlay();
        overlay.style.opacity = '0';
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 200);
    }

    document.addEventListener('click', function (e) {
        const link = e.target.closest('a');
        if (!link) return;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.hasAttribute('download') || link.target === '_blank') return;
        if (link.origin !== window.location.origin) return;
        if (e.ctrlKey || e.metaKey || e.shiftKey) return;
        showLoader();
    });

    document.addEventListener('submit', function (e) {
        if (!e.target.hasAttribute('wire:submit')) {
            showLoader();
        }
    });

    window.addEventListener('beforeunload', showLoader);
    window.addEventListener('pageshow', hideLoader);
})();
