@php
    $pesanFrom = $from ?? null;
    $pesanFrom = is_string($pesanFrom) && $pesanFrom !== '' ? '?from=' . $pesanFrom : '';
    $pesanSort = request('sort');
    $pesanDir = request('dir') === 'asc' ? 'asc' : 'desc';
    $pesanToggle = fn ($col) => ($pesanSort === $col && $pesanDir === 'desc') ? 'asc' : 'desc';
    // Unknown or missing arah falls back to "Semua" so the control always
    // shows exactly one active tab.
    $rawArah = request('arah');
    $pesanArah = in_array($rawArah, ['masuk', 'keluar'], true) ? $rawArah : '';
    $pesanQ = (string) request('q', '');
    $pesanDari = (string) request('dari', '');
    $pesanSampai = (string) request('sampai', '');
    $tabs = ['' => 'Semua', 'masuk' => 'Masuk', 'keluar' => 'Keluar'];
    // Tab URLs keep the other filters but reset to page 1; "Semua" drops
    // the arah parameter entirely instead of leaving a dangling ?arah=.
    $tabUrl = function ($value) {
        $query = request()->query();
        unset($query['page']);
        if ($value === '') {
            unset($query['arah']);
        } else {
            $query['arah'] = $value;
        }
        $qs = http_build_query($query);

        return url()->current().($qs !== '' ? '?'.$qs : '');
    };
    $filterAktif = $pesanQ !== '' || $pesanArah !== '' || $pesanDari !== '' || $pesanSampai !== '';
    $sortIcon = 'opacity-0 group-hover:opacity-100 group-focus-visible:opacity-100';
    $tampilDari = $pesans->total() > 0 ? ($pesans->currentPage() - 1) * $pesans->perPage() + 1 : 0;
    $tampilSampai = min($pesans->currentPage() * $pesans->perPage(), $pesans->total());
@endphp

