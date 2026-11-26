@once
<script>
    (function () {
        const root = document.querySelector('[data-message-conversation]');
        if (!root || root.dataset.bound === '1') return;
        root.dataset.bound = '1';

        const input = root.querySelector('[data-reply-input]');
        const submit = root.querySelector('[data-reply-submit]');
        const errorBox = root.querySelector('[data-reply-error]');
        const appended = root.querySelector('[data-appended-messages]');
        const thread = root.querySelector('[data-thread]');

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, (c) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[c]));
        }

        function sizeTextarea() {
            if (!input) return;
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 186) + 'px';
        }

        function setSubmitState() {
            if (!submit || !input) return;
            submit.disabled = input.value.trim() === '' || submit.dataset.loading === '1';
        }

        function scrollToBottom() {
            const main = root.closest('main');
            if (main) main.scrollTop = main.scrollHeight;
            else window.scrollTo(0, document.body.scrollHeight);
        }

        function formatDate(value) {
            const date = value ? new Date(value) : new Date();
            if (Number.isNaN(date.getTime())) return '';

            const parts = Object.fromEntries(
                new Intl.DateTimeFormat('en-GB', {
                    timeZone: 'Asia/Jakarta', day: 'numeric', month: 'numeric', year: 'numeric',
                    hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
                }).formatToParts(date).filter((part) => part.type !== 'literal')
                    .map((part) => [part.type, part.value])
            );
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            return `${parts.day} ${months[Number(parts.month) - 1]} ${parts.year}, ${parts.hour}:${parts.minute}`;
        }

        function appendReply(payload) {
            if (!appended) return;
            const name = payload?.pengirim?.nama || root.dataset.currentName || 'Anda';
            const foto = payload?.pengirim?.foto_url || root.dataset.currentFoto || '';
            const body = payload?.deskripsi ?? input.value;
            const time = formatDate(payload?.created_at);
            const initials = (name.trim().charAt(0) || 'A').toUpperCase();

            const article = document.createElement('article');
            article.className = 'flex gap-3 border-b border-[#E2E8EC] py-4';
            article.innerHTML =
                '<span class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#AFD3E2] text-[12px] font-semibold text-[#0B4A66]">' +
                    (foto ? '<img src="' + escapeHtml(foto) + '" alt="" class="h-8 w-8 rounded-full object-cover">' : escapeHtml(initials)) +
                '</span>' +
                '<div class="min-w-0 flex-1">' +
                    '<div class="flex flex-wrap items-start justify-between gap-x-3 gap-y-1">' +
                        '<div class="flex items-center gap-2">' +
                            '<span class="text-[14px] font-semibold text-[#172B36]">' + escapeHtml(name) + '</span>' +
                            '<span class="inline-flex rounded-full bg-[#E3F1F7] px-2 py-0.5 text-[11px] text-[#0B4A66]">Anda</span>' +
                        '</div>' +
                        '<time class="text-[12px] text-[#4B5563]">' + escapeHtml(time) + '</time>' +
                    '</div>' +
                    '<p class="mt-1 whitespace-pre-wrap break-words text-[14px] leading-[1.6] text-[#172B36]">' + escapeHtml(body) + '</p>' +
                '</div>';
            appended.appendChild(article);
        }

        if (input) {
            input.addEventListener('input', function () {
                sizeTextarea();
                setSubmitState();
                if (errorBox) {
                    errorBox.classList.add('hidden');
                    errorBox.textContent = '';
                }
            });
            input.addEventListener('keydown', function (event) {
                if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
                    event.preventDefault();
                    if (submit && !submit.disabled) submit.click();
                }
            });
            sizeTextarea();
        }

        const olderButton = root.querySelector('[data-show-older]');
        if (olderButton) {
            olderButton.addEventListener('click', function () {
                thread.querySelectorAll('[data-message-item].hidden').forEach((item) => item.classList.remove('hidden'));
                olderButton.remove();
            });
        }

        if (submit && input) {
            submit.addEventListener('click', async function () {
                const body = input.value.trim();
                if (!body || submit.dataset.loading === '1') return;

                submit.dataset.loading = '1';
                submit.disabled = true;
                const label = root.querySelector('[data-reply-label]');
                if (label) label.textContent = 'Mengirim...';

                const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json' };
                if (root.dataset.token) headers['Authorization'] = 'Bearer ' + root.dataset.token;
                if (root.dataset.csrf) headers['X-CSRF-TOKEN'] = root.dataset.csrf;

                try {
                    const response = await fetch(root.dataset.endpoint.replace('__ID__', encodeURIComponent(root.dataset.messageId)), {
                        method: 'POST',
                        headers: headers,
                        body: JSON.stringify({ deskripsi: body })
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || 'Balasan gagal dikirim.');

                    appendReply(result.data || result);
                    input.value = '';
                    sizeTextarea();
                    setSubmitState();
                    scrollToBottom();
                    if (window.appToast) window.appToast('Balasan terkirim', 'success');
                } catch (error) {
                    if (errorBox) {
                        errorBox.textContent = error.message || 'Balasan gagal dikirim.';
                        errorBox.classList.remove('hidden');
                    }
                } finally {
                    delete submit.dataset.loading;
                    if (label) label.textContent = label.dataset.defaultLabel;
                    setSubmitState();
                }
            });
        }
    })();
</script>
@endonce
