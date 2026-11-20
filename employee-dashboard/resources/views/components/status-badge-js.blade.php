{{-- Shared status-pill renderer for JS-driven tables and detail pages.
     Colors come from StatusPalette so JS and Blade badges stay identical. --}}
<script>
    (function () {
        const STATUS_BADGE_PALETTE = @json(\App\Support\StatusPalette::all());

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, (character) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            }[character]));
        }

        window.statusBadgeKey = function (status) {
            const value = String(status ?? '').toLowerCase().trim();

            if (!value) return null;
            if (value.includes('tidak') && value.includes('aktif')) return 'tidak_aktif';
            if (value === 'aktif') return 'aktif';
            if (value === 'baru') return 'baru';
            if (value === 'berjalan') return 'berjalan';
            if (value === 'pending') return 'menunggu';
            if (value.includes('menunggu')) return 'menunggu';
            if (value === 'acc') return 'disetujui';
            if (value.includes('sudah') || value.includes('disetujui') || value.includes('selesai')) return 'disetujui';
            if (value === 'ditolak' || value === 'tolak') return 'ditolak';
            if (value === 'dicairkan') return 'dicairkan';
            if (value === 'terverifikasi') return 'terverifikasi';
            if (value.includes('revisi')) return 'perlu_revisi';
            if (value.includes('terlambat') || value.includes('telat')) return 'terlambat';

            return null;
        };

        window.statusBadgeHtml = function (status, options) {
            const config = Object.assign({ short: false, dot: true, label: null }, options || {});
            const key = window.statusBadgeKey(status);
            const palette = key ? STATUS_BADGE_PALETTE[key] : null;
            const text = config.label || (palette
                ? (config.short ? (palette.short || palette.label) : palette.label)
                : (status || '-'));

            if (!palette) {
                return '<span class="inline-flex w-fit items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] text-[#172B36]">'
                    + escapeHtml(text) + '</span>';
            }

            const dot = config.dot
                ? '<span class="h-1.5 w-1.5 shrink-0 rounded-full" style="background-color: ' + palette.dot + ';" aria-hidden="true"></span>'
                : '';

            return '<span class="inline-flex w-fit items-center gap-1.5 rounded-full border px-2 py-0.5 text-[11px] font-medium"'
                + ' style="background-color: ' + palette.bg + '; border-color: ' + palette.border + '; color: ' + palette.text + ';"'
                + ' title="' + escapeHtml(config.label || palette.label) + '">'
                + dot + escapeHtml(text) + '</span>';
        };

        window.statusBadgeRender = function (element, status, options) {
            if (!element) return;

            const holder = document.createElement('span');
            holder.innerHTML = window.statusBadgeHtml(status, options);
            const fresh = holder.firstElementChild;

            element.className = fresh.className;
            element.setAttribute('style', fresh.getAttribute('style') || '');
            element.setAttribute('title', fresh.getAttribute('title') || '');
            element.innerHTML = fresh.innerHTML;
            element.classList.remove('hidden');
        };
    })();
</script>
