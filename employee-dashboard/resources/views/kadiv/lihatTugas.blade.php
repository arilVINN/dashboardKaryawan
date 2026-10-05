<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Tugas - PT SILINDO</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white flex h-screen overflow-hidden">

    {{-- SIDEBAR --}}
    @include('component_kadiv.sidebar')

    {{-- AREA KANAN --}}
    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        {{-- TOPBAR --}}
        @include('component_kadiv.topbar')

        {{-- CONTENT --}}
        <main class="flex-1 overflow-y-auto">
            <div class="px-9 pt-7 pb-16 max-w-[1134px]">

                {{-- TUGAS 1 --}}
                <section>
                    <h1 class="text-[28px] leading-[36px] font-bold text-black">Tugas 1</h1>

                    <h2 class="mt-6 text-[28px] leading-[36px] font-bold text-black">lorem ipsum</h2>

                    <p class="text-[16px] leading-[24px] font-bold text-black">
                        tenggat : 21 sep 2026, 16.00
                    </p>
                    <p class="text-[16px] leading-[24px] font-bold text-black">
                        status : on going
                    </p>

                    <p class="mt-10 text-[16px] leading-[24px] text-black">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                        Quisque pharetra ut lectus vel luctus. Aenean pellentesque
                        sapien placerat justo tincidunt, sit amet laoreet lectus
                        dapibus. Etiam fermentum erat faucibus, auctor nisi vitae,
                        aliquet quam. Cras eget lacus et mauris gravida aliquet.
                        Proin auctor arcu nec dapibus accumsan. Quisque nec mauris
                        leo. Pellentesque eu pellentesque arcu, ac varius diam.
                        Phasellus a libero sem. Pellentesque placerat at odio eu
                        tempor. Ut non eros tortor. Aenean tincidunt sit amet risus
                        vel imperdiet. Vestibulum posuere facilisis urna, quis
                        pulvinar nisl porttitor ut. Ut sollicitudin ullamcorper eros.
                    </p>

                    {{-- FILE DOCX --}}
                    <div class="mt-6 w-[299px] h-[62px] bg-white rounded-[10px] border border-black shadow-[0px_4px_4px_0px_rgba(0,0,0,0.3)] flex items-center relative overflow-hidden">
                        <div class="pl-7 flex flex-col">
                            <span class="text-black text-[16px] leading-[24px] font-bold underline">
                                Tugas 1 Divisi Writer
                            </span>
                            <span class="text-black/40 text-[16px] leading-[24px] font-bold">
                                docx
                            </span>
                        </div>

                        <div class="absolute right-5 top-1/2 -translate-y-1/2 w-9 h-9 rounded bg-[#146C94] flex items-center justify-center text-white font-bold text-xs">
                            W
                        </div>
                    </div>
                </section>

                {{-- GARIS PEMISAH --}}
                <div class="mt-10 border-t border-black"></div>

                {{-- TUGAS KEDUA --}}
                <section class="pt-4">
                    <h2 class="text-[24px] leading-[32px] font-bold text-black">tugas1</h2>

                    <p class="text-[16px] leading-[24px] font-bold text-black">
                        tenggat : 21 sep 2026, 16.00
                    </p>
                    <p class="text-[16px] leading-[24px] font-bold text-black">
                        status : on going
                    </p>

                    <div class="mt-5 grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_299px] gap-6 items-start">

                        <div>
                            <p class="text-[16px] leading-[24px] text-black">
                                buk ini untuk tugas kemarin
                            </p>
                        </div>

                        {{-- FILE PDF --}}
                        <div class="w-[299px] h-[62px] bg-white rounded-[10px] border border-black shadow-[0px_4px_4px_0px_rgba(0,0,0,0.3)] flex items-center relative overflow-hidden lg:justify-self-end">
                            <div class="pl-7 flex flex-col">
                                <span class="text-black text-[16px] leading-[24px] font-bold underline">
                                    Tugas 1 Divisi Writer
                                </span>
                                <span class="text-black/40 text-[16px] leading-[24px] font-bold">
                                    Pdf
                                </span>
                            </div>

                            <div class="absolute right-5 top-1/2 -translate-y-1/2 w-9 h-9 rounded bg-[#D22B2B] flex items-center justify-center text-white font-bold text-[9px]">
                                PDF
                            </div>
                        </div>

                    </div>
                </section>

                {{-- DAFTAR REVISI (dari session) --}}