<div class="flex flex-col gap-4 w-full mt-2">
    <div class="flex flex-wrap justify-between items-center gap-4 mb-2">
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="text-xl font-bold text-slate-800">Daftar Pesan</h2>

            @if (($belumDibaca ?? 0) > 0)
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

        <button type="button" onclick="bukaModalPesanHrd()"
            class="inline-flex h-10 shrink-0 items-center gap-2 rounded-lg bg-[#004A65] px-4 text-sm font-semibold text-white transition hover:bg-[#003347]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
            </svg>
            Tulis Pesan
        </button>
    </div>

    <form method="GET" action="{{ url()->current() }}" class="bg-white border border-slate-200 rounded-xl shadow-sm p-3">
        <div class="flex flex-wrap items-center gap-2">
            {{-- Segmented arah tabs --}}
            <div role="tablist" aria-label="Arah pesan"
                class="inline-flex shrink-0 rounded-lg border border-slate-200 bg-slate-50 p-0.5">
                @foreach ($tabs as $value => $label)
                    <a href="{{ $tabUrl($value) }}" role="tab"
                        aria-selected="{{ $pesanArah === $value ? 'true' : 'false' }}"
                        class="inline-flex h-9 min-w-[5.25rem] items-center justify-center rounded-md px-3 text-sm font-medium transition {{ $pesanArah === $value ? 'bg-[#004A65] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Search --}}
            <div class="relative min-w-[15rem] flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path>
                </svg>
                <input id="hrdPesanSearch" type="search" name="q" value="{{ $pesanQ }}"
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
                <label class="sr-only" for="hrdPesanDari">Rentang tanggal, dari</label>
                <input id="hrdPesanDari" type="date" name="dari" value="{{ $pesanDari }}" title="Rentang tanggal — dari"
                    class="w-[8.5rem] bg-transparent text-sm text-slate-600 outline-none">
                <span class="text-slate-300" aria-hidden="true">–</span>
                <label class="sr-only" for="hrdPesanSampai">Rentang tanggal, sampai</label>
                <input id="hrdPesanSampai" type="date" name="sampai" value="{{ $pesanSampai }}"
                    title="Rentang tanggal — sampai"
                    class="w-[8.5rem] bg-transparent text-sm text-slate-600 outline-none">
            </div>

            <input type="hidden" name="arah" value="{{ $pesanArah }}">
            @if ($pesanSort)
                <input type="hidden" name="sort" value="{{ $pesanSort }}">
                <input type="hidden" name="dir" value="{{ $pesanDir }}">
            @endif
            <input type="hidden" name="per_page" value="{{ $perPage ?? 10 }}">

            {{-- Reset (only while a filter is active) --}}
            @if ($filterAktif)
                <a href="{{ url()->current() }}"
                    class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-lg px-2.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992V4.356m-4.992 4.992-3.181-3.183a8.25 8.25 0 00-11.667 0L1.5 9m.001 5.652v4.992h4.992m-4.993-4.992 3.181 3.183a8.25 8.25 0 0011.667 0l.75-.75">
                        </path>
                    </svg>
                    Reset
                </a>
            @endif
        </div>
    </form>

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
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'judul', 'dir' => $pesanToggle('judul')]) }}"
                                class="group inline-flex items-center gap-1 font-semibold transition hover:text-[#004A65]">
                                Pesan
                                <svg class="h-3.5 w-3.5 transition {{ $pesanSort === 'judul' ? 'opacity-100 text-[#004A65]' : $sortIcon }}"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"></path>
                                </svg>
                            </a>
                        </th>
                        <th scope="col" class="px-3 py-3 font-semibold">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'pengirim', 'dir' => $pesanToggle('pengirim')]) }}"
                                class="group inline-flex items-center gap-1 font-semibold transition hover:text-[#004A65]">
                                Dari / Kepada
                                <svg class="h-3.5 w-3.5 transition {{ $pesanSort === 'pengirim' ? 'opacity-100 text-[#004A65]' : $sortIcon }}"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"></path>
                                </svg>
                            </a>
                        </th>
                        <th scope="col" class="px-3 py-3 font-semibold">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'tanggal', 'dir' => $pesanToggle('tanggal')]) }}"
                                class="group inline-flex items-center gap-1 font-semibold transition hover:text-[#004A65]">
                                Tanggal
                                <svg class="h-3.5 w-3.5 transition {{ $pesanSort === 'tanggal' ? 'opacity-100 text-[#004A65]' : $sortIcon }}"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"></path>
                                </svg>
                            </a>
                        </th>
                        <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pesans as $row)
                        @php $detailUrl = route('hrd.detailPesan', $row['id_pesan']) . $pesanFrom; @endphp
                        <tr tabindex="0" onclick="window.location='{{ $detailUrl }}'"
                            onkeydown="if(event.key==='Enter'){window.location='{{ $detailUrl }}';}"
                            class="h-16 cursor-pointer transition focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#0097B2] {{ $row['unread'] ? 'bg-[#F5F9FF]' : 'bg-white' }} hover:bg-slate-50 focus-visible:bg-slate-50">
                            {{-- Status dot --}}
                            <td class="py-3 pl-4 pr-1 align-middle">
                                @if ($row['unread'])
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
                                        class="truncate text-[15px] leading-5 text-[#1F2937] {{ $row['unread'] ? 'font-bold' : 'font-normal' }}">
                                        {{ $row['judul'] }}
                                    </p>
                                    @if ($row['preview'] !== '')
                                        <p class="truncate text-[13px] leading-5 text-[#6B7280]">{{ $row['preview'] }}</p>
                                    @endif
                                </div>
                            </td>

                            {{-- Dari / Kepada --}}
                            <td class="px-3 py-3 align-middle">
                                <div class="flex items-center gap-2.5">
                                    @if ($row['arah'] === 'masuk')
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
                                        <p class="truncate text-sm font-medium text-[#1F2937]">{{ $row['kontak_nama'] }}</p>
                                        <p
                                            class="text-[12px] font-semibold {{ $row['arah'] === 'masuk' ? 'text-green-700' : 'text-blue-700' }}">
                                            {{ $row['arah'] === 'masuk' ? 'Masuk' : 'Keluar' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Tanggal --}}
                            <td class="whitespace-nowrap px-3 py-3 align-middle">
                                <span class="text-sm text-slate-600" title="{{ $row['tanggal_lengkap'] }}">
                                    {{ $row['tanggal_relatif'] }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-4 py-3 align-middle text-right">
                                <a href="{{ $detailUrl }}" onclick="event.stopPropagation()"
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
                                </a>
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
                                    <a href="{{ url()->current() }}"
                                        class="mt-1 inline-flex h-9 items-center rounded-lg border border-slate-300 px-4 text-sm font-medium text-slate-700 transition hover:border-[#004A65] hover:text-[#004A65]">
                                        Reset filter
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- MOBILE CARDS --}}
        <div class="md:hidden">
            @forelse ($pesans as $row)
                @php $detailUrlM = route('hrd.detailPesan', $row['id_pesan']) . $pesanFrom; @endphp
                <a href="{{ $detailUrlM }}"
                    class="flex w-full items-start gap-3 border-b border-slate-100 p-4 text-left transition {{ $row['unread'] ? 'bg-[#F5F9FF]' : 'bg-white' }} hover:bg-slate-50 last:border-0">
                    <span
                        class="mt-1.5 block h-2.5 w-2.5 shrink-0 rounded-full {{ $row['unread'] ? 'bg-blue-500' : 'bg-transparent' }}"
                        aria-hidden="true"></span>
                    <span class="min-w-0 flex-1">
                        <span
                            class="block truncate text-sm text-[#1F2937] {{ $row['unread'] ? 'font-bold' : 'font-normal' }}">{{ $row['judul'] }}</span>
                        @if ($row['preview'] !== '')
                            <span class="mt-0.5 block truncate text-[13px] text-[#6B7280]">{{ $row['preview'] }}</span>
                        @endif
                        <span class="mt-1.5 flex items-center justify-between gap-2">
                            <span class="min-w-0 truncate text-[12px] text-[#6B7280]">
                                <span
                                    class="font-semibold {{ $row['arah'] === 'masuk' ? 'text-green-700' : 'text-blue-700' }}">{{ $row['arah'] === 'masuk' ? 'Masuk' : 'Keluar' }}</span>
                                · {{ $row['kontak_nama'] }}
                            </span>
                            <span class="shrink-0 text-[12px] text-slate-500"
                                title="{{ $row['tanggal_lengkap'] }}">{{ $row['tanggal_relatif'] }}</span>
                        </span>
                    </span>
                </a>
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
                    <a href="{{ url()->current() }}"
                        class="mt-1 inline-flex h-9 items-center rounded-lg border border-slate-300 px-4 text-sm font-medium text-slate-700 transition hover:border-[#004A65] hover:text-[#004A65]">
                        Reset filter
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- =========================================================
         FOOTER: summary + rows-per-page + pagination
    ========================================================== --}}
    <div class="flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="order-2 text-sm text-slate-600 sm:order-1">
            Menampilkan <span class="font-semibold text-slate-800">{{ $tampilDari }}-{{ $tampilSampai }}</span> dari
            <span class="font-semibold text-slate-800">{{ $pesans->total() }}</span> pesan
        </p>

        <div class="order-1 flex flex-wrap items-center justify-center gap-3 sm:order-2">
            <form method="GET" action="{{ url()->current() }}"
                class="flex items-center gap-2 text-sm text-slate-600">
                @foreach (['q' => $pesanQ, 'arah' => $pesanArah, 'dari' => $pesanDari, 'sampai' => $pesanSampai, 'sort' => $pesanSort, 'dir' => $pesanDir] as $nama => $nilai)
                    @if ($nilai !== '' && $nilai !== null)
                        <input type="hidden" name="{{ $nama }}" value="{{ $nilai }}">
                    @endif
                @endforeach
                <span>Tampilkan</span>
                <label class="sr-only" for="hrdPesanPerPage">Baris per halaman</label>
                <select id="hrdPesanPerPage" name="per_page" onchange="this.form.submit()"
                    class="h-9 rounded-lg border border-slate-300 bg-white px-2 text-sm text-slate-700 outline-none transition focus:border-[#004A65]">
                    @foreach ([10, 25, 50] as $jumlah)
                        <option value="{{ $jumlah }}" @selected(($perPage ?? 10) == $jumlah)>{{ $jumlah }}</option>
                    @endforeach
                </select>
                <span>per halaman</span>
            </form>

            {{ $pesans->links('vendor.pagination.custom') }}
        </div>
    </div>

    {{-- MODAL KIRIM PESAN --}}
    <div id="modalKirimPesanHrd"
         class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalPesanHrd()"></div>

        <div id="modalBoxPesanHrd"
             class="transform scale-95 translate-y-4 relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transition-all duration-300">
            <div class="flex items-center justify-between p-4 border-b border-dashed border-gray-300">
                <div class="flex items-center gap-3">
                    <div class="bg-cyan-100 p-1.5 rounded text-cyan-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Kirim Pesan</h3>
                </div>
                <button type="button" onclick="tutupModalPesanHrd()" class="text-slate-400 hover:text-red-500 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="formKirimPesanHrd" class="p-5 flex flex-col gap-4">
                <div>
                    <label for="penerimaPesanHrd" class="block text-xs font-bold text-slate-500 mb-1">PENERIMA</label>
                    <select name="penerima_id_user" id="penerimaPesanHrd"
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm text-slate-700"
                            required>
                        <option value="">Memuat penerima...</option>
                    </select>
                </div>

                <div>
                    <label for="isiPesanBaruHrd" class="block text-xs font-bold text-slate-500 mb-1">ISI PESAN</label>
                    <textarea name="deskripsi" id="isiPesanBaruHrd" rows="4"
                              class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-[#004A65] focus:border-[#004A65] outline-none text-sm placeholder-gray-400"
                              placeholder="Tuliskan pesan di sini..." required></textarea>
                </div>

                <div class="flex justify-between items-center mt-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="tutupModalPesanHrd()"
                            class="px-5 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-1.5 bg-[#004A65] text-white text-sm font-medium rounded-md hover:bg-[#003347] transition flex items-center gap-1">
                        Kirim
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const modal = document.getElementById('modalKirimPesanHrd');
            const modalBox = document.getElementById('modalBoxPesanHrd');
            const form = document.getElementById('formKirimPesanHrd');
            const recipientSelect = document.getElementById('penerimaPesanHrd');

            async function loadRecipients() {
                // HRD login via web session — gunakan web route /hrd/staff bukan API token
                const response = await fetch('/hrd/staff', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    }
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Gagal memuat daftar penerima.');

                recipientSelect.replaceChildren(new Option('-- Pilih Penerima --', ''));
                (result.data || []).forEach((employee) => {
                    const account = employee.user;
                    const role = account?.role?.nama_role?.toLowerCase();
                    if (!account || account.id_user === @json(auth()->user()->id_user) || !['staff', 'kadiv'].includes(role)) {
                        return;
                    }

                    const division = employee.divisi?.nama_divisi;
                    const label = [employee.nama, role.toUpperCase(), division].filter(Boolean).join(' - ');
                    recipientSelect.add(new Option(label, account.id_user));
                });
            }

            loadRecipients().catch((error) => {
                console.error('Gagal memuat penerima pesan HRD:', error);
                recipientSelect.replaceChildren(new Option('Penerima gagal dimuat', ''));
                alert(error.message);
            });

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                const description = document.getElementById('isiPesanBaruHrd').value.trim();
                if (!description) {
                    alert('Isi pesan wajib diisi.');
                    return;
                }
                if (!recipientSelect.value) {
                    alert('Pilih penerima terlebih dahulu.');
                    return;
                }

                const payload = {
                    penerima_id_user: recipientSelect.value,
                    tipe: 'pesan',
                    judul_pesan: description.slice(0, 200),
                    deskripsi: description
                };

                try {
                    // Gunakan web route /hrd/pesan (POST) via CSRF session auth
                    const response = await fetch('/hrd/pesan', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload)
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || 'Pesan gagal dikirim.');

                    form.reset();
                    tutupModalPesanHrd();
                    window.location.reload();
                } catch (error) {
                    alert(error.message);
                }
            });

            window.bukaModalPesanHrd = function () {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modal.classList.add('opacity-100');
                modalBox.classList.remove('scale-95', 'translate-y-4');
                modalBox.classList.add('scale-100', 'translate-y-0');
            };

            window.tutupModalPesanHrd = function () {
                modal.classList.add('opacity-0', 'pointer-events-none');
                modal.classList.remove('opacity-100');
                modalBox.classList.remove('scale-100', 'translate-y-0');
                modalBox.classList.add('scale-95', 'translate-y-4');
            };
        })();
    </script>
</div>

