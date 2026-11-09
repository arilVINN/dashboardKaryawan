@php
    $indicator = fn(string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';
@endphp

<div>
    <div class="flex flex-col gap-3 w-full mt-2">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2.5">
                <span class="shrink-0 rounded-lg bg-[#E3F1F7] p-1.5 text-[#146C94]">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9 2 2 4-4"></path>
                    </svg>
                </span>
                <span>Daftar Tugas</span>
            </h2>
        </div>

        @if (session('tugas_success'))
            <div class="text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-md px-3 py-2">
                {{ session('tugas_success') }}
            </div>
        @endif

        <div class="mt-1 flex flex-col gap-2 min-[700px]:flex-row min-[700px]:items-center">
            <div class="relative w-full shrink-0 min-[700px]:w-[280px]">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6B7280]"
                    fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path>
                </svg>
                <input id="kadivTaskSearch" type="text" wire:model.live.debounce.300ms="search"
                    value="{{ $search }}" placeholder="Cari judul tugas..."
                    class="h-10 w-full rounded-lg border bg-white pl-9 pr-3 text-sm text-[#172B36] outline-none transition placeholder:text-[#6B7280] focus:border-[#146C94] focus:ring-1 focus:ring-[#146C94] {{ $search !== '' ? 'border-[#146C94]' : 'border-[#D5DEE3]' }}">
            </div>

            <div class="flex w-full flex-wrap gap-2 min-[700px]:w-auto min-[700px]:flex-nowrap">
                <select id="kadivTaskStatus" wire:model.live="status"
                    class="h-10 min-w-0 flex-1 rounded-lg border px-3 text-sm outline-none transition focus:border-[#146C94] focus:ring-1 focus:ring-[#146C94] min-[700px]:w-[170px] min-[700px]:flex-none {{ $status !== '' ? 'border-[#146C94] bg-[#E3F1F7] font-medium text-[#0B4A66]' : 'border-[#D5DEE3] bg-white text-[#172B36]' }}">
                    <option value="" @selected($status === '')>Semua status</option>
                    <option value="baru" @selected($status === 'baru')>Baru</option>
                    <option value="berjalan" @selected($status === 'berjalan')>Berjalan</option>
                    <option value="menunggu di-acc" @selected($status === 'menunggu di-acc')>Menunggu persetujuan</option>
                    <option value="sudah di-acc" @selected($status === 'sudah di-acc')>Disetujui</option>
                    <option value="terlambat" @selected($status === 'terlambat')>Terlambat</option>
                </select>

                <select id="kadivTaskStaff" wire:model.live="staff"
                    class="h-10 min-w-0 flex-1 rounded-lg border px-3 text-sm outline-none transition focus:border-[#146C94] focus:ring-1 focus:ring-[#146C94] min-[700px]:w-[190px] min-[700px]:flex-none {{ $staff !== '' ? 'border-[#146C94] bg-[#E3F1F7] font-medium text-[#0B4A66]' : 'border-[#D5DEE3] bg-white text-[#172B36]' }}">
                    <option value="" @selected($staff === '')>Semua staff</option>
                    @foreach ($staffOptions as $option)
                        <option value="{{ $option->id_karyawan }}" @selected((string) $staff === (string) $option->id_karyawan)>{{ $option->nama }}</option>
                    @endforeach
                </select>

                @if ($search !== '' || $status !== '' || $staff !== '')
                    <button type="button" wire:click="resetFilters"
                        class="inline-flex h-10 shrink-0 items-center gap-1 text-sm font-medium text-[#146C94] transition hover:text-[#0B4A66]">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                        </svg>
                        Reset
                    </button>
                @endif
            </div>

            <button type="button" data-modal-open="modalTugasBaru"
                class="inline-flex h-10 w-full shrink-0 items-center justify-center gap-1.5 rounded-lg bg-[#146C94] px-4 text-sm font-medium text-white transition hover:bg-[#0f5878] min-[700px]:ml-auto min-[700px]:w-auto">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                </svg>
                Tambah Tugas
            </button>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-[#EAF4F8] text-[#334155] text-xs">
                        <tr>
                            <th class="px-6 py-4 font-medium whitespace-nowrap text-left">
                                <button type="button" wire:click="sortBy('judul')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Nama Tugas <span
                                        class="{{ $sort === 'judul' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('judul') }}</span>
                                </button>
                            </th>
                            <th class="px-6 py-4 font-medium whitespace-nowrap text-left">
                                <button type="button" wire:click="sortBy('status')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Status <span
                                        class="{{ $sort === 'status' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('status') }}</span>
                                </button>
                            </th>
                            <th class="px-6 py-4 font-medium whitespace-nowrap text-left">
                                <button type="button" wire:click="sortBy('tanggal')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Tenggat <span
                                        class="{{ $sort === 'tanggal' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('tanggal') }}</span>
                                </button>
                            </th>
                            <th class="px-6 py-4 font-medium whitespace-nowrap text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EEF2F5]">
                        @forelse ($rows as $task)
                            @php $status = $task->status_efektif; @endphp
                            <tr wire:key="tugas-{{ $task->id_tugas }}"
                                class="h-[52px] cursor-pointer hover:bg-[#F5FAFC] transition"
                                onclick="window.location.href='{{ url('/kadiv/detailTugas/' . $task->id_tugas) }}'">
                                <td class="px-6 py-4 font-semibold text-slate-800 whitespace-nowrap">
                                    {{ $task->judul_tugas }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-left">
                                    <div class="inline-flex flex-wrap items-center justify-start gap-1">
                                        @if (
                                            $task->is_terlambat &&
                                                in_array($task->status, [\App\Models\Tugas::STATUS_MENUNGGU_ACC, \App\Models\Tugas::STATUS_SUDAH_ACC], true))
                                            <x-status-badge :status="$task->status" />
                                            <x-status-badge status="terlambat" />
                                        @else
                                            <x-status-badge :status="$status" />
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-left whitespace-nowrap">
                                    @php
                                        $deadline = $task->deadline ? \Illuminate\Support\Carbon::parse($task->deadline) : null;
                                        $deadlineLabel = $deadline ? $deadline->format('d M Y') : '-';
                                        if ($deadline && ! in_array($deadline->format('H:i'), ['23:59', '00:00'], true)) {
                                            $deadlineLabel .= ' ' . $deadline->format('H:i');
                                        }
                                    @endphp
                                    {{ $deadlineLabel }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ url('/kadiv/detailTugas/' . $task->id_tugas) }}"
                                        onclick="event.stopPropagation()"
                                        class="text-[14px] font-medium text-[#146C94] hover:text-[#0f5878] transition">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-400">Belum ada tugas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($rows->total() > 0)
                <div class="border-t border-slate-100 px-6 pt-4 pb-6">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] sm:items-center">
                        <p class="text-[12px] text-[#4B5563] sm:text-[13px]">
                            Menampilkan {{ $rows->firstItem() }}-{{ $rows->lastItem() }} dari {{ $rows->total() }} tugas
                        </p>
                        <div class="flex justify-center">
                            @if ($rows->hasPages())
                                {{ $rows->links('vendor.pagination.custom') }}
                            @endif
                        </div>
                        <div class="hidden sm:block" aria-hidden="true"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL BUAT TUGAS BARU --}}
    <div id="modalTugasBaru" wire:ignore.self
        class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-4 bg-[rgba(86,94,116,0.4)] backdrop-blur-sm transition-opacity duration-150 ease-in-out">
        <div data-modal-panel
            class="bg-white rounded-xl w-full max-w-[460px] max-h-[90vh] flex flex-col shadow-[0_8px_16px_rgba(0,0,0,0.12)] scale-95 translate-y-2 transition-all duration-150 ease-in-out">

            <div
                class="flex items-center justify-between px-6 py-4 border-b border-[#E2E8F0] bg-[#F8FAFC] rounded-t-xl">
                <h3 class="text-base font-bold text-black">Buat Tugas Baru</h3>
                <button type="button" data-modal-close="modalTugasBaru"
                    class="text-[#565E74] hover:text-black text-2xl leading-none">&times;</button>
            </div>

            {{-- TAB SWITCHER --}}
            <div class="px-6 pt-4">
                <div class="flex gap-6 border-b border-[#E2E8F0]">
                    <button type="button" wire:click="setTab('tugas')"
                        class="pb-3 text-sm font-semibold border-b-2 transition
                               {{ $activeTab === 'tugas' ? 'border-[#146C94] text-[#146C94]' : 'border-transparent text-[#94A3B8] hover:text-[#565E74]' }}">
                        Tugas
                    </button>
                    <button type="button" wire:click="setTab('project')"
                        class="pb-3 text-sm font-semibold border-b-2 transition
                               {{ $activeTab === 'project' ? 'border-[#146C94] text-[#146C94]' : 'border-transparent text-[#94A3B8] hover:text-[#565E74]' }}">
                        Project
                    </button>
                </div>
            </div>

            {{-- PANEL: TUGAS --}}
            @if ($activeTab === 'tugas')
                <form wire:submit="createTask" class="flex-1 overflow-y-auto flex flex-col min-h-0">
                    <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">
                        <div>
                            <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">JUDUL
                                TUGAS</label>
                            <input type="text" wire:model="judul" maxlength="100" placeholder="Judul tugas"
                                class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none focus:border-[#19A7CE]">
                            @error('judul')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">PENERIMA</label>
                            <select wire:model="recipient"
                                class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] bg-white outline-none focus:border-[#19A7CE]">
                                <option value="">-- Pilih Staff --</option>
                                @foreach ($staffOptions as $option)
                                    <option value="{{ $option->id_karyawan }}">{{ $option->nama }}</option>
                                @endforeach
                                <option value="semua">Semua anggota divisi</option>
                            </select>
                            @error('recipient')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">DESKRIPSI</label>
                            <textarea wire:model="deskripsi" rows="4" placeholder="Tulis deskripsi tugas..."
                                class="w-full resize-none rounded-lg border border-[#CBD5E1] px-3 py-2.5 text-sm text-[#283044] outline-none focus:border-[#19A7CE]"></textarea>
                            @error('deskripsi')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">TENGGAT</label>
                            <input type="datetime-local" wire:model="tenggat"
                                class="w-full h-10 rounded-lg border px-3 text-sm text-[#283044] outline-none cursor-pointer focus:border-[#19A7CE] transition
                                    {{ $errors->has('tenggat') ? 'border-red-500 bg-red-50' : 'border-[#CBD5E1]' }}">
                            @error('tenggat')
                                <div
                                    class="flex items-center gap-1.5 mt-1.5 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                    </svg>
                                    <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                                </div>
                            @enderror
                        </div>

                        <div class="bg-[#F1F5F9] rounded-lg p-4">
                            <label for="fileTugas"
                                class="flex flex-col items-center justify-center text-center gap-1 h-[110px] rounded-lg border-2 border-dashed border-[#CBD5E1] bg-white cursor-pointer hover:border-[#19A7CE] transition">
                                <span
                                    class="text-sm font-bold text-[#283044] px-3 truncate max-w-full">{{ $attachment ? $attachment->getClientOriginalName() : 'Pilih File' }}</span>
                                <span class="text-xs text-[#94A3B8]">Maks 200 MB</span>
                            </label>
                            <input id="fileTugas" type="file" wire:model="attachment" class="hidden">
                            @error('attachment')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-between px-6 py-4 border-t border-[#E2E8F0]">
                        <button type="button" data-modal-close="modalTugasBaru"
                            class="h-9 px-4 bg-[#E8E7E9] rounded-lg text-sm text-[#333335] hover:bg-[#D9D9D9] transition">Batal</button>
                        <button type="submit"
                            class="h-9 px-4 bg-[#146C94] rounded-lg text-sm text-white font-bold hover:bg-[#0f5878] transition"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="createTask">Kirim Tugas</span>
                            <span wire:loading wire:target="createTask">Mengirim...</span>
                        </button>
                    </div>
                </form>
            @endif

            {{-- PANEL: PROJECT --}}
            @if ($activeTab === 'project')
                <form wire:submit="createProject" class="flex-1 overflow-y-auto flex flex-col min-h-0">
                    <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">
                        <div>
                            <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">NAMA
                                PROJECT</label>
                            <input type="text" wire:model="namaProject" maxlength="150"
                                placeholder="Contoh: Project Writer & Markom"
                                class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none focus:border-[#19A7CE]">
                            @error('namaProject')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">DESKRIPSI</label>
                            <textarea wire:model="deskripsiProject" rows="3" maxlength="5000"
                                placeholder="Deskripsikan tujuan atau informasi project..."
                                class="w-full rounded-lg border border-[#CBD5E1] px-3 py-2 text-sm text-[#283044] outline-none focus:border-[#19A7CE]"></textarea>
                            @error('deskripsiProject')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="projectFile"
                                class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">FILE PROJECT
                                (OPSIONAL)</label>
                            <input id="projectFile" type="file" wire:model="projectFile"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                                class="w-full text-sm text-[#283044] file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium">
                            <p class="text-xs text-slate-400 mt-1">PDF, Word, Excel, atau gambar; maks. 200 MB.</p>
                            @error('projectFile')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">TENGGAT</label>
                            <input type="datetime-local" wire:model="tenggatProject"
                                class="w-full h-10 rounded-lg border px-3 text-sm text-[#283044] outline-none cursor-pointer focus:border-[#19A7CE] transition
                                {{ $errors->has('tenggatProject') ? 'border-red-500 bg-red-50' : 'border-[#CBD5E1]' }}">
                            @error('tenggatProject')
                                <div
                                    class="flex items-center gap-1.5 mt-1.5 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                    </svg>
                                    <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                                </div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">DAFTAR
                                TASK
                            </label>

                            <div class="space-y-3">
                                @foreach ($taskLabels as $i => $label)
                                    <div class="flex gap-2 items-start">
                                        <div class="flex-1">
                                            <input type="text" wire:model="taskLabels.{{ $i }}"
                                                placeholder="Nama pekerjaan..."
                                                class="w-full h-10 rounded-lg border border-[#CBD5E1] px-3 text-sm text-[#283044] outline-none focus:border-[#19A7CE]">
                                            @error("taskLabels.$i")
                                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="w-36">
                                            <select wire:model="taskKaryawan.{{ $i }}"
                                                class="w-full h-10 rounded-lg border border-[#CBD5E1] px-2 text-sm text-[#283044] bg-white outline-none focus:border-[#19A7CE]">
                                                <option value="">-- Staff --</option>
                                                @foreach ($projectKaryawanOptions as $option)
                                                    <option value="{{ $option->id_karyawan }}">
                                                        {{ $option->nama }}
                                                        ({{ $option->divisi->nama_divisi ?? 'Tanpa divisi' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("taskKaryawan.$i")
                                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <button type="button" wire:click="removeTaskRow({{ $i }})"
                                            class="shrink-0 w-10 h-10 rounded-lg border border-[#FEE2E2] text-[#B91C1C] hover:bg-[#FEE2E2] transition">&times;</button>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" wire:click="addTaskRow"
                                class="mt-3 text-sm font-semibold text-[#146C94] hover:underline flex items-center gap-1">
                                + Tambah Task
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between px-6 py-4 border-t border-[#E2E8F0]">
                        <button type="button" data-modal-close="modalTugasBaru"
                            class="h-9 px-4 bg-[#E8E7E9] rounded-lg text-sm text-[#333335] hover:bg-[#D9D9D9] transition">Batal</button>
                        <button type="submit"
                            class="h-9 px-4 bg-[#146C94] rounded-lg text-sm text-white font-bold hover:bg-[#0f5878] transition"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="createProject">Buat Project</span>
                            <span wire:loading wire:target="createProject">Membuat...</span>
                        </button>
                    </div>
                </form>
            @endif

        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('tutup-modal-tugas', () => {
                if (window.setModal) {
                    window.setModal('modalTugasBaru', false);
                }
            });
        });
    </script>
</div>