@php
    $semuaRevisi = collect(session('revisi_tugas', []))
        ->where('id_tugas', $id)
        ->values();
@endphp

@if ($semuaRevisi->count() > 0)
    <div class="mt-8 border-t border-black pt-4">

        {{-- Header Riwayat --}}
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-[18px] leading-[26px] font-bold text-black">
                Riwayat Revisi
            </h3>
            <span class="text-sm text-[#565E74]">
                {{ $semuaRevisi->count() }} kali revisi
            </span>
        </div>

        {{-- Container scroll internal --}}
        <div class="max-h-[500px] overflow-y-auto pr-2 space-y-6">

            @foreach ($semuaRevisi as $revisi)
                <div>
                    <h2 class="text-[24px] leading-[32px] font-bold text-black">
                        Revisi ke-{{ $loop->iteration }}
                    </h2>

                    <p class="text-[14px] leading-[20px] text-[#565E74]">
                        {{ $revisi['waktu'] }}
                    </p>

                    <p class="text-[16px] leading-[24px] font-bold text-black mt-1">
                        tenggat : {{ $revisi['tenggat'] ?: '21 sep 2026, 16.00' }}
                    </p>
                    <p class="text-[16px] leading-[24px] font-bold text-black">
                        status : on going
                    </p>

                    <div class="mt-5 grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_299px] gap-6 items-start">

                        <div>
                            <p class="text-[16px] leading-[24px] text-black">
                                {{ $revisi['isi'] }}
                            </p>
                        </div>

                        {{-- FILE PDF --}}
                        <div class="w-[299px] h-[62px] bg-white rounded-[10px] border border-black shadow-[0px_4px_4px_0px_rgba(0,0,0,0.3)] flex items-center relative overflow-hidden lg:justify-self-end">
                            <div class="pl-7 flex flex-col">
                                <span class="text-black text-[16px] leading-[24px] font-bold underline">
                                    Tugas 1 Divisi Writer
                                </span>
                                <span class="text-black/40 text-[16px] leading-[24px] font-bold">
                                    Pdf
                                </span>
                            </div>

                            <div class="absolute right-5 top-1/2 -translate-y-1/2 w-9 h-9 rounded bg-[#D22B2B] flex items-center justify-center text-white font-bold text-[9px]">
                                PDF
                            </div>
                        </div>

                    </div>

                    {{-- Garis pemisah antar revisi --}}
                    @if (!$loop->last)
                        <div class="mt-6 border-t border-dashed border-gray-300"></div>
                    @endif

                </div>
            @endforeach

        </div>

        </div>
        @endif

                {{-- AKSI / FORM REVISI --}}
                <div class="mt-8">

                    {{-- STATE 1: TOMBOL --}}
                    <div id="aksiTombol" class="flex items-center gap-5">

                        <button type="button"
                                data-toggle-form="formRevisi"
                                class="w-[148px] h-9 rounded-[9px] bg-[#D22B2B] flex items-center justify-center text-white text-[16px] leading-[24px] hover:bg-[#b92323] transition cursor-pointer">
                            Revisi
                        </button>

                        <button type="button"
                                data-modal-open="modalKonfirmasiAcc"
                                class="w-[148px] h-9 rounded-[9px] bg-[#0E9DC3] flex items-center justify-center text-white text-[16px] leading-[24px] hover:bg-[#0c89aa] transition cursor-pointer">
                            Acc
                        </button>

                    </div>

                    {{-- STATE 2: FORM REVISI (hidden by default) --}}
