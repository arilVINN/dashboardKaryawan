@php
    use Illuminate\Support\Str;

    $tabs = ['' => 'Semua', 'masuk' => 'Masuk', 'keluar' => 'Keluar'];

    $filterAktif = $search !== '' || $arah !== '' || $tipe !== '' || $dari !== '' || $sampai !== '';

    $sortIcon = 'opacity-0 group-hover:opacity-100 group-focus-visible:opacity-100';
    $sortIconAktif = 'opacity-100 text-[#004A65]';

    $from = $messages->total() > 0 ? ($messages->currentPage() - 1) * $messages->perPage() + 1 : 0;
    $to = min($messages->currentPage() * $messages->perPage(), $messages->total());
@endphp

<div>
<div class="flex flex-col gap-4 w-full mt-2">

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2"><span class="p-2 rounded-lg bg-green-100 text-green-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path></svg></span><span>Daftar Pesan</span></h2>

            @if ($belumDibaca > 0)
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800">
                    <span class="h-2 w-2 rounded-full bg-sky-600" aria-hidden="true"></span>
                    {{ $belumDibaca }} belum dibaca
                </span>
            @else
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                    Semua terbaca
                </span>
            @endif
        </div>

        <button type="button" data-modal-open="modalKirimPesan"
            class="inline-flex h-10 shrink-0 items-center gap-2 rounded-lg bg-[#004A65] px-4 text-sm font-semibold text-white transition hover:bg-[#003347]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
            </svg>
            Tulis Pesan
        </button>
    </header>

    @if (session('pesan_success'))
        <div class="text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
            {{ session('pesan_success') }}
        </div>
    @endif

    {{-- =========================================================
         FILTER BAR (compact, single row)
    ========================================================== --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-3">
        <div class="flex flex-wrap items-center gap-2">

            {{-- Segmented arah tabs --}}
            <div role="tablist" aria-label="Arah pesan"
                class="inline-flex shrink-0 rounded-lg border border-slate-200 bg-slate-50 p-0.5">
                @foreach ($tabs as $value => $label)
                    <button type="button" role="tab" wire:click="$set('arah', '{{ $value }}')"
                        aria-selected="{{ $arah === $value ? 'true' : 'false' }}"
                        class="h-9 min-w-[5.25rem] rounded-md px-3 text-sm font-medium transition {{ $arah === $value ? 'bg-[#004A65] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Search --}}
            <div class="relative min-w-[15rem] flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path>
                </svg>
                <input id="kadivPesanSearch" type="search" wire:model.live.debounce.300ms="search"
                    placeholder="Cari pengirim / topik..." aria-label="Cari pengirim atau topik"
                    class="h-10 w-full rounded-lg border border-slate-300 pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-500 focus:border-[#004A65]">
            </div>

            {{-- Date range --}}
            <div class="flex h-10 shrink-0 items-center gap-1 rounded-lg border border-slate-300 px-2">
                <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8"
                    viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5">
                    </path>
                </svg>
                <label class="sr-only" for="kadivPesanDari">Rentang tanggal, dari</label>
                <input id="kadivPesanDari" type="date" wire:model.live="dari" title="Rentang tanggal — dari"
                    class="w-[8.5rem] bg-transparent text-sm text-slate-600 outline-none">
                <span class="text-slate-300" aria-hidden="true">–</span>
                <label class="sr-only" for="kadivPesanSampai">Rentang tanggal, sampai</label>
                <input id="kadivPesanSampai" type="date" wire:model.live="sampai" title="Rentang tanggal — sampai"
                    class="w-[8.5rem] bg-transparent text-sm text-slate-600 outline-none">
            </div>

            {{-- Tipe --}}
            <div class="shrink-0">
                <label class="sr-only" for="kadivPesanTipe">Tipe</label>
                <select id="kadivPesanTipe" wire:model.live="tipe" title="Tipe"
                    class="h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-sm text-slate-700 outline-none transition focus:border-[#004A65]">
                    <option value="">Semua tipe</option>
                    <option value="pesan">Pesan</option>
                    <option value="surat">Surat</option>
                </select>
            </div>

            {{-- Reset (only while a filter is active) --}}
            @if ($filterAktif)
                <button type="button" wire:click="resetFilters"
                    class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-lg px-2.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992V4.356m-4.992 4.992-3.181-3.183a8.25 8.25 0 00-11.667 0L1.5 9m.001 5.652v4.992h4.992m-4.993-4.992 3.181 3.183a8.25 8.25 0 0011.667 0l.75-.75">
                        </path>
                    </svg>
                    Reset
                </button>
            @endif
        </div>
    </div>

    {{-- =========================================================
         TABLE / CARDS  (header stays visible while empty)
    ========================================================== --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">

        {{-- DESKTOP TABLE --}}
        <div class="hidden overflow-x-auto md:block">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#F3F4F6] text-[#4B5563]">
                    <tr class="text-[12.5px] font-semibold">
                        <th scope="col" class="w-12 py-3 pl-4 pr-1">
                            <span class="sr-only">Status</span>
                        </th>
                        <th scope="col" class="px-3 py-3 font-semibold">
                            <button type="button" wire:click="sortBy('judul')"
                                class="group inline-flex items-center gap-1 font-semibold transition hover:text-[#004A65]">
                                Pesan
                                <svg class="h-3.5 w-3.5 transition {{ $sort === 'judul' ? $sortIconAktif : $sortIcon }}"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"></path>
                                </svg>
                            </button>
                        </th>
                        <th scope="col" class="px-3 py-3 font-semibold">
                            <button type="button" wire:click="sortBy('pengirim')"
                                class="group inline-flex items-center gap-1 font-semibold transition hover:text-[#004A65]">
                                Dari / Kepada
                                <svg class="h-3.5 w-3.5 transition {{ $sort === 'pengirim' ? $sortIconAktif : $sortIcon }}"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"></path>
                                </svg>
                            </button>
                        </th>
                        <th scope="col" class="px-3 py-3 font-semibold">
                            <button type="button" wire:click="sortBy('tanggal')"
                                class="group inline-flex items-center gap-1 font-semibold transition hover:text-[#004A65]">
                                Tanggal
                                <svg class="h-3.5 w-3.5 transition {{ $sort === 'tanggal' ? $sortIconAktif : $sortIcon }}"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"></path>
                                </svg>
                            </button>
                        </th>
                        <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($messages as $message)
                        @php
                            $masuk = ($message['arah'] ?? 'masuk') === 'masuk';
                            $kontak = $masuk ? ($message['pengirim'] ?? null) : ($message['penerima'] ?? null);
                            $kontakNama = $kontak['nama'] ?? $kontak['username'] ?? 'Pengguna tidak tersedia';
                            $preview = Str::limit((string) ($message['deskripsi'] ?? ''), 90);
                            $relatif = \App\Models\Pesan::formatTanggalRelatif($message['tanggal_pesan'] ?? null, $message['created_at'] ?? null);
                            $lengkap = \App\Models\Pesan::formatTanggalJam($message['tanggal_pesan'] ?? null, $message['created_at'] ?? null);
                        @endphp
                        <tr wire:key="pesan-{{ $message['id_pesan'] }}" tabindex="0"
                            wire:click="baca('{{ $message['id_pesan'] }}')"
                            wire:keydown.enter="baca('{{ $message['id_pesan'] }}')"
                            class="group h-16 cursor-pointer transition focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#0097B2] {{ $message['unread'] ?? false ? 'bg-[#F5F9FF]' : 'bg-white' }} hover:bg-slate-50 focus-visible:bg-slate-50">
                            {{-- Status dot --}}
                            <td class="py-3 pl-4 pr-1 align-middle">
                                @if ($message['unread'] ?? false)
                                    <span class="block h-2.5 w-2.5 rounded-full bg-blue-500" title="Belum dibaca"><span
                                            class="sr-only">Belum dibaca</span></span>
                                @else
                                    <span class="block h-2.5 w-2.5 rounded-full bg-transparent" aria-hidden="true"></span>
                                @endif
                            </td>

                            {{-- Pesan --}}
                            <td class="px-3 py-3 align-middle">
                                <div class="max-w-[32rem]">
                                    <p
                                        class="truncate text-[15px] leading-5 text-[#1F2937] {{ $message['unread'] ?? false ? 'font-bold' : 'font-normal' }}">
                                        {{ $message['judul_pesan'] }}
                                    </p>
                                    @if ($preview !== '')
                                        <p class="truncate text-[13px] leading-5 text-[#6B7280]">{{ $preview }}</p>
                                    @endif
                                </div>
                            </td>

                            {{-- Dari / Kepada --}}
                            <td class="px-3 py-3 align-middle">
                                <div class="flex items-center gap-2.5">
                                    @if ($masuk)
                                        <span
                                            class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-700"
                                            aria-hidden="true">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19.5 4.5 4.5 19.5m0 0h10.5m-10.5 0V9"></path>
                                            </svg>
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700"
                                            aria-hidden="true">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.5 19.5 19.5 4.5m0 0H9m10.5 0V15"></path>
                                            </svg>
                                        </span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-[#1F2937]">{{ $kontakNama }}</p>
                                        <p
                                            class="text-[12px] font-semibold {{ $masuk ? 'text-green-700' : 'text-blue-700' }}">
                                            {{ $masuk ? 'Masuk' : 'Keluar' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Tanggal --}}
                            <td class="whitespace-nowrap px-3 py-3 align-middle">
                                <span class="text-sm text-slate-600" title="{{ $lengkap }}">
                                    {{ $relatif }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-4 py-3 align-middle text-right">
                                <button type="button" wire:click.stop="baca('{{ $message['id_pesan'] }}')"
                                    class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-300 px-3 text-sm font-medium text-slate-700 transition hover:border-[#004A65] hover:text-[#004A65]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                        viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0Z"></path>
                                    </svg>
                                    Baca
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16">
                                <div class="flex flex-col items-center justify-center gap-3 text-center">
                                    <span
                                        class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6"
                                            viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75">
                                            </path>
                                        </svg>
                                    </span>
                                    <p class="text-base font-semibold text-[#1F2937]">Tidak ada pesan ditemukan</p>
                                    <p class="max-w-sm text-sm text-[#6B7280]">Coba ubah kata kunci, rentang tanggal, atau
                                        arah pesan.</p>
                                    <button type="button" wire:click="resetFilters"
                                        class="mt-1 inline-flex h-9 items-center rounded-lg border border-slate-300 px-4 text-sm font-medium text-slate-700 transition hover:border-[#004A65] hover:text-[#004A65]">
                                        Reset filter
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- MOBILE CARDS --}}
        <div class="md:hidden">
            @forelse ($messages as $message)
                @php
                    $masukM = ($message['arah'] ?? 'masuk') === 'masuk';
                    $kontakM = $masukM ? ($message['pengirim'] ?? null) : ($message['penerima'] ?? null);
                    $kontakNamaM = $kontakM['nama'] ?? $kontakM['username'] ?? 'Pengguna tidak tersedia';
                    $previewM = Str::limit((string) ($message['deskripsi'] ?? ''), 90);
                    $relatifM = \App\Models\Pesan::formatTanggalRelatif($message['tanggal_pesan'] ?? null, $message['created_at'] ?? null);
                    $lengkapM = \App\Models\Pesan::formatTanggalJam($message['tanggal_pesan'] ?? null, $message['created_at'] ?? null);
                @endphp
                <button type="button" wire:key="pesan-m-{{ $message['id_pesan'] }}"
                    wire:click="baca('{{ $message['id_pesan'] }}')"
                    class="flex w-full items-start gap-3 border-b border-slate-100 p-4 text-left transition {{ $message['unread'] ?? false ? 'bg-[#F5F9FF]' : 'bg-white' }} hover:bg-slate-50 last:border-0">
                    <span
                        class="mt-1.5 block h-2.5 w-2.5 shrink-0 rounded-full {{ $message['unread'] ?? false ? 'bg-blue-500' : 'bg-transparent' }}"
                        aria-hidden="true"></span>
                    <span class="min-w-0 flex-1">
                        <span
                            class="block truncate text-sm text-[#1F2937] {{ $message['unread'] ?? false ? 'font-bold' : 'font-normal' }}">{{ $message['judul_pesan'] }}</span>
                        @if ($previewM !== '')
                            <span class="mt-0.5 block truncate text-[13px] text-[#6B7280]">{{ $previewM }}</span>
                        @endif
                        <span class="mt-1.5 flex items-center justify-between gap-2">
                            <span class="min-w-0 truncate text-[12px] text-[#6B7280]">
                                <span
                                    class="font-semibold {{ $masukM ? 'text-green-700' : 'text-blue-700' }}">{{ $masukM ? 'Masuk' : 'Keluar' }}</span>
                                · {{ $kontakNamaM }}
                            </span>
                            <span class="shrink-0 text-[12px] text-slate-500"
                                title="{{ $lengkapM }}">{{ $relatifM }}</span>
                        </span>
                    </span>
                </button>
            @empty
                <div class="flex flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75">
                            </path>
                        </svg>
                    </span>
                    <p class="text-base font-semibold text-[#1F2937]">Tidak ada pesan ditemukan</p>
                    <p class="max-w-sm text-sm text-[#6B7280]">Coba ubah kata kunci, rentang tanggal, atau arah pesan.</p>
                    <button type="button" wire:click="resetFilters"
                        class="mt-1 inline-flex h-9 items-center rounded-lg border border-slate-300 px-4 text-sm font-medium text-slate-700 transition hover:border-[#004A65] hover:text-[#004A65]">
                        Reset filter
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    {{-- =========================================================
         FOOTER: summary + rows-per-page + pagination
    ========================================================== --}}
    <div class="flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="order-2 text-sm text-slate-600 sm:order-1">
            Menampilkan <span class="font-semibold text-slate-800">{{ $from }}-{{ $to }}</span> dari
            <span class="font-semibold text-slate-800">{{ $messages->total() }}</span> pesan
        </p>

        <div class="order-1 flex flex-wrap items-center justify-center gap-3 sm:order-2">
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <span>Tampilkan</span>
                <select wire:model.live="perPage"
                    class="h-9 rounded-lg border border-slate-300 bg-white px-2 text-sm text-slate-700 outline-none transition focus:border-[#004A65]">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span>per halaman</span>
            </label>

            {{ $messages->links('vendor.pagination.custom') }}
        </div>
    </div>

    {{-- =========================================================
         MODAL: Tulis Pesan
    ========================================================== --}}
    <div id="modalKirimPesan" class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-modal-close="modalKirimPesan"></div>
        <form wire:submit="sendMessage" data-modal-panel
            class="transform scale-95 translate-y-4 relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transition-all duration-300">
            <div class="flex items-center justify-between p-4 border-b border-dashed border-gray-300">
                <h3 class="text-base font-bold text-slate-900">Tulis Pesan</h3>
                <button type="button" data-modal-close="modalKirimPesan" class="text-slate-400 hover:text-red-500">&times;</button>
            </div>

            <div class="p-5 flex flex-col gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">PENERIMA</label>
                    <select wire:model="recipientUserId"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm text-slate-700 outline-none focus:border-[#004A65]">
                        <option value="">-- Pilih Penerima --</option>
                        @foreach ($recipients as $r)
                            <option value="{{ $r->id_user }}">{{ $r->karyawan?->nama ?? $r->username }}</option>
                        @endforeach
                    </select>
                    @error('recipientUserId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">ISI PESAN</label>
                    <textarea wire:model="isi" rows="4" placeholder="Tuliskan pesan..."
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]"></textarea>
                    @error('isi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-between items-center px-5 py-4 border-t border-slate-100">
                <button type="button" data-modal-close="modalKirimPesan"
                    class="px-5 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Batal</button>
                <button type="submit"
                    class="px-4 py-1.5 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove>Kirim</span>
                    <span wire:loading>Mengirim...</span>
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('tutup-modal-pesan', () => {
                if (window.setModal) {
                    window.setModal('modalKirimPesan', false);
                }
            });
        });
    </script>
</div>
</div>
