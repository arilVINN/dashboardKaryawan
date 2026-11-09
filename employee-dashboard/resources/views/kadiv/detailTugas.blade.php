<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Tugas - PT Silindo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Detail Tugas (Kadiv): revision thread + submission column layout */
        .tugas-body { display: block; }
        .tugas-form-col { max-width: 640px; }

        .tugas-body.has-revisi .tugas-form-col {
            max-width: none;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #E2E8EC;
        }

        @media (min-width: 1100px) {
            .tugas-body.has-revisi {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 360px;
                align-items: start;
            }

            .tugas-body.has-revisi .tugas-thread {
                padding-right: 24px;
            }

            .tugas-body.has-revisi .tugas-form-col {
                margin-top: 0;
                padding-top: 0;
                padding-left: 24px;
                border-top: 0;
                border-left: 1px solid #E2E8EC;
            }

            .tugas-body.has-revisi #section-pengumpulan-inner {
                position: sticky;
                top: 24px;
            }
        }
    </style>
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

    {{-- SIDEBAR --}}
    @include('component_kadiv.sidebar')

    {{-- AREA KANAN --}}
    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        {{-- TOPBAR --}}
        @include('component.topbar')

        {{-- CONTENT --}}
        <main class="flex-1 overflow-y-auto px-6 pt-6 pb-8 sm:px-8">
            <div class="w-full">
                @include('component.breadcrumbs', ['parentText' => 'Daftar Tugas', 'parentUrl' => url('/kadiv/tugas'), 'currentPage' => 'Detail Tugas', 'inline' => true])

                {{-- HEADER --}}
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-3">
                            <h1 id="detail-task-title" class="text-2xl font-semibold leading-snug text-[#172B36]">Memuat tugas...</h1>
                            <span id="detail-task-badge" class="hidden"></span>
                        </div>
                        <p id="detail-task-meta" class="mt-1 text-sm text-[#4B5563]">-</p>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <button type="button" id="editTaskButton" data-modal-open="modalEditTugas" disabled
                                class="inline-flex h-10 items-center gap-2 rounded-lg bg-[#FACC15] px-4 text-sm font-semibold text-[#422006] transition hover:bg-[#EAB308] disabled:cursor-not-allowed disabled:opacity-50">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                            </svg>
                            Edit tugas
                        </button>

                        <button type="button" id="deleteTaskButton" disabled
                                class="inline-flex h-10 items-center gap-2 rounded-lg bg-[#DC2626] px-4 text-sm font-semibold text-white transition hover:bg-[#B91C1C] disabled:cursor-not-allowed disabled:opacity-50">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            Hapus tugas
                        </button>
                    </div>
                </div>

                <p id="detail-task-description" class="mt-4 text-sm font-normal leading-[1.6] text-[#172B36]">
                    Memuat deskripsi tugas...
                </p>

                <div id="task-support-card" class="mt-4 hidden flex-col gap-3"></div>

                {{-- GARIS PEMISAH --}}
                <hr class="mt-6 border-0 border-t border-[#E2E8EC]">

                {{-- BODY: RIWAYAT REVISI (kiri) + PENGUMPULAN (kanan) --}}
                <div id="tugas-body" class="tugas-body mt-6">

                    {{-- RIWAYAT REVISI --}}
                    <section id="backendRevision" class="tugas-thread hidden min-w-0">
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="text-lg font-semibold text-[#172B36]">Riwayat Revisi</h3>
                            <span id="revision-count" class="text-[13px] text-[#4B5563]"></span>
                        </div>
                        <div id="revision-history-list" class="mt-4 space-y-5"></div>
                    </section>

                    {{-- KOLOM KANAN: PENGUMPULAN + AKSI --}}
                    <div id="section-pengumpulan" class="tugas-form-col min-w-0">
                        <div id="section-pengumpulan-inner">

                            {{-- PENGUMPULAN --}}
                            <section>
                                <h2 class="text-lg font-semibold text-[#172B36]">Pengumpulan</h2>
                                <p id="detail-submission-meta" class="mt-1 text-sm text-[#4B5563]">-</p>
                                <p id="detail-submission-note" class="mt-4 text-sm leading-[1.6] text-[#172B36]">-</p>
                                <div id="task-submission-card" class="mt-4 hidden flex-col gap-3"></div>
                            </section>

                            {{-- GARIS PEMISAH --}}
                            <hr class="mt-6 border-0 border-t border-[#E2E8EC]">

                            {{-- AKSI REVIEW --}}
                            <div class="mt-6">
                                <div id="aksiTombol" class="hidden items-center gap-3">
                                    <button type="button" id="btnOpenRevisi" data-modal-open="modalRevisi"
                                            class="inline-flex h-10 items-center justify-center rounded-lg border border-[#D5DEE3] bg-white px-5 text-sm font-medium text-[#172B36] transition hover:bg-slate-50 cursor-pointer">
                                        Revisi
                                    </button>

                                    <button type="button" data-modal-open="modalKonfirmasiAcc"
                                            class="inline-flex h-10 items-center justify-center rounded-lg bg-[#146C94] px-5 text-sm font-medium text-white transition hover:bg-[#0f5878] cursor-pointer">
                                        Setujui
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </main>

    </div>

    {{-- MODAL EDIT TUGAS --}}
    <div id="modalEditTugas" class="hidden fixed inset-0 z-50 bg-[rgba(86,94,116,0.4)] flex items-center justify-center p-4">
        <form id="formEditTugas" class="bg-white rounded-xl w-full max-w-[420px] max-h-[90vh] flex flex-col shadow-[0_8px_16px_rgba(0,0,0,0.12)]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-[#E2E8F0] bg-[#F8FAFC] rounded-t-xl">
                <h3 class="text-lg font-semibold text-black">Edit Tugas</h3>
                <button type="button" data-modal-close="modalEditTugas" class="text-[#565E74] hover:text-black text-2xl leading-none cursor-pointer">&times;</button>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">
                <div>
                    <label for="editJudulTugas" class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">JUDUL TUGAS</label>
                    <input type="text" name="judul_tugas" id="editJudulTugas" required maxlength="100"
                           class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                </div>

                <div>
                    <label for="editPenerimaTugas" class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">PENERIMA</label>
                    <select name="karyawan_id_karyawan" id="editPenerimaTugas" required
                            class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] bg-white outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                        <option value="">Memuat staff...</option>
                    </select>
                </div>

                <div>
                    <label for="editDeskripsiTugas" class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">DESKRIPSI</label>
                    <textarea name="deskripsi" id="editDeskripsiTugas" rows="3" required
                              class="w-full resize-none rounded-lg border border-[#CBD5E1] px-3 py-2.5 text-sm text-[#283044] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]"></textarea>
                </div>

                <div>
                    <label for="editDeadlineTugas" class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">TENGGAT</label>
                    <input type="datetime-local" name="deadline" id="editDeadlineTugas" required
                           class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none cursor-pointer focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]">
                </div>
            </div>

            <div class="flex items-center justify-between px-6 py-4 border-t border-[#E2E8F0]">
                <button type="button" data-modal-close="modalEditTugas"
                        class="h-9 px-4 bg-[#E8E7E9] rounded-lg text-sm text-[#333335] hover:bg-[#D9D9D9] transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="h-9 px-4 bg-[#146C94] rounded-lg text-sm text-white font-bold hover:bg-[#0f5878] transition cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    {{-- MODAL KONFIRMASI ACC --}}
    <div id="modalKonfirmasiAcc"
         class="hidden fixed inset-0 z-50 bg-[rgba(86,94,116,0.4)] flex items-center justify-center p-4">

        <div class="bg-white rounded-xl w-full max-w-[400px] p-6 shadow-[0_8px_16px_rgba(0,0,0,0.12)]">

            <h3 class="text-xl font-bold text-black mb-2">Konfirmasi Acc</h3>

            <p class="text-sm text-[#565E74] mb-6">
                Apakah Anda yakin ingin menyetujui tugas ini?
            </p>

            <div class="flex items-center justify-end gap-3">

                <button type="button"
                        data-modal-close="modalKonfirmasiAcc"
                        class="h-9 px-4 bg-[#E8E7E9] rounded-lg text-[#333335] hover:bg-[#D9D9D9] transition cursor-pointer">
                    Batal
                </button>

                <button type="button"
                        id="confirmTaskAcc"
                        data-modal-close="modalKonfirmasiAcc"
                        class="h-9 px-4 bg-[#0E9DC3] rounded-lg text-white font-bold hover:bg-[#0c89aa] transition cursor-pointer">
                    Ya, Acc
                </button>

            </div>

        </div>
    </div>

    {{-- MODAL REVISI --}}
    <div id="modalRevisi"
         class="hidden fixed inset-0 z-50 bg-[rgba(86,94,116,0.4)] flex items-center justify-center p-4">
        <form id="formRevisi"
              action="#"
              method="POST"
              enctype="multipart/form-data"
              class="bg-white rounded-xl w-full max-w-[520px] max-h-[90vh] flex flex-col shadow-[0_8px_16px_rgba(0,0,0,0.12)]">
            @csrf

            <div class="flex items-center justify-between px-6 py-4 border-b border-[#E2E8F0] bg-[#F8FAFC] rounded-t-xl">
                <h3 class="text-lg font-semibold text-[#172B36]">Revisi</h3>
                <button type="button" data-modal-close="modalRevisi"
                        class="text-[#565E74] hover:text-black text-2xl leading-none cursor-pointer"
                        aria-label="Tutup">&times;</button>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-5">
                {{-- Catatan revisi --}}
                <div>
                    <label for="isi_revisi" class="block text-[13px] font-medium text-[#172B36]">
                        Catatan revisi <span class="text-[#DC2626]">*</span>
                    </label>
                    <textarea id="isi_revisi" name="isi_revisi" rows="4" required
                              placeholder="Jelaskan bagian yang perlu diperbaiki.."
                              class="mt-1.5 w-full resize-none rounded-lg border border-[#D5DEE3] bg-white px-3 py-2.5 text-sm text-[#172B36] outline-none transition placeholder:text-[#4B5563] focus:border-[#146C94] focus:ring-1 focus:ring-[#146C94]"></textarea>
                </div>

                {{-- Tenggat baru + jam --}}
                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-[minmax(0,1fr)_140px]">
                    <div>
                        <label for="tenggatTanggal" class="block text-[13px] font-medium text-[#172B36]">
                            Tenggat baru <span class="font-normal text-[#4B5563]">(opsional)</span>
                        </label>
                        <input type="date" id="tenggatTanggal" name="tenggat_tanggal"
                               class="mt-1.5 h-10 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-sm text-[#172B36] outline-none transition focus:border-[#146C94] focus:ring-1 focus:ring-[#146C94]">
                    </div>
                    <div>
                        <label for="tenggatJam" class="block text-[13px] font-medium text-[#172B36]">Jam</label>
                        <input type="time" id="tenggatJam" name="tenggat_jam"
                               class="mt-1.5 h-10 w-full rounded-lg border border-[#D5DEE3] bg-white px-3 text-sm text-[#172B36] outline-none transition focus:border-[#146C94] focus:ring-1 focus:ring-[#146C94]">
                    </div>
                </div>

                {{-- Lampiran --}}
                <div class="mt-5">
                    <label for="fileRevisi" class="block text-[13px] font-medium text-[#172B36]">
                        Lampiran <span class="font-normal text-[#4B5563]">(opsional)</span>
                    </label>
                    <div class="mt-1.5 flex h-10 items-center overflow-hidden rounded-lg border border-[#D5DEE3] bg-white">
                        <label for="fileRevisi"
                               class="flex h-full shrink-0 cursor-pointer items-center border-r border-[#D5DEE3] bg-slate-50 px-3 text-[13px] font-medium text-[#172B36] transition hover:bg-slate-100">
                            Pilih file
                        </label>
                        <span id="namaFileRevisi" class="ml-3 min-w-0 flex-1 truncate text-[13px] text-[#4B5563]">Belum ada file dipilih</span>
                    </div>
                    <input type="file" name="file_revisi" id="fileRevisi" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip" class="sr-only">
                </div>
            </div>

            <div class="border-t border-[#E2E8F0] px-6 py-4">
                <div id="revisiErrors" class="mt-3 hidden space-y-1 rounded-lg border border-[#F5C2C7] bg-[#FEF2F2] px-3 py-2 text-[13px] leading-5 text-[#B91C1C]" role="alert" aria-live="polite"></div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" data-modal-close="modalRevisi"
                            class="h-10 rounded-lg bg-[#E8E7E9] px-4 text-sm font-medium text-[#333335] transition hover:bg-[#D9D9D9] cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex h-10 items-center justify-center rounded-lg bg-[#146C94] px-5 text-sm font-medium text-white transition hover:bg-[#0f5878] cursor-pointer">
                        Kirim revisi
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- SCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            const token = sessionStorage.getItem('staff_token');
            const taskId = @json($id);
            const formRevisi = document.getElementById('formRevisi');
            const actions = document.getElementById('aksiTombol');
            const setActionsVisible = (visible) => {
                actions.classList.toggle('hidden', !visible);
                actions.style.display = visible ? 'flex' : 'none';
            };
            const formEditTugas = document.getElementById('formEditTugas');
            const editTaskButton = document.getElementById('editTaskButton');
            const editPenerima = document.getElementById('editPenerimaTugas');
            let currentTaskDeadline = null;
            const setText = (id, value) => {
                const element = document.getElementById(id);
                if (element) element.textContent = value || '-';
            };
            const pad2 = (value) => String(value).padStart(2, '0');
            const formatDate = (value) => {
                if (!value) return '-';
                const date = new Date(String(value).replace(' ', 'T'));
                if (Number.isNaN(date.getTime())) return '-';
                const day = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                return day + ', ' + pad2(date.getHours()) + ':' + pad2(date.getMinutes());
            };

            const splitDateTime = (value) => {
                if (!value) return { date: '', time: '' };
                const normalised = String(value).replace(' ', 'T');
                const match = normalised.match(/^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2})/);
                if (match) return { date: match[1], time: match[2] };
                const parsed = new Date(normalised);
                if (Number.isNaN(parsed.getTime())) return { date: '', time: '' };
                return {
                    date: parsed.getFullYear() + '-' + pad2(parsed.getMonth() + 1) + '-' + pad2(parsed.getDate()),
                    time: pad2(parsed.getHours()) + ':' + pad2(parsed.getMinutes()),
                };
            };

            const STATUS_PALETTE = @json(\App\Support\StatusPalette::all());

            function normalizeStatus(status) {
                const value = String(status || '').toLowerCase().trim();
                if (!value) return null;

                if (value.includes('tidak') && value.includes('aktif')) return 'tidak_aktif';
                if (value === 'aktif') return 'aktif';
                if (value === 'baru') return 'baru';
                if (value === 'berjalan') return 'berjalan';
                if (value.includes('menunggu')) return 'menunggu';
                if (value.includes('sudah') || value.includes('disetujui') || value.includes('selesai')) return 'disetujui';
                if (value.includes('terlambat') || value.includes('telat')) return 'terlambat';

                return null;
            }

            function renderStatusBadge(status) {
                const badge = document.getElementById('detail-task-badge');
                const key = normalizeStatus(status);
                const palette = key ? STATUS_PALETTE[key] : null;

                if (!palette) {
                    badge.textContent = '';
                    badge.className = 'hidden';
                    return;
                }

                badge.textContent = palette.label;
                badge.className = 'inline-flex w-fit items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold';
                badge.style.backgroundColor = palette.bg;
                badge.style.borderColor = palette.border;
                badge.style.color = palette.text;
            }

            const attachmentIconPaths = {
                document: 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
                text: 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
                table: 'M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125V6.375c0-.621.504-1.125 1.125-1.125h17.25c.621 0 1.125.504 1.125 1.125v12c0 .621-.504 1.125-1.125 1.125ZM3.375 9.75h17.25M9.75 9.75v9.75M15 9.75v9.75',
                image: 'm2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
                archive: 'm20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z',
                link: 'M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244',
            };
            const downloadIconSvg = '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>';

            function attachmentIcon(ext, isLink = false) {
                const icon = (path) => `<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="${path}" /></svg>`;

                if (isLink) return { color: 'text-[#146C94]', svg: icon(attachmentIconPaths.link) };
                if (ext === 'PDF') return { color: 'text-red-500', svg: icon(attachmentIconPaths.text) };
                if (['DOC', 'DOCX'].includes(ext)) return { color: 'text-blue-500', svg: icon(attachmentIconPaths.text) };
                if (['XLS', 'XLSX', 'CSV'].includes(ext)) return { color: 'text-emerald-600', svg: icon(attachmentIconPaths.table) };
                if (['JPG', 'JPEG', 'PNG', 'GIF', 'WEBP'].includes(ext)) return { color: 'text-violet-500', svg: icon(attachmentIconPaths.image) };
                if (['ZIP', 'RAR', '7Z'].includes(ext)) return { color: 'text-amber-600', svg: icon(attachmentIconPaths.archive) };

                return { color: 'text-[#64748B]', svg: icon(attachmentIconPaths.document) };
            }

            const formatSize = (bytes) => {
                const value = Number(bytes);
                if (!Number.isFinite(value) || value <= 0) return '';
                const kb = value / 1024;
                if (kb < 1024) return Math.round(kb) + ' KB';
                return (kb / 1024).toFixed(1).replace('.', ',') + ' MB';
            };

            function appendAttachmentRow(container, filePath, label, href = null, sizeBytes = null, displayName = null) {
                if (typeof filePath !== 'string' || !filePath.trim() || filePath.trim() === '-') return false;

                const value = filePath.trim();
                let targetUrl;
                try {
                    targetUrl = new URL(href || (/^https?:\/\//i.test(value) ? value : '/storage/' + value.replace(/^\/+/, '')), window.location.origin);
                } catch {
                    return false;
                }
                if (!['http:', 'https:'].includes(targetUrl.protocol)) return false;

                const isExternalLink = /^https?:\/\//i.test(value) && !href;
                const storedName = value.split(/[?#]/)[0].split('/').pop() || '';
                const originalName = typeof displayName === 'string' ? displayName.trim() : '';
                const looksHashed = /^[A-Za-z0-9]{24,}(?:\.[A-Za-z0-9]+)?$/.test(storedName);
                const fileName = isExternalLink
                    ? (label || 'Link Referensi')
                    : (originalName || (!looksHashed ? storedName : '') || label || 'Lampiran');
                const extension = isExternalLink
                    ? 'Tautan'
                    : (storedName.includes('.') ? storedName.split('.').pop().toUpperCase() : 'FILE');
                const sizeLabel = formatSize(sizeBytes);
                const metaParts = isExternalLink ? ['Tautan'] : [extension, sizeLabel];

                const row = document.createElement('a');
                row.href = targetUrl.href;
                row.target = '_blank';
                row.rel = 'noopener noreferrer';
                if (!isExternalLink) row.setAttribute('download', fileName);
                row.className = 'group flex w-[320px] max-w-full items-center gap-3 rounded-lg bg-white px-3 py-2.5 shadow-sm transition hover:shadow-md';

                const icon = attachmentIcon(extension, isExternalLink);
                const iconTile = document.createElement('span');
                iconTile.className = 'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#F1F5F9] ' + icon.color;
                iconTile.innerHTML = icon.svg;

                const details = document.createElement('span');
                details.className = 'min-w-0 flex-1';

                const name = document.createElement('span');
                name.className = 'block truncate text-sm font-medium text-[#172B36]';
                name.textContent = fileName;

                const meta = document.createElement('span');
                meta.className = 'block truncate text-[12px] leading-5 text-[#4B5563]';
                meta.textContent = metaParts.filter(Boolean).join(' · ');

                details.append(name, meta);

                const action = document.createElement('span');
                action.className = 'flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-[#4B5563] transition group-hover:text-[#146C94]';
                action.innerHTML = downloadIconSvg;
                action.setAttribute('aria-hidden', 'true');

                row.append(iconTile, details, action);
                container.appendChild(row);
                return true;
            }

            const revisiErrors = document.getElementById('revisiErrors');

            function clearRevisiErrors() {
                revisiErrors.replaceChildren();
                revisiErrors.classList.add('hidden');
            }

            function showRevisiErrors(errorOrPayload, fallbackMessage) {
                const messages = [];
                const fieldErrors = (errorOrPayload?.errors && typeof errorOrPayload.errors === 'object')
                    ? errorOrPayload.errors
                    : ((errorOrPayload?.data && typeof errorOrPayload.data === 'object') ? errorOrPayload.data : null);

                if (fieldErrors) {
                    Object.values(fieldErrors).forEach((value) => {
                        (Array.isArray(value) ? value : [value]).forEach((message) => {
                            if (message) messages.push(String(message));
                        });
                    });
                }

                if (!messages.length) {
                    messages.push(errorOrPayload?.message || fallbackMessage || 'Permintaan tugas gagal.');
                }

                revisiErrors.replaceChildren();
                messages.forEach((message) => {
                    const line = document.createElement('p');
                    line.textContent = message;
                    revisiErrors.appendChild(line);
                });
                revisiErrors.classList.remove('hidden');
            }

            async function requestTask(method, payload) {
                const headers = {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                };
                if (!(payload instanceof FormData)) {
                    headers['Content-Type'] = 'application/json';
                }
                const response = await fetch('/api/kadiv/tugas/' + encodeURIComponent(taskId) + (method === 'POST' ? '/review' : ''), {
                    method,
                    headers,
                    ...(payload ? { body: payload instanceof FormData ? payload : JSON.stringify(payload) } : {})
                });

                let result = {};
                try {
                    result = await response.json();
                } catch {
                    result = {};
                }

                if (!response.ok) {
                    const error = new Error(result.message
                        || (response.status >= 500
                            ? 'Server gagal memproses permintaan. Silakan coba lagi.'
                            : 'Permintaan tugas gagal.'));
                    error.status = response.status;
                    error.errors = result.errors || {};
                    error.data = result.data || null;
                    throw error;
                }

                return result;
            }

            async function loadStaffOptions() {
                const response = await fetch('/api/kadiv/staff', {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Gagal memuat daftar staff.');

                editPenerima.replaceChildren();
                (result.data || []).forEach((staff) => {
                    if (staff.user?.role?.nama_role?.toLowerCase() === 'staff') {
                        editPenerima.add(new Option(staff.nama, staff.id_karyawan));
                    }
                });
            }

            async function updateTask(payload) {
                const response = await fetch('/api/kadiv/tugas/' + encodeURIComponent(taskId), {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                    body: payload
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Gagal mengubah tugas.');
                return result;
            }

            async function deleteTask() {
                const response = await fetch('/api/kadiv/tugas/' + encodeURIComponent(taskId), {
                    method: 'DELETE',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Gagal menghapus tugas.');
                return result;
            }

            async function loadTaskDetail() {
                if (!token) {
                    window.location.href = '/login';
                    return;
                }
                try {
                    const response = await fetch('/api/kadiv/tugas/' + encodeURIComponent(taskId), {
                        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || 'Gagal memuat tugas.');
                    const task = result.data;
                    const submissions = Array.isArray(task.submit_tugas) ? task.submit_tugas : [];
                    const submission = submissions[0];
                    document.getElementById('editJudulTugas').value = task.judul_tugas || '';
                    document.getElementById('editDeskripsiTugas').value = task.deskripsi || '';
                    document.getElementById('editDeadlineTugas').value = task.deadline
                        ? String(task.deadline).replace(' ', 'T').slice(0, 16)
                        : '';
                    const recipientId = String(task.karyawan_id_karyawan || task.karyawan?.id_karyawan || '');
                    if (recipientId && !Array.from(editPenerima.options).some((option) => option.value === recipientId)) {
                        editPenerima.add(new Option(task.karyawan?.nama || 'Staff saat ini', recipientId));
                    }
                    editPenerima.value = recipientId;
                    editTaskButton.disabled = task.status === 'sudah di-acc';
                    const deleteTaskButton = document.getElementById('deleteTaskButton');
                    deleteTaskButton.disabled = false;
                    const staffName = task.karyawan?.nama || 'Staff';

                    setText('detail-task-title', task.judul_tugas);
                    renderStatusBadge(task.status_efektif || task.status);
                    setText('detail-task-meta', staffName + ' · ' + formatDate(task.deadline));
                    setText('detail-task-description', task.deskripsi);

                    const crumb = document.querySelector('[data-breadcrumb-current]');
                    if (crumb) crumb.textContent = task.judul_tugas || 'Detail Tugas';

                    setText('detail-submission-meta', submission
                        ? 'Dikirim oleh ' + staffName + ' · ' + formatDate(submission.created_at || submission.tanggal_submit)
                        : 'Belum ada pengumpulan');

                    const noteEl = document.getElementById('detail-submission-note');
                    const submissionNote = typeof submission?.catatan_karyawan === 'string'
                        ? submission.catatan_karyawan.trim()
                        : '';
                    if (submissionNote && submissionNote !== '-') {
                        noteEl.textContent = submissionNote;
                        noteEl.classList.remove('hidden');
                    } else {
                        noteEl.textContent = '';
                        noteEl.classList.add('hidden');
                    }

                    const taskSupportCard = document.getElementById('task-support-card');
                    taskSupportCard.replaceChildren();
                    const taskFileName = typeof task.file_pendukung === 'string'
                        ? task.file_pendukung.trim()
                        : '';
                    const taskLink = typeof task.link_pendukung === 'string'
                        ? task.link_pendukung.trim()
                        : '';
                    const hasTaskFile = appendAttachmentRow(
                        taskSupportCard,
                        taskFileName,
                        'Lampiran tugas',
                        taskFileName && taskFileName !== '-'
                            ? '/storage/' + taskFileName.replace(/^\/+/, '')
                            : null,
                        task.file_pendukung_size,
                        task.file_pendukung_name
                    );
                    const hasTaskLink = appendAttachmentRow(taskSupportCard, taskLink, 'Link Referensi');
                    taskSupportCard.classList.toggle('hidden', !(hasTaskFile || hasTaskLink));
                    taskSupportCard.classList.toggle('flex', hasTaskFile || hasTaskLink);

                    const submissionCard = document.getElementById('task-submission-card');
                    submissionCard.replaceChildren();
                    const submissionFileName = typeof submission?.file_hasil === 'string'
                        ? submission.file_hasil.trim()
                        : '';
                    const submissionLink = typeof submission?.link_submit === 'string'
                        ? submission.link_submit.trim()
                        : '';
                    const hasSubmissionFile = appendAttachmentRow(
                        submissionCard,
                        submissionFileName,
                        'File hasil',
                        submissionFileName && submissionFileName !== '-'
                            ? '/storage/' + submissionFileName.replace(/^\/+/, '')
                            : null,
                        submission?.file_hasil_size,
                        submission?.file_hasil_name
                    );
                    const hasSubmissionLink = appendAttachmentRow(submissionCard, submissionLink, 'Link Pengumpulan');
                    submissionCard.classList.toggle('hidden', !(hasSubmissionFile || hasSubmissionLink));
                    submissionCard.classList.toggle('flex', hasSubmissionFile || hasSubmissionLink);

                    const revisions = submissions.filter((item) =>
                        item.status_review === 'revisi'
                        && typeof item.catatan_revisi === 'string'
                        && item.catatan_revisi.trim() !== ''
                        && item.catatan_revisi.trim() !== '-'
                    );
                    const history = document.getElementById('revision-history-list');
                    history.replaceChildren();
                    if (revisions.length) {
                        setText('revision-count', revisions.length + ' kali revisi');
                        revisions.forEach((revision, index) => {
                            const item = document.createElement('div');

                            // Submissions arrive newest-first, so the oldest
                            // revision keeps number 1.
                            const revisionNumber = revisions.length - index;

                            const headingRow = document.createElement('div');
                            headingRow.className = 'flex items-center gap-2';

                            const badge = document.createElement('span');
                            badge.className = 'flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#E6F2F7] text-[12px] font-semibold text-[#146C94]';
                            badge.textContent = String(revisionNumber);

                            const heading = document.createElement('p');
                            heading.className = 'text-[14px] font-semibold text-[#172B36]';
                            heading.textContent = 'Catatan revisi';

                            headingRow.append(badge, heading);
                            item.appendChild(headingRow);

                            const date = document.createElement('p');
                            date.className = 'text-[13px] leading-5 text-[#4B5563]';
                            date.textContent = formatDate(revision.created_at || revision.tanggal_submit);
                            item.appendChild(date);

                            const note = document.createElement('p');
                            note.className = 'mt-2 text-sm leading-[1.6] text-[#172B36]';
                            note.textContent = revision.catatan_revisi;
                            item.appendChild(note);

                            const revisionFile = typeof revision.file_revisi === 'string'
                                ? revision.file_revisi.trim()
                                : '';
                            if (revisionFile && revisionFile !== '-') {
                                const revisionAttachment = document.createElement('div');
                                revisionAttachment.className = 'mt-3 flex flex-col gap-3';
                                appendAttachmentRow(
                                    revisionAttachment,
                                    revisionFile,
                                    'Lampiran revisi',
                                    '/storage/' + revisionFile.replace(/^\/+/, ''),
                                    revision.file_revisi_size,
                                    revision.file_revisi_name
                                );
                                item.appendChild(revisionAttachment);
                            }

                            history.appendChild(item);
                        });
                        document.getElementById('backendRevision').classList.remove('hidden');
                    } else {
                        document.getElementById('backendRevision').classList.add('hidden');
                    }

                    // Two-column layout only when there is a revision thread
                    // to sit on the left, matching the staff detail page.
                    document.getElementById('tugas-body')
                        .classList.toggle('has-revisi', revisions.length > 0);

                    const canReview = submission?.status_review === 'submitted'
                        && task.status !== 'sudah di-acc';
                    setActionsVisible(canReview);

                    // Prefill tenggat revisi dengan deadline tugas saat ini.
                    currentTaskDeadline = task.deadline || null;
                    const defaultDeadline = splitDateTime(currentTaskDeadline);
                    document.getElementById('tenggatTanggal').value = defaultDeadline.date;
                    document.getElementById('tenggatJam').value = defaultDeadline.time || '17:00';
                    setText('revisiPenerima', staffName);
                } catch (error) {
                    console.error('Gagal memuat detail tugas Kadiv:', error);
                    setText('detail-task-title', error.message);
                }
            }

            if (!token) {
                window.location.href = '/login';
                return;
            }

            try {
                await loadStaffOptions();
            } catch (error) {
                console.error('Gagal memuat penerima tugas:', error);
                editPenerima.replaceChildren(new Option('Staff gagal dimuat', ''));
            }
            await loadTaskDetail();

            const closeModal = (modal) => {
                if (!modal) return;

                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');

                if (modal.id === 'modalRevisi') {
                    formRevisi.reset();
                    document.getElementById('namaFileRevisi').textContent = 'Belum ada file dipilih';
                    clearRevisiErrors();
                }
            };

            // Modal open
            document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const t = document.getElementById(this.dataset.modalOpen);
                    if (!t) return;

                    t.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');

                    if (t.id === 'modalRevisi') {
                        clearRevisiErrors();

                        // Re-apply the task deadline when the modal is reopened
                        // after a reset.
                        const fallback = splitDateTime(currentTaskDeadline);
                        const dateInput = t.querySelector('#tenggatTanggal');
                        const timeInput = t.querySelector('#tenggatJam');
                        if (dateInput && !dateInput.value) dateInput.value = fallback.date;
                        if (timeInput && !timeInput.value) timeInput.value = fallback.time || '17:00';

                        t.querySelector('textarea')?.focus();
                    }
                });
            });

            // Modal close
            document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    closeModal(document.getElementById(this.dataset.modalClose));
                });
            });

            // Klik area overlay → tutup modal
            document.querySelectorAll('#modalKonfirmasiAcc, #modalRevisi').forEach(function (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) {
                        closeModal(modal);
                    }
                });
            });

            document.getElementById('fileRevisi').addEventListener('change', function () {
                const file = this.files[0];
                clearRevisiErrors();
                if (file && file.size > 200 * 1024 * 1024) {
                    showRevisiErrors(null, 'Ukuran file maksimal 200 MB.');
                    this.value = '';
                    document.getElementById('namaFileRevisi').textContent = 'Belum ada file dipilih';
                    return;
                }
                document.getElementById('namaFileRevisi').textContent = file?.name || 'Belum ada file dipilih';
            });

            formRevisi.addEventListener('input', function (event) {
                if (event.target.matches('textarea, input')) clearRevisiErrors();
            });

            formRevisi.addEventListener('submit', async function (event) {
                event.preventDefault();
                clearRevisiErrors();

                const note = this.elements.isi_revisi.value.trim();
                if (!note) {
                    showRevisiErrors(null, 'Catatan revisi wajib diisi.');
                    this.elements.isi_revisi.focus();
                    return;
                }

                // Tenggat baru opsional: fallback ke deadline tugas saat ini.
                const dateValue = this.elements.tenggat_tanggal.value;
                const timeValue = this.elements.tenggat_jam.value || '17:00';
                const fallback = splitDateTime(currentTaskDeadline);
                const deadlineDate = dateValue || fallback.date;
                const deadlineTime = dateValue ? timeValue : (fallback.time || '17:00');

                if (!deadlineDate) {
                    showRevisiErrors(null, 'Tenggat revisi wajib ditentukan.');
                    this.elements.tenggat_tanggal.focus();
                    return;
                }

                const payload = new FormData();
                payload.append('status_review', 'revisi');
                payload.append('catatan_revisi', note);
                payload.append('deadline', deadlineDate + 'T' + deadlineTime);
                const file = this.elements.file_revisi.files[0];
                if (file) payload.append('file_revisi', file);

                const submitButton = this.querySelector('button[type="submit"]');
                const originalLabel = submitButton.textContent;
                submitButton.disabled = true;
                submitButton.textContent = 'Mengirim...';

                try {
                    await requestTask('POST', payload);
                    closeModal(document.getElementById('modalRevisi'));
                    await loadTaskDetail();
                    alert('Revisi berhasil dikirim.');
                } catch (error) {
                    showRevisiErrors(error, 'Gagal mengirim revisi. Silakan coba lagi.');
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = originalLabel;
                }
            });

            formEditTugas.addEventListener('submit', async function (event) {
                event.preventDefault();
                const payload = new FormData(formEditTugas);
                payload.append('_method', 'PUT');
                try {
                    await updateTask(payload);
                    document.getElementById('modalEditTugas').classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                    await loadTaskDetail();
                    alert('Tugas berhasil diperbarui.');
                } catch (error) {
                    alert(error.message);
                }
            });

            document.getElementById('deleteTaskButton').addEventListener('click', async function () {
                if (!confirm('Yakin ingin menghapus tugas ini beserta riwayat pengumpulannya?')) {
                    return;
                }

                this.disabled = true;
                try {
                    await deleteTask();
                    alert('Tugas berhasil dihapus.');
                    window.location.href = '/kadiv/tugas';
                } catch (error) {
                    this.disabled = false;
                    alert(error.message);
                }
            });

            document.getElementById('confirmTaskAcc').addEventListener('click', async function () {
                try {
                    await requestTask('POST', { status_review: 'acc' });
                    await loadTaskDetail();
                    alert('Tugas berhasil di-ACC.');
                } catch (error) {
                    alert(error.message);
                }
            });
        });
    </script>

</body>
</html>