<form id="formRevisi"
      action="{{ route('kadiv.revisiTugas', $id) }}"
      method="POST"
      enctype="multipart/form-data"
      class="hidden">

    @csrf

    <div class="max-w-[820px]">

        <div class="border border-gray-300 rounded-lg p-5 bg-white space-y-5">

            {{-- Textarea catatan --}}
            <div>
                <textarea name="isi_revisi"
                          rows="4"
                          placeholder="Tulis catatan revisi..."
                          class="w-full resize-none rounded-lg border border-[#CBD5E1] px-3 py-2.5 text-sm text-[#283044] placeholder:text-[#94A3B8] outline-none focus:border-[#19A7CE] focus:ring-1 focus:ring-[#19A7CE]"></textarea>
            </div>

            {{-- Kartu file yang direvisi --}}
            <div class="w-[299px] h-[62px] bg-white rounded-[10px] border border-black shadow-[0px_4px_4px_0px_rgba(0,0,0,0.3)] flex items-center relative overflow-hidden">
                <div class="pl-7 flex flex-col">
                    <span class="text-black text-[15px] leading-[22px] font-bold underline">
                        Tugas 1 Divisi Writer
                    </span>
                    <span class="text-black/40 text-[14px] leading-[20px] font-bold">
                        docx
                    </span>
                </div>

                <div class="absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 rounded bg-[#146C94] flex items-center justify-center text-white font-bold text-xs">
                    W
                </div>
            </div>

            {{-- Choose File + Tanggal (sejajar) --}}
            <div class="flex flex-wrap gap-4">

                {{-- Choose File --}}
                <div class="w-[299px] h-[42px] border border-[#CBD5E1] rounded-lg bg-white flex items-center overflow-hidden shrink-0">
                    <label class="h-full px-4 bg-[#E4E8ED] border-r border-[#CBD5E1] flex items-center text-sm font-medium text-[#283044] cursor-pointer hover:bg-[#d5dbe2] transition">
                        <input type="file" name="lampiran" class="hidden">
                        Choose File
                    </label>
                    <span class="px-4 text-sm text-[#94A3B8] truncate">No file chosen</span>
                </div>

                {{-- Tanggal Tenggat --}}
                <div class="w-[299px] h-[42px] border border-[#CBD5E1] rounded-lg bg-white flex items-center px-3 shrink-0">
                    <input type="date"
                           name="tenggat"
                           class="w-full h-full outline-none text-sm text-[#283044] bg-transparent cursor-pointer">
                </div>

            </div>

        </div>

        {{-- Tombol Submit --}}
        <div class="mt-4 flex justify-end">
            <button type="submit"
                    class="h-9 px-6 bg-[#0E9DC3] rounded-lg text-white font-bold hover:bg-[#0c89aa] transition cursor-pointer">
                Submit
            </button>
        </div>

        </div>

        </form>

            </div>

            </div>
        </main>

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
                        data-modal-close="modalKonfirmasiAcc"
                        class="h-9 px-4 bg-[#0E9DC3] rounded-lg text-white font-bold hover:bg-[#0c89aa] transition cursor-pointer">
                    Ya, Acc
                </button>

            </div>

        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Toggle form revisi ↔ tombol
            document.querySelectorAll('[data-toggle-form]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const form   = document.getElementById(this.dataset.toggleForm);
                    const tombol = document.getElementById('aksiTombol');
                    if (form && tombol) {
                        form.classList.remove('hidden');
                        tombol.classList.add('hidden');
                    }
                });
            });

            // Modal open
            document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const t = document.getElementById(this.dataset.modalOpen);
                    if (t) {
                        t.classList.remove('hidden');
                        document.body.classList.add('overflow-hidden');
                    }
                });
            });

            // Modal close
            document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const t = document.getElementById(this.dataset.modalClose);
                    if (t) {
                        t.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                });
            });

            // Klik area overlay → tutup modal
            document.querySelectorAll('[id^="modalKonfirmasi"]').forEach(function (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) {
                        modal.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                });
            });

        });
    </script>

</body>
</html>