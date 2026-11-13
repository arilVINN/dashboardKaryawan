<div class="space-y-4">
    <style>
        /* Toolbar navigasi identik dengan Kalender Tugas Divisi */
        .cal-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 38px;
            padding: 0 0.85rem;
            border: 1px solid #E2E8EC;
            border-radius: 0.5rem;
            background-color: #FFFFFF;
            color: #4B5563;
            font-size: 13px;
            font-weight: 500;
            line-height: 1;
            text-transform: capitalize;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.1s ease-out;
        }
        .cal-btn:hover {
            background-color: #F8FAFC;
            border-color: #B9C6CE;
            color: #172B36;
        }
        .cal-nav-group {
            display: inline-flex;
            align-items: stretch;
            overflow: hidden;
            border: 1px solid #E2E8EC;
            border-radius: 0.5rem;
            background-color: #FFFFFF;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
        }
        .cal-nav-group .cal-btn {
            height: 38px;
            border: 0;
            border-right: 1px solid #E2E8EC;
            border-radius: 0;
        }
        .cal-nav-group .cal-btn:last-child {
            border-right: 0;
        }
        .cal-nav-group .cal-btn.cal-nav {
            width: 38px;
            padding: 0;
            color: #4B5563;
        }
        .cal-nav-group .cal-btn.cal-nav:hover {
            background-color: #F8FAFC;
            color: #172B36;
        }
        .cal-view-group {
            display: inline-flex;
            align-items: center;
            padding: 2px;
            border: 1px solid #E2E8EC;
            border-radius: 0.5rem;
            background-color: #FFFFFF;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
        }
        .cal-view-group .cal-btn {
            height: 34px;
            padding: 0 0.85rem;
            border: 0;
            border-radius: 0.375rem;
            background-color: transparent;
            font-size: 13px;
            font-weight: 500;
        }
        .cal-view-group .cal-btn:hover {
            background-color: #F8FAFC;
            color: #172B36;
        }
        .cal-view-group .cal-btn.is-active {
            background-color: #146C94;
            color: #FFFFFF;
        }
        .cal-view-group .cal-btn.is-active:hover {
            background-color: #146C94;
            color: #FFFFFF;
        }
    </style>

    {{-- Toolbar: Navigasi Minggu + Judul Periode (Persis seperti Kalender) --}}
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
        {{-- Navigasi < > Hari Ini + Periode Mingguan --}}
        <div class="flex min-w-0 flex-wrap items-center gap-3">
            <div class="cal-nav-group">
                <button type="button" wire:click="prevWeek" class="cal-btn cal-nav" aria-label="Minggu Sebelumnya" title="Minggu Sebelumnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button type="button" wire:click="nextWeek" class="cal-btn cal-nav" aria-label="Minggu Berikutnya" title="Minggu Berikutnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <button type="button" wire:click="goToToday" class="cal-btn">Hari ini</button>
            </div>

            <h2 class="truncate text-lg md:text-xl font-bold text-[#172B36] tracking-tight">
                {{ $weekTitle }}
            </h2>

            @if($activeTargetKaryawan && $viewMode !== 'my')
                <span class="text-xs px-2.5 py-1 bg-slate-100 text-slate-700 font-medium rounded-md">
                    Catatan: <strong class="text-slate-900">{{ $activeTargetKaryawan->nama }}</strong>
                </span>
            @endif
        </div>

        {{-- Role Tabs & Filters for Kadiv & HRD --}}
        <div class="flex flex-wrap items-center gap-2">
            @if($role === 'kadiv' || $role === 'hrd')
                <div class="cal-view-group">
                    <button type="button" wire:click="setViewMode('my')"
                            class="cal-btn {{ $viewMode === 'my' ? 'is-active' : '' }}">
                        Jurnal Saya
                    </button>

                    @if($role === 'kadiv')
                        <button type="button" wire:click="setViewMode('team')"
                                class="cal-btn {{ $viewMode === 'team' ? 'is-active' : '' }}">
                            Jurnal Tim
                        </button>
                    @elseif($role === 'hrd')
                        <button type="button" wire:click="setViewMode('all')"
                                class="cal-btn {{ $viewMode === 'all' ? 'is-active' : '' }}">
                            Monitoring Karyawan
                        </button>
                    @endif
                </div>

                {{-- Dropdowns bila melihat tim / monitoring --}}
                @if($viewMode !== 'my')
                    @if($role === 'hrd')
                        <select wire:model.live="filterDivisiId" class="text-xs px-2.5 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#19A7CE] outline-none">
                            <option value="">Semua Divisi</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id_divisi }}">{{ $div->nama_divisi }}</option>
                            @endforeach
                        </select>
                    @endif

                    <select wire:model.live="filterKaryawanId" class="text-xs px-2.5 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#19A7CE] outline-none min-w-[150px]">
                        <option value="">Pilih Karyawan</option>
                        @foreach($employeeOptions as $emp)
                            <option value="{{ $emp->id_karyawan }}">{{ $emp->nama }}</option>
                        @endforeach
                    </select>
                @endif
            @endif

            {{-- Ringkasan Mingguan Mini --}}
            <div class="hidden sm:flex items-center gap-2 pl-2 border-l border-slate-200 text-xs">
                <span class="text-slate-500">Minggu ini:</span>
                <span class="font-bold text-slate-800">{{ $stats['totalTugas'] }} tugas</span>
                <span class="text-emerald-600 font-semibold">({{ $stats['totalSelesai'] }} selesai)</span>
            </div>
        </div>
    </div>

    {{-- Feedback Message --}}
    @if($feedbackMessage && ! $isModalOpen)
        <div class="p-3.5 rounded-xl border text-xs font-medium {{ $feedbackType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }} flex items-center justify-between">
            <span>{{ $feedbackMessage }}</span>
            <button type="button" wire:click="resetFeedback" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
    @endif

    {{-- Tabel Jurnal Mingguan (7 Hari: Senin s.d. Minggu) --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/90 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4 w-44">Tanggal & Hari</th>
                        <th class="py-3 px-4 min-w-[260px]">Aktivitas / Pekerjaan</th>
                        <th class="py-3 px-3 w-28 text-center">Prioritas</th>
                        <th class="py-3 px-3 w-32 text-center">Status</th>
                        <th class="py-3 px-4 min-w-[220px]">Catatan / Bukti Pengerjaan</th>
                        <th class="py-3 px-4 w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @foreach($calendarDays as $day)
                        @php
                            $rowBg = 'hover:bg-slate-50/80 transition';
                            if ($day['isToday']) {
                                $rowBg = 'bg-sky-50/40 hover:bg-sky-50/70 transition';
                            } elseif ($day['isWeekend']) {
                                $rowBg = 'bg-slate-50/30 hover:bg-slate-100/50 transition';
                            }
                        @endphp
                        <tr class="{{ $rowBg }} cursor-pointer group" wire:click="openDayModal('{{ $day['date'] }}')">
                            {{-- 1. Tanggal & Hari --}}
                            <td class="py-3.5 px-4 align-top">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg flex flex-col items-center justify-center font-bold {{ $day['isToday'] ? 'bg-[#146C94] text-white shadow-2xs' : ($day['isWeekend'] ? 'bg-slate-200 text-slate-600' : 'bg-slate-100 text-slate-700') }}">
                                        <span class="text-xs leading-none">{{ $day['dayNumber'] }}</span>
                                    </div>
                                    <div>
                                        <div class="font-bold text-xs {{ $day['isToday'] ? 'text-[#146C94]' : ($day['isWeekend'] ? 'text-rose-600' : 'text-slate-800') }}">
                                            {{ $day['dayName'] }}
                                        </div>
                                        <div class="text-[11px] text-slate-400">
                                            {{ $day['shortFormatted'] }}
                                        </div>
                                    </div>
                                </div>

                                @if($day['isToday'])
                                    <span class="inline-block mt-1 px-1.5 py-0.5 text-[10px] font-bold bg-sky-100 text-[#146C94] rounded">
                                        Hari Ini
                                    </span>
                                @endif

                                @if($day['holiday'])
                                    <span class="inline-block mt-1 px-1.5 py-0.5 text-[10px] font-semibold bg-rose-100 text-rose-700 rounded truncate max-w-[130px]" title="{{ $day['holiday'] }}">
                                        {{ $day['holiday'] }}
                                    </span>
                                @endif
                            </td>

                            {{-- 2. Aktivitas / Pekerjaan --}}
                            <td class="py-3.5 px-4 align-top">
                                @if($day['count'] > 0)
                                    <ul class="space-y-1.5">
                                        @foreach($day['journals'] as $jurnal)
                                            <li class="flex items-start gap-2 text-xs text-slate-700">
                                                <span class="mt-1 w-1.5 h-1.5 rounded-full shrink-0 {{ $jurnal->status === 'selesai' ? 'bg-emerald-500' : 'bg-blue-500' }}"></span>
                                                <span class="leading-relaxed {{ $jurnal->status === 'selesai' ? 'text-slate-500 line-through/60' : 'text-slate-800 font-medium' }}">
                                                    {{ $jurnal->aktivitas }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum ada catatan</span>
                                @endif
                            </td>

                            {{-- 3. Prioritas (Merah, Kuning, Hijau) --}}
                            <td class="py-3.5 px-3 align-top text-center">
                                @if($day['count'] > 0)
                                    <div class="flex flex-col gap-1 items-center">
                                        @foreach($day['journals'] as $jurnal)
                                            @if($jurnal->prioritas === 'tinggi')
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Tinggi
                                                </span>
                                            @elseif($jurnal->prioritas === 'sedang')
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Sedang
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Rendah
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-slate-300">-</span>
                                @endif
                            </td>

                            {{-- 4. Status Progress (On Progress: Biru, Selesai: Emerald) --}}
                            <td class="py-3.5 px-3 align-top text-center">
                                @if($day['count'] > 0)
                                    <div class="flex flex-col gap-1 items-center">
                                        @foreach($day['journals'] as $jurnal)
                                            @if($jurnal->status === 'selesai')
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Selesai
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                    <svg class="w-3 h-3 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                                    </svg>
                                                    On Progress
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-normal text-slate-400 bg-slate-100">
                                        Kosong
                                    </span>
                                @endif
                            </td>

                            {{-- 5. Catatan / Bukti Pengerjaan (Tepat di sebelah kanan status) --}}
                            <td class="py-3.5 px-4 align-top">
                                @if($day['count'] > 0)
                                    <div class="space-y-1.5">
                                        @foreach($day['journals'] as $jurnal)
                                            <div class="text-xs text-slate-600 leading-relaxed">
                                                @if(!empty($jurnal->catatan))
                                                    <span class="text-slate-700">{{ $jurnal->catatan }}</span>
                                                @else
                                                    <span class="text-slate-300">-</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-slate-300">-</span>
                                @endif
                            </td>

                            {{-- 6. Aksi --}}
                            <td class="py-3.5 px-4 align-top text-center" onclick="event.stopPropagation()">
                                <button type="button" wire:click="openDayModal('{{ $day['date'] }}')"
                                        class="px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 bg-white hover:bg-[#146C94] hover:text-white hover:border-[#146C94] text-slate-700 transition shadow-2xs inline-flex items-center gap-1.5 group/btn">
                                    @if($day['count'] > 0)
                                        <svg class="w-3.5 h-3.5 text-slate-500 group-hover/btn:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Detail</span>
                                    @else
                                        @if($viewMode === 'my')
                                            <svg class="w-3.5 h-3.5 text-[#146C94] group-hover/btn:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                            </svg>
                                            <span>Isi</span>
                                        @else
                                            <svg class="w-3.5 h-3.5 text-slate-500 group-hover/btn:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <span>Lihat</span>
                                        @endif
                                    @endif
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL DETAIL & INPUT JURNAL HARI --}}
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                
                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <span class="text-xs font-semibold text-[#146C94] uppercase tracking-wider">Jurnal Harian</span>
                        <h3 class="text-lg font-bold text-slate-800">{{ $modalDateFormatted }}</h3>
                    </div>
                    <button type="button" wire:click="closeDayModal" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 overflow-y-auto space-y-6 flex-1">
                    
                    {{-- Alert Notification inside Modal --}}
                    @if($feedbackMessage)
                        <div class="p-3.5 rounded-xl border text-xs font-medium {{ $feedbackType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }} flex items-center justify-between">
                            <span>{{ $feedbackMessage }}</span>
                            <button type="button" wire:click="resetFeedback" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                    @endif

                    {{-- Daftar Aktivitas Hari Tersebut --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                Daftar Aktivitas ({{ $modalJournals->count() }})
                            </h4>
                        </div>

                        @if($modalJournals->isNotEmpty())
                            <div class="space-y-3">
                                @foreach($modalJournals as $item)
                                    <div class="p-4 rounded-xl border {{ $item->status === 'selesai' ? 'bg-emerald-50/20 border-emerald-100' : 'bg-slate-50/80 border-slate-200' }} transition">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="space-y-1.5 flex-1">
                                                {{-- Badges --}}
                                                <div class="flex flex-wrap items-center gap-2">
                                                    {{-- Prioritas Badge --}}
                                                    @if($item->prioritas === 'tinggi')
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">
                                                            🔴 Tinggi
                                                        </span>
                                                    @elseif($item->prioritas === 'sedang')
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                                                            🟡 Sedang
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                                            🟢 Rendah
                                                        </span>
                                                    @endif

                                                    {{-- Status Badge --}}
                                                    @if($item->status === 'selesai')
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                                            ✅ Selesai
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">
                                                            ⏳ On Progress
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Aktivitas --}}
                                                <p class="text-sm font-semibold text-slate-800 leading-snug">
                                                    {{ $item->aktivitas }}
                                                </p>

                                                {{-- Catatan / Bukti Pengerjaan (Teks) --}}
                                                @if(!empty($item->catatan))
                                                    <div class="mt-2 pt-2 border-t border-slate-200/60 text-xs text-slate-600 bg-white/70 p-2.5 rounded-lg">
                                                        <span class="font-semibold text-slate-700 block mb-0.5">Catatan / Bukti Pengerjaan:</span>
                                                        <p class="whitespace-pre-line">{{ $item->catatan }}</p>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Action Buttons for Owner --}}
                                            @if(! $isViewingOther)
                                                <div class="flex items-center gap-1 shrink-0">
                                                    {{-- Quick status toggle button --}}
                                                    <button type="button" wire:click="quickToggleStatus({{ $item->id }})"
                                                            class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                                                            title="{{ $item->status === 'selesai' ? 'Ubah ke On Progress' : 'Tandai Selesai' }}">
                                                        @if($item->status === 'selesai')
                                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        @else
                                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <circle cx="12" cy="12" r="9" stroke-width="2"></circle>
                                                            </svg>
                                                        @endif
                                                    </button>

                                                    <button type="button" wire:click="editJournal({{ $item->id }})"
                                                            class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                                            title="Edit Aktivitas">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                        </svg>
                                                    </button>

                                                    <button type="button" wire:confirm="Hapus aktivitas ini dari jurnal?" wire:click="deleteJournal({{ $item->id }})"
                                                            class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                                            title="Hapus Aktivitas">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-6 text-center rounded-xl border border-dashed border-slate-200 text-slate-400 text-xs">
                                Belum ada aktivitas yang dicatat untuk hari ini.
                            </div>
                        @endif
                    </div>

                    {{-- Form Input / Edit Aktivitas (Hanya untuk pemilik jurnal) --}}
                    @if(! $isViewingOther)
                        <div class="pt-5 border-t border-slate-100">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3 flex items-center gap-1.5">
                                <span>{{ $editingJournalId ? '✏️ Edit Aktivitas' : '➕ Tambah Aktivitas Baru' }}</span>
                            </h4>

                            <form wire:submit="saveJournal" class="space-y-4">
                                {{-- Aktivitas --}}
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                                        Aktivitas / Uraian Tugas <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" wire:model="aktivitas"
                                           placeholder="Tuliskan apa yang kamu kerjakan hari ini..."
                                           class="w-full text-xs px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#146C94] focus:border-transparent outline-none transition">
                                    @error('aktivitas')
                                        <span class="text-[11px] text-rose-500 font-medium block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Prioritas & Status --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {{-- Prioritas --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                                            Prioritas <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="grid grid-cols-3 gap-2">
                                            <button type="button" wire:click="$set('prioritas', 'rendah')"
                                                    class="py-2 px-2 text-center rounded-lg border text-xs font-bold transition {{ $prioritas === 'rendah' ? 'bg-emerald-100 border-emerald-400 text-emerald-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                                🟢 Rendah
                                            </button>
                                            <button type="button" wire:click="$set('prioritas', 'sedang')"
                                                    class="py-2 px-2 text-center rounded-lg border text-xs font-bold transition {{ $prioritas === 'sedang' ? 'bg-amber-100 border-amber-400 text-amber-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                                🟡 Sedang
                                            </button>
                                            <button type="button" wire:click="$set('prioritas', 'tinggi')"
                                                    class="py-2 px-2 text-center rounded-lg border text-xs font-bold transition {{ $prioritas === 'tinggi' ? 'bg-rose-100 border-rose-400 text-rose-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                                🔴 Tinggi
                                            </button>
                                        </div>
                                        @error('prioritas')
                                            <span class="text-[11px] text-rose-500 font-medium block mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- Status --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                                            Status Progress <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button" wire:click="$set('status', 'on_progress')"
                                                    class="py-2 px-2 text-center rounded-lg border text-xs font-bold transition {{ $status === 'on_progress' ? 'bg-blue-100 border-blue-400 text-blue-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                                ⏳ On Progress
                                            </button>
                                            <button type="button" wire:click="$set('status', 'selesai')"
                                                    class="py-2 px-2 text-center rounded-lg border text-xs font-bold transition {{ $status === 'selesai' ? 'bg-emerald-100 border-emerald-400 text-emerald-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                                ✅ Selesai
                                            </button>
                                        </div>
                                        @error('status')
                                            <span class="text-[11px] text-rose-500 font-medium block mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Catatan / Bukti Pengerjaan (Teks biasa opsional) --}}
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                                        Catatan / Bukti Pengerjaan <span class="text-slate-400 font-normal">(Opsional)</span>
                                    </label>
                                    <textarea wire:model="catatan" rows="2"
                                              placeholder="Tuliskan keterangan bukti hasil pengerjaan atau catatan tambahan..."
                                              class="w-full text-xs px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#146C94] focus:border-transparent outline-none transition"></textarea>
                                    @error('catatan')
                                        <span class="text-[11px] text-rose-500 font-medium block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Submit Button --}}
                                <div class="flex items-center justify-end gap-2 pt-2">
                                    @if($editingJournalId)
                                        <button type="button" wire:click="resetForm"
                                                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                                            Batal Edit
                                        </button>
                                    @endif

                                    <button type="submit"
                                            class="px-5 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-[#044564] to-[#146C94] hover:opacity-95 rounded-xl shadow-2xs transition flex items-center gap-1.5">
                                        <span>{{ $editingJournalId ? 'Simpan Perubahan' : '+ Tambah ke Jurnal' }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="p-3 bg-slate-100 text-slate-500 text-xs rounded-xl text-center">
                            Mode pantau (Read-only). Anda sedang melihat jurnal anggota tim.
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/50 flex justify-end">
                    <button type="button" wire:click="closeDayModal"
                            class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 rounded-xl transition">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>
