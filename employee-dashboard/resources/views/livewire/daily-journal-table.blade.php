<div class="space-y-4">
    <style>
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

    {{-- Toolbar: Navigasi Minggu + Judul Periode + Filter Dropdown --}}
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

        {{-- Role Tabs & Dropdowns Filter --}}
        <div class="flex flex-wrap items-center gap-2">
            @if(in_array($role, ['kadiv', 'hrd', 'direktur', 'direksi', 'superadmin'], true))
                <div class="cal-view-group">
                    <button type="button" wire:click="setViewMode('my')"
                            class="cal-btn {{ $viewMode === 'my' ? 'is-active' : '' }}">
                        To Do Saya
                    </button>

                    @if($role === 'kadiv')
                        <button type="button" wire:click="setViewMode('team')"
                                class="cal-btn {{ $viewMode === 'team' ? 'is-active' : '' }}">
                            To Do Tim
                        </button>
                        <button type="button" wire:click="setViewMode('rekap_divisi')"
                                class="cal-btn {{ $viewMode === 'rekap_divisi' ? 'is-active' : '' }}">
                            Progres Divisi
                        </button>
                    @elseif($role === 'hrd')
                        <button type="button" wire:click="setViewMode('all')"
                                class="cal-btn {{ $viewMode === 'all' ? 'is-active' : '' }}">
                            Monitoring Karyawan
                        </button>
                        <button type="button" wire:click="setViewMode('rekap_divisi')"
                                class="cal-btn {{ $viewMode === 'rekap_divisi' ? 'is-active' : '' }}">
                            Progres Divisi
                        </button>
                    @elseif(in_array($role, ['direktur', 'direksi', 'superadmin'], true))
                        <button type="button" wire:click="setViewMode('all')"
                                class="cal-btn {{ $viewMode === 'all' ? 'is-active' : '' }}">
                            Monitoring Karyawan
                        </button>
                    @endif
                </div>

                {{-- Dropdown Divisi & Karyawan --}}
                @if($viewMode === 'rekap_divisi')
                    @if(in_array($role, ['hrd', 'direktur', 'direksi', 'superadmin'], true))
                        <select wire:model.live="filterDivisiId" class="text-xs px-2.5 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#19A7CE] outline-none">
                            <option value="">Semua Divisi</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id_divisi }}">{{ $div->nama_divisi }}</option>
                            @endforeach
                        </select>
                    @endif
                @elseif($viewMode !== 'my')
                    @if(in_array($role, ['hrd', 'direktur', 'direksi', 'superadmin'], true))
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
                <span class="text-slate-500">Progres:</span>
                <span class="font-bold px-2 py-0.5 rounded-full {{ $stats['activePercentage'] >= 80 ? 'bg-emerald-100 text-emerald-800' : ($stats['activePercentage'] >= 50 ? 'bg-sky-100 text-sky-800' : 'bg-amber-100 text-amber-800') }}">
                    {{ $stats['activePercentage'] }}%
                </span>
                @if($activeDivisi)
                    <span class="text-slate-400">| Divisi: <strong class="text-slate-800 font-bold">{{ $stats['divisiWeeklyPercentage'] }}%</strong></span>
                @endif
            </div>

            @if($viewMode === 'my' || ($canAssignTodos && $viewMode !== 'rekap_divisi' && $filterKaryawanId))
                <button type="button" wire:click="openCreateModal"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#0097B2] hover:bg-[#008199] text-white text-xs font-bold shadow-2xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>{{ $viewMode === 'my' ? 'Tambah To Do' : 'Tugaskan To Do' }}</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Feedback Message --}}
    @if($feedbackMessage && ! $isModalOpen)
        <div class="p-3.5 rounded-xl border text-xs font-medium {{ $feedbackType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }} flex items-center justify-between">
            <span>{{ $feedbackMessage }}</span>
            <button type="button" wire:click="resetFeedback" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
    @endif

    @if($viewMode === 'rekap_divisi')
        {{-- VIEW PROGRES DIVISI --}}
        <div class="space-y-6">
            {{-- KPI Card Ringkasan Capaian Divisi --}}
            <div class="bg-gradient-to-r from-[#146C94] to-[#0A4D68] rounded-2xl p-6 text-white shadow-md relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="relative z-10">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-white/15 text-white backdrop-blur-xs mb-2">
                        📊 Persentase Divisi Minggu Ini
                    </span>
                    <h3 class="text-2xl font-bold tracking-tight text-white mb-1">
                        {{ $activeDivisi->nama_divisi ?? 'Divisi' }}
                    </h3>
                    <p class="text-xs text-sky-100">
                        Periode: {{ $weekTitle }}
                    </p>
                </div>

                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center gap-6">
                    <div class="min-w-[220px]">
                        <div class="flex items-baseline justify-between mb-1.5">
                            <span class="text-xs text-sky-200">Total Capaian Divisi</span>
                            <span class="text-3xl font-black tracking-tight text-white">
                                {{ $divisiWeeklyPercentage }}%
                            </span>
                        </div>
                        {{-- Bar Indikator --}}
                        <div class="w-full bg-white/20 rounded-full h-3 overflow-hidden">
                            <div class="bg-emerald-400 h-3 rounded-full transition-all duration-500" style="width: {{ min(100, $divisiWeeklyPercentage) }}%"></div>
                        </div>
                    </div>

                    <div class="sm:pl-6 sm:border-l border-white/20 text-xs text-sky-100 flex flex-col justify-center">
                        <span class="text-sky-200">Total To Do Minggu Ini</span>
                        <strong class="text-white text-xl font-bold">{{ $totalDivisiTasks }} Tugas</strong>
                    </div>
                </div>

                {{-- Background decorative bubble --}}
                <div class="absolute -right-8 -bottom-8 w-48 h-48 rounded-full bg-white/5 pointer-events-none"></div>
            </div>

            {{-- Tabel Daftar Capaian Seluruh Anggota Divisi --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Capaian Progres Karyawan & Kadiv</h4>
                        <p class="text-xs text-slate-500">Daftar capaian persentase mingguan masing-masing anggota per week.</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-600">
                        {{ $staffProgressList->count() }} Anggota
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-bold uppercase text-[11px] tracking-wider border-b border-slate-200">
                                <th class="py-3.5 px-6">Karyawan</th>
                                <th class="py-3.5 px-4 text-center">Peran</th>
                                <th class="py-3.5 px-4 text-center">Total To Do</th>
                                <th class="py-3.5 px-4 text-center">Rincian Status</th>
                                <th class="py-3.5 px-6 min-w-[200px]">Persentase Minggu Ini</th>
                                <th class="py-3.5 px-6 text-center">Aksi Review</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($staffProgressList as $sp)
                                <tr class="hover:bg-slate-50/70 transition">
                                    {{-- Karyawan --}}
                                    <td class="py-3.5 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-[#146C94] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                                {{ strtoupper(substr($sp['karyawan']->nama ?? 'K', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800">{{ $sp['karyawan']->nama }}</div>
                                                <div class="text-[11px] text-slate-400">{{ $sp['karyawan']->jabatan?->nama_jabatan ?? 'Karyawan' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Peran --}}
                                    <td class="py-3.5 px-4 text-center">
                                        @if($sp['isKadiv'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                Kepala Divisi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                Staff
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Total To Do --}}
                                    <td class="py-3.5 px-4 text-center font-bold text-slate-700">
                                        {{ $sp['totalTasks'] }} Tugas
                                    </td>

                                    {{-- Rincian Status --}}
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                            @if($sp['count100'] > 0)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800" title="Fix 100%">
                                                    {{ $sp['count100'] }} Fix (100%)
                                                </span>
                                            @endif
                                            @if($sp['count90'] > 0)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#D1F4D9] text-[#137333]" title="Completed 90%">
                                                    {{ $sp['count90'] }} Selesai (90%)
                                                </span>
                                            @endif
                                            @if($sp['count50Progress'] > 0)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E8F0FE] text-[#1967D2]" title="In Progress 50%">
                                                    {{ $sp['count50Progress'] }} Progress (50%)
                                                </span>
                                            @endif
                                            @if($sp['count50Revisi'] > 0)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800" title="Revisi 50%">
                                                    {{ $sp['count50Revisi'] }} Revisi (50%)
                                                </span>
                                            @endif
                                            @if($sp['count0'] > 0)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600" title="Pending 0%">
                                                    {{ $sp['count0'] }} Pending (0%)
                                                </span>
                                            @endif
                                            @if($sp['totalTasks'] === 0)
                                                <span class="text-slate-400 italic text-[11px]">- Kosong -</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Persentase Minggu Ini --}}
                                    <td class="py-3.5 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                                                <div class="h-2 rounded-full transition-all duration-300 {{ $sp['pct'] >= 80 ? 'bg-emerald-500' : ($sp['pct'] >= 50 ? 'bg-sky-500' : 'bg-amber-500') }}"
                                                     style="width: {{ min(100, $sp['pct']) }}%"></div>
                                            </div>
                                            <span class="font-extrabold text-xs text-slate-800 w-12 text-right">
                                                {{ $sp['pct'] }}%
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Aksi Review --}}
                                    <td class="py-3.5 px-6 text-center">
                                        <button type="button"
                                                wire:click="selectStaffForReview('{{ $sp['karyawan']->id_karyawan }}')"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#EAF4F8] hover:bg-[#D4EAF3] text-[#146C94] text-xs font-bold transition">
                                            <span>Lihat / Review</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        Tidak ada data anggota divisi yang ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        {{-- Tabel To Do Mingguan (Format Spreadsheet Pastel Pink Sesuai Desain) --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#146C94] border-b border-[#0116d1] text-white text-xs font-bold uppercase tracking-wider">
                            <th class="py-3 px-4 min-w-[280px] border-r border-dashed border-[#F3B7C4]">Tugas</th>
                            <th class="py-3 px-3 w-36 text-center border-r border-dashed border-[#F3B7C4]">Status</th>
                            <th class="py-3 px-3 w-32 text-center border-r border-dashed border-[#F3B7C4]">Prioritas</th>
                            <th class="py-3 px-4 min-w-[220px] border-r border-dashed border-[#F3B7C4]">Catatan / Bukti</th>
                            <th class="py-3 px-4 w-24 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dashed divide-slate-200 text-xs bg-white">
                        @foreach($calendarDays as $day)
                            @php
                                $rowBg = 'hover:bg-rose-50/20 transition group';
                            if ($day['isToday']) {
                                $rowBg = 'bg-sky-50/30 hover:bg-sky-50/60 transition group';
                            }
                        @endphp
                        <tr class="{{ $rowBg }}">
                            {{-- 1. Tugas (Memuat Hari & Daftar To Do dalam Baris yang Sama) --}}
                            <td class="py-3 px-4 align-top border-r border-dashed border-slate-200 font-medium text-slate-800">
                                {{-- Label Hari & Tanggal --}}
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded font-bold text-[11px] {{ $day['isToday'] ? 'bg-[#146C94] text-white shadow-2xs' : ($day['isWeekend'] ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700') }}">
                                        {{ $day['dayName'] }}, {{ $day['shortFormatted'] }}
                                    </span>
                                    @if($day['isToday'])
                                        <span class="text-[10px] font-bold text-[#146C94]">Hari Ini</span>
                                    @endif
                                    @if($day['holiday'])
                                        <span class="text-[10px] font-semibold text-rose-600 truncate max-w-[140px]" title="{{ $day['holiday'] }}">
                                            {{ $day['holiday'] }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Daftar To Do pada Baris Hari Ini --}}
                                @if($day['journals']->isNotEmpty())
                                    <div class="space-y-1.5">
                                        @foreach($day['journals'] as $jurnal)
                                            <div class="h-8 flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $jurnal->status === 'selesai' ? 'bg-emerald-500' : 'bg-blue-500' }}"></span>
                                                <span class="{{ $jurnal->status === 'selesai' ? 'line-through text-slate-400' : 'text-slate-800 font-medium' }} truncate max-w-[260px]">
                                                    {{ $jurnal->aktivitas }}
                                                </span>
                                                {{-- Badge: Todo ditugaskan oleh atasan --}}
                                                @if($jurnal->is_assigned_by_other)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-violet-100 text-violet-700 border border-violet-200 shrink-0 ml-1">
                                                        Dari: {{ $jurnal->assigned_by_label }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="h-8 flex items-center text-slate-300 italic text-xs">
                                        - Belum ada to do
                                    </div>
                                @endif

                                {{-- Tombol Tambah To Do --}}
                                @if($viewMode === 'my' || ($canAssignTodos && $viewMode !== 'rekap_divisi' && $filterKaryawanId))
                                    <button type="button"
                                            wire:click="openCreateModal('{{ $day['date'] }}', '{{ $filterKaryawanId ?: '' }}')"
                                            class="inline-flex items-center gap-1 mt-2 text-[11px] font-bold text-[#146C94] hover:text-[#0b4d6b] hover:underline">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span>{{ $viewMode !== 'my' ? 'Tugaskan To Do' : 'Tambah to do' }}</span>
                                    </button>
                                @endif
                            </td>

                            {{-- 2. Status Progress --}}
                            <td class="py-3 px-3 align-top text-center border-r border-dashed border-slate-200">
                                {{-- Spacer penyeimbang label hari --}}
                                <div class="h-6 mb-2"></div>

                                @if($day['journals']->isNotEmpty())
                                    <div class="space-y-1.5">
                                        @foreach($day['journals'] as $jurnal)
                                            @php
                                                $statusColor = match($jurnal->status) {
                                                    'disetujui' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                                    'selesai' => 'bg-[#D1F4D9] text-[#137333] border-[#A6E9B9]',
                                                    'on_progress' => 'bg-[#E8F0FE] text-[#1967D2] border-[#C2D7FA]',
                                                    'revisi' => 'bg-amber-100 text-amber-800 border-amber-300',
                                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                                };
                                                $statusLabel = match($jurnal->status) {
                                                    'disetujui' => 'Fix (100%)',
                                                    'selesai' => 'Completed (90%)',
                                                    'on_progress' => 'In Progress (50%)',
                                                    'revisi' => 'Revisi (50%)',
                                                    default => 'Pending (0%)',
                                                };
                                                $canEditStatus = ! $isViewingOther || in_array($role, ['kadiv', 'hrd', 'direktur', 'direksi', 'superadmin'], true);
                                            @endphp
                                            <div class="h-8 flex items-center justify-center">
                                                @if($canEditStatus)
                                                    <div class="relative inline-block">
                                                        <select wire:change="updateStatus({{ $jurnal->id }}, $event.target.value)"
                                                                class="appearance-none cursor-pointer pl-2.5 pr-6 py-1 rounded-full text-[10px] sm:text-[11px] font-bold border transition outline-none {{ $statusColor }}">
                                                            @if(! $isViewingOther && $role === 'staff')
                                                                {{-- Opsi untuk Staff pada To Do Sendiri --}}
                                                                <option value="pending" {{ $jurnal->status === 'pending' ? 'selected' : '' }}>Pending (0%)</option>
                                                                <option value="on_progress" {{ $jurnal->status === 'on_progress' ? 'selected' : '' }}>In Progress (50%)</option>
                                                                <option value="selesai" {{ $jurnal->status === 'selesai' ? 'selected' : '' }}>Completed (90%)</option>
                                                                @if($jurnal->status === 'revisi')
                                                                    <option value="revisi" selected disabled>Revisi Kadiv (50%)</option>
                                                                @elseif($jurnal->status === 'disetujui')
                                                                    <option value="disetujui" selected disabled>Fix Kadiv (100%)</option>
                                                                @endif
                                                            @else
                                                                {{-- Opsi untuk Kadiv / HRD --}}
                                                                <option value="pending" {{ $jurnal->status === 'pending' ? 'selected' : '' }}>Pending (0%)</option>
                                                                <option value="on_progress" {{ $jurnal->status === 'on_progress' ? 'selected' : '' }}>In Progress (50%)</option>
                                                                <option value="selesai" {{ $jurnal->status === 'selesai' ? 'selected' : '' }}>Completed (90%)</option>
                                                                <option value="revisi" {{ $jurnal->status === 'revisi' ? 'selected' : '' }}>Revisi (50%)</option>
                                                                <option value="disetujui" {{ $jurnal->status === 'disetujui' ? 'selected' : '' }}>Fix (100%)</option>
                                                            @endif
                                                        </select>
                                                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-[9px] opacity-70">▾</span>
                                                    </div>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $statusColor }}">
                                                        {{ $statusLabel }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="h-8 flex items-center justify-center">
                                        <div class="inline-flex items-center justify-between px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-100/80 text-slate-400 border border-slate-200 w-28 mx-auto">
                                            <span>-</span>
                                            <span class="text-[9px]">▾</span>
                                        </div>
                                    </div>
                                @endif

                                {{-- Spacer penyeimbang tombol tambah --}}
                                @if(! $isViewingOther)
                                    <div class="mt-2 h-4"></div>
                                @endif
                            </td>

                            {{-- 3. Prioritas --}}
                            <td class="py-3 px-3 align-top text-center border-r border-dashed border-slate-200">
                                {{-- Spacer penyeimbang label hari --}}
                                <div class="h-6 mb-2"></div>

                                @if($day['journals']->isNotEmpty())
                                    <div class="space-y-1.5">
                                        @foreach($day['journals'] as $jurnal)
                                            <div class="h-8 flex items-center justify-center">
                                                @if(! $isViewingOther)
                                                    <div class="relative inline-block">
                                                        <select wire:change="updatePrioritas({{ $jurnal->id }}, $event.target.value)"
                                                                class="appearance-none cursor-pointer pl-3 pr-6 py-1 rounded-full text-[11px] font-bold border transition outline-none
                                                                {{ $jurnal->prioritas === 'tinggi'
                                                                    ? 'bg-[#FEE4D2] text-[#C4320A] border-[#FED1B5]'
                                                                    : ($jurnal->prioritas === 'sedang'
                                                                        ? 'bg-[#FEF0C3] text-[#B45309] border-[#FDE68A]'
                                                                        : 'bg-[#DCFCE7] text-[#15803D] border-[#BBF7D0]') }}">
                                                            <option value="tinggi" {{ $jurnal->prioritas === 'tinggi' ? 'selected' : '' }}>High</option>
                                                            <option value="sedang" {{ $jurnal->prioritas === 'sedang' ? 'selected' : '' }}>Medium</option>
                                                            <option value="rendah" {{ $jurnal->prioritas === 'rendah' ? 'selected' : '' }}>Low</option>
                                                        </select>
                                                        <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-[9px]
                                                            {{ $jurnal->prioritas === 'tinggi' ? 'text-[#C4320A]' : ($jurnal->prioritas === 'sedang' ? 'text-[#B45309]' : 'text-[#15803D]') }}">▾</span>
                                                    </div>
                                                @else
                                                    @php
                                                        $pClass = match($jurnal->prioritas) {
                                                            'tinggi' => 'bg-[#FEE4D2] text-[#C4320A] border-[#FED1B5]',
                                                            'sedang' => 'bg-[#FEF0C3] text-[#B45309] border-[#FDE68A]',
                                                            'rendah' => 'bg-[#DCFCE7] text-[#15803D] border-[#BBF7D0]',
                                                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                                                        };
                                                        $pLabel = match($jurnal->prioritas) {
                                                            'tinggi' => 'High',
                                                            'sedang' => 'Medium',
                                                            'rendah' => 'Low',
                                                            default => ucfirst($jurnal->prioritas),
                                                        };
                                                    @endphp
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $pClass }}">
                                                        {{ $pLabel }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="h-8 flex items-center justify-center">
                                        <div class="inline-flex items-center justify-between px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-100/80 text-slate-400 border border-slate-200 w-24 mx-auto">
                                            <span>-</span>
                                            <span class="text-[9px]">▾</span>
                                        </div>
                                    </div>
                                @endif

                                {{-- Spacer penyeimbang tombol tambah --}}
                                @if(! $isViewingOther)
                                    <div class="mt-2 h-4"></div>
                                @endif
                            </td>

                            {{-- 4. Catatan / Bukti Pengerjaan --}}
                            <td class="py-3 px-4 align-top border-r border-dashed border-slate-200">
                                {{-- Spacer penyeimbang label hari --}}
                                <div class="h-6 mb-2"></div>

                                @if($day['journals']->isNotEmpty())
                                    <div class="space-y-1.5">
                                        @foreach($day['journals'] as $jurnal)
                                            <div class="h-8 flex items-center">
                                                @if($jurnal->file_bukti)
                                                    <a href="{{ asset('storage/' . $jurnal->file_bukti) }}" target="_blank"
                                                       class="inline-flex items-center gap-1.5 font-semibold text-rose-600 hover:text-rose-800 hover:underline">
                                                        <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span class="truncate max-w-[180px]">{{ $jurnal->file_nama_asli ?: 'Screenshot' }}</span>
                                                    </a>
                                                @elseif($jurnal->link_bukti)
                                                    <a href="{{ $jurnal->link_bukti }}" target="_blank"
                                                       class="inline-flex items-center gap-1 font-medium text-sky-600 hover:text-sky-800 hover:underline">
                                                        <svg class="w-3 h-3 text-sky-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                        </svg>
                                                        <span class="truncate max-w-[180px]">{{ $jurnal->link_bukti }}</span>
                                                    </a>
                                                @elseif(!empty($jurnal->catatan))
                                                    <p class="text-[11px] text-slate-600 truncate max-w-[180px]">{{ $jurnal->catatan }}</p>
                                                @else
                                                    <span class="text-slate-300">-</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="h-8 flex items-center text-slate-300">
                                        -
                                    </div>
                                @endif

                                {{-- Spacer penyeimbang tombol tambah --}}
                                @if(! $isViewingOther)
                                    <div class="mt-2 h-4"></div>
                                @endif
                            </td>

                            {{-- 5. Aksi --}}
                            <td class="py-3 px-4 align-top text-center">
                                {{-- Spacer penyeimbang label hari --}}
                                <div class="h-6 mb-2"></div>

                                @if($day['journals']->isNotEmpty())
                                    <div class="space-y-1.5">
                                        @foreach($day['journals'] as $jurnal)
                                            <div class="h-8 flex items-center justify-center gap-1">
                                                @if(! $isViewingOther)
                                                    <button type="button" wire:click="editJournal({{ $jurnal->id }})"
                                                            class="p-1 text-slate-400 hover:text-blue-600 rounded transition" title="Edit">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                        </svg>
                                                    </button>

                                                    <button type="button" wire:confirm="Hapus to do ini?" wire:click="deleteJournal({{ $jurnal->id }})"
                                                            class="p-1 text-slate-400 hover:text-rose-600 rounded transition" title="Hapus">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                @else
                                                    <span class="text-slate-300">-</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="h-8 flex items-center justify-center">
                                        @if(! $isViewingOther && ($viewMode === 'my' || ($canAssignTodos && $filterKaryawanId)))
                                            <button type="button" wire:click="openCreateModal('{{ $day['date'] }}')"
                                                    class="text-[11px] text-[#146C94] font-semibold hover:underline">
                                                {{ $viewMode === 'my' ? 'Tambah' : 'Tugaskan' }}
                                            </button>
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Spacer penyeimbang tombol tambah --}}
                                @if(! $isViewingOther)
                                    <div class="mt-2 h-4"></div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- MODAL INPUT / EDIT TO DO --}}
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-2xl max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">

                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                    <div>
                        <span class="text-xs font-bold text-[#146C94] uppercase tracking-wider">To Do Harian</span>
                        <h3 class="text-base font-bold text-slate-800">
                            {{ $editingJournalId ? 'Edit To Do' : 'Tambah To Do Baru' }}
                        </h3>
                    </div>
                    <button type="button" wire:click="closeModal" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 overflow-y-auto space-y-4 flex-1">
                    @if($feedbackMessage)
                        <div class="p-3.5 rounded-xl border text-xs font-medium {{ $feedbackType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }} flex items-center justify-between">
                            <span>{{ $feedbackMessage }}</span>
                            <button type="button" wire:click="resetFeedback" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                    @endif

                    @if(! $isViewingOther)
                        <form wire:submit="saveJournal" class="space-y-4">
                            @if($canAssignTodos && ! $editingJournalId)
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                                        Penerima To Do <span class="text-rose-500">*</span>
                                    </label>
                                    <select wire:model="targetKaryawanId"
                                            class="w-full text-xs px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#146C94] outline-none">
                                        <option value="">Pilih karyawan</option>
                                        @foreach($assignableKaryawan as $karyawan)
                                            <option value="{{ $karyawan->id_karyawan }}">
                                                {{ $karyawan->nama }}{{ $karyawan->user?->role?->nama_role ? ' — '.strtoupper($karyawan->user->role->nama_role) : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-[11px] text-slate-500">
                                        @if($role === 'kadiv')
                                            To Do hanya dapat diberikan kepada karyawan di divisi Anda.
                                        @else
                                            Penerima akan melihat nama Anda sebagai pemberi To Do.
                                        @endif
                                    </p>
                                    @error('targetKaryawanId')
                                        <span class="text-[11px] text-rose-500 font-medium block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif

                            {{-- Tugas / Uraian Pekerjaan --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tugas / Uraian Pekerjaan <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" wire:model="aktivitas"
                                       placeholder="Tuliskan tugas pekerjaan kamu (contoh: [rabu] perbaiki bug laporan keuangan)..."
                                       class="w-full text-xs px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#146C94] focus:border-transparent outline-none transition">
                                @error('aktivitas')
                                    <span class="text-[11px] text-rose-500 font-medium block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Pilih Hari (Dalam Minggu Ini) --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Hari Pengerjaan
                                </label>
                                <select wire:model="modalDate"
                                        class="w-full text-xs px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#146C94] outline-none">
                                    @foreach($daysOfWeek as $dow)
                                        <option value="{{ $dow['date'] }}">{{ $dow['label'] }}{{ $dow['isToday'] ? ' (Hari Ini)' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Prioritas & Status --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Prioritas --}}
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                                        Prioritas <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button type="button" wire:click="$set('prioritas', 'tinggi')"
                                                class="py-2 px-1 text-center rounded-lg border text-xs font-bold transition
                                                {{ $prioritas === 'tinggi' ? 'bg-[#FEE4D2] border-[#C4320A] text-[#C4320A] shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                            High
                                        </button>
                                        <button type="button" wire:click="$set('prioritas', 'sedang')"
                                                class="py-2 px-1 text-center rounded-lg border text-xs font-bold transition
                                                {{ $prioritas === 'sedang' ? 'bg-[#FEF0C3] border-[#B45309] text-[#B45309] shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                            Medium
                                        </button>
                                        <button type="button" wire:click="$set('prioritas', 'rendah')"
                                                class="py-2 px-1 text-center rounded-lg border text-xs font-bold transition
                                                {{ $prioritas === 'rendah' ? 'bg-[#DCFCE7] border-[#15803D] text-[#15803D] shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                            Low
                                        </button>
                                    </div>
                                    @error('prioritas')
                                        <span class="text-[11px] text-rose-500 font-medium block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Status Progress --}}
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                                        Status Progress <span class="text-rose-500">*</span>
                                    </label>
                                    @if(in_array($role, ['kadiv', 'hrd'], true))
                                        <div class="grid grid-cols-3 sm:grid-cols-5 gap-1.5">
                                            <button type="button" wire:click="$set('status', 'pending')"
                                                    class="py-1.5 px-1 text-center rounded-lg border text-[11px] font-bold transition
                                                    {{ $status === 'pending' ? 'bg-slate-200 border-slate-400 text-slate-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                                Pending (0%)
                                            </button>
                                            <button type="button" wire:click="$set('status', 'on_progress')"
                                                    class="py-1.5 px-1 text-center rounded-lg border text-[11px] font-bold transition
                                                    {{ $status === 'on_progress' ? 'bg-[#E8F0FE] border-[#1967D2] text-[#1967D2] shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                                Progress (50%)
                                            </button>
                                            <button type="button" wire:click="$set('status', 'selesai')"
                                                    class="py-1.5 px-1 text-center rounded-lg border text-[11px] font-bold transition
                                                    {{ $status === 'selesai' ? 'bg-[#D1F4D9] border-[#137333] text-[#137333] shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                                Complete (90%)
                                            </button>
                                            <button type="button" wire:click="$set('status', 'revisi')"
                                                    class="py-1.5 px-1 text-center rounded-lg border text-[11px] font-bold transition
                                                    {{ $status === 'revisi' ? 'bg-amber-100 border-amber-400 text-amber-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                                Revisi (50%)
                                            </button>
                                            <button type="button" wire:click="$set('status', 'disetujui')"
                                                    class="py-1.5 px-1 text-center rounded-lg border text-[11px] font-bold transition
                                                    {{ $status === 'disetujui' ? 'bg-emerald-100 border-emerald-400 text-emerald-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                                Fix (100%)
                                            </button>
                                        </div>
                                    @else
                                        <div class="grid grid-cols-3 gap-2">
                                            <button type="button" wire:click="$set('status', 'pending')"
                                                    class="py-2 px-1 text-center rounded-lg border text-xs font-bold transition
                                                    {{ $status === 'pending' ? 'bg-slate-200 border-slate-400 text-slate-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                                Pending (0%)
                                            </button>
                                            <button type="button" wire:click="$set('status', 'on_progress')"
                                                    class="py-2 px-1 text-center rounded-lg border text-xs font-bold transition
                                                    {{ $status === 'on_progress' ? 'bg-[#E8F0FE] border-[#1967D2] text-[#1967D2] shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                                In Progress (50%)
                                            </button>
                                            <button type="button" wire:click="$set('status', 'selesai')"
                                                    class="py-2 px-1 text-center rounded-lg border text-xs font-bold transition
                                                    {{ $status === 'selesai' ? 'bg-[#D1F4D9] border-[#137333] text-[#137333] shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                                Complete (90%)
                                            </button>
                                        </div>
                                    @endif
                                    @error('status')
                                        <span class="text-[11px] text-rose-500 font-medium block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Upload File Bukti Pekerjaan --}}
                            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 space-y-2">
                                <label class="block text-xs font-semibold text-slate-700">
                                    File Bukti Pekerjaan <span class="text-slate-400 font-normal">(Screenshot / Gambar / Dokumen)</span>
                                </label>

                                @if($existingFileBukti)
                                    <div class="flex items-center justify-between p-2 bg-white border border-rose-200 rounded-lg text-xs">
                                        <div class="flex items-center gap-2 truncate">
                                            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="font-medium text-slate-700 truncate">{{ $existingFileNamaAsli ?: 'File Bukti' }}</span>
                                        </div>
                                        <button type="button" wire:click="removeExistingFile"
                                                class="text-xs font-semibold text-rose-600 hover:text-rose-800 shrink-0 ml-2">
                                            Hapus
                                        </button>
                                    </div>
                                @endif

                                <input type="file" wire:model="fileBukti"
                                       accept="image/*,.pdf,.doc,.docx,.zip,.rar"
                                       class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#EAF4F8] file:text-[#146C94] hover:file:bg-[#D4EAF3] cursor-pointer">

                                <div wire:loading wire:target="fileBukti" class="text-[11px] text-[#146C94] font-medium">
                                    Mengunggah file bukti...
                                </div>

                                @error('fileBukti')
                                    <span class="text-[11px] text-rose-500 font-medium block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Link Bukti Pekerjaan --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Link Bukti Pekerjaan <span class="text-slate-400 font-normal">(Tautan Google Drive, Figma, GitHub, dll.)</span>
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">
                                        🔗
                                    </span>
                                    <input type="text" wire:model="linkBukti"
                                           placeholder="https://..."
                                           class="w-full text-xs pl-8 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#146C94] focus:border-transparent outline-none transition">
                                </div>
                                @error('linkBukti')
                                    <span class="text-[11px] text-rose-500 font-medium block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Catatan Tambahan (Opsional) --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Catatan Tambahan <span class="text-slate-400 font-normal">(Opsional)</span>
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
                                <button type="button" wire:click="closeModal"
                                        class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                                    Batal
                                </button>

                                <button type="submit"
                                        wire:loading.attr="disabled"
                                        class="px-5 py-2 text-xs font-bold text-white bg-[#0097B2] hover:bg-[#008199] rounded-xl shadow-2xs transition flex items-center gap-1.5">
                                    <span wire:loading.remove wire:target="saveJournal,fileBukti">
                                        {{ $editingJournalId ? 'Simpan Perubahan' : 'Tambah ke To Do' }}
                                    </span>
                                    <span wire:loading wire:target="saveJournal,fileBukti">
                                        Menyimpan...
                                    </span>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="p-4 bg-slate-100 text-slate-600 text-xs rounded-xl text-center">
                            Mode pantau (Read-only). Anda sedang melihat to do karyawan.
                        </div>
                    @endif
                </div>

            </div>
        </div>
    @endif
</div>
