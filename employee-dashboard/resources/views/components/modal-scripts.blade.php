<script>
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

        if (open) return setModal(open.dataset.modalOpen, true);
        if (close) return setModal(close.dataset.modalClose, false);
        if (event.target.matches('[id^="modal"]')) setModal(event.target.id, false);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        document.querySelectorAll('[id^="modal"].opacity-100').forEach(function (modal) {
            setModal(modal.id, false);
        });
    });
})();
</script>
