@php
    $indicator = fn(string $col) => $sort === $col ? ($dir === 'asc' ? '▲' : '▼') : '↕';
@endphp

<div>
    <div class="flex flex-col gap-3 w-full mt-2">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-slate-800">Daftar Tugas</h2>
            <button type="button" data-modal-open="modalTugasBaru"
                class="text-sm px-4 py-2 bg-[#0097B2] text-white rounded-lg font-medium hover:bg-[#008199] transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                </svg>
                Tambah Tugas
            </button>
        </div>

        @if (session('tugas_success'))
            <div class="text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-md px-3 py-2">
                {{ session('tugas_success') }}
            </div>
        @endif

        <div class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 rounded-xl shadow-sm p-5">
            <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
                <label for="kadivTaskSearch" class="text-xs font-bold text-slate-500">Cari Judul</label>
                <input id="kadivTaskSearch" type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Judul tugas..."
                    class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
            </div>
            <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
                <label for="kadivTaskStatus" class="text-xs font-bold text-slate-500">Status</label>
                <select id="kadivTaskStatus" wire:model.live="status"
                    class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                    <option value="">Semua</option>
                    <option value="baru">Baru</option>
                    <option value="berjalan">Berjalan</option>
                    <option value="menunggu di-acc">Menunggu di-acc</option>
                    <option value="sudah di-acc">Sudah di-acc</option>
                    <option value="terlambat">Terlambat</option>
                </select>
            </div>
            <div class="flex flex-1 min-w-[10rem] max-w-[16rem] flex-col gap-1.5">
                <label for="kadivTaskStaff" class="text-xs font-bold text-slate-500">Staff</label>
                <select id="kadivTaskStaff" wire:model.live="staff"
                    class="h-10 w-full px-3 border border-slate-300 rounded-md text-sm outline-none focus:border-[#004A65]">
                    <option value="">Semua Staff</option>
                    @foreach ($staffOptions as $option)
                        <option value="{{ $option->id_karyawan }}">{{ $option->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex shrink-0 gap-2 sm:ml-auto">
                <button type="button" wire:click="resetFilters"
                    class="h-10 inline-flex items-center px-4 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">Reset</button>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-4 font-medium whitespace-nowrap">
                                <button type="button" wire:click="sortBy('judul')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Nama Tugas <span
                                        class="{{ $sort === 'judul' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('judul') }}</span>
                                </button>
                            </th>
                            <th class="px-6 py-4 font-medium whitespace-nowrap text-center">
                                <button type="button" wire:click="sortBy('status')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Keterangan <span
                                        class="{{ $sort === 'status' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('status') }}</span>
                                </button>
                            </th>
                            <th class="px-6 py-4 font-medium whitespace-nowrap text-center">
                                <button type="button" wire:click="sortBy('tanggal')"
                                    class="inline-flex items-center gap-1 hover:text-[#004A65]">
                                    Tanggal <span
                                        class="{{ $sort === 'tanggal' ? 'text-[#004A65]' : 'text-slate-300' }}">{{ $indicator('tanggal') }}</span>
                                </button>
                            </th>
                            <th class="px-6 py-4 font-medium whitespace-nowrap text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($rows as $task)
                            @php $status = $task->status_efektif; @endphp
                            <tr wire:key="tugas-{{ $task->id_tugas }}" class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-800 whitespace-nowrap">
                                    {{ $task->judul_tugas }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="inline-flex gap-1">
                                        @if (
                                            $task->is_terlambat &&
                                                in_array($task->status, [\App\Models\Tugas::STATUS_MENUNGGU_ACC, \App\Models\Tugas::STATUS_SUDAH_ACC], true))
                                            <span
                                                class="px-2.5 py-1 rounded-full text-xs font-bold {{ $task->status === \App\Models\Tugas::STATUS_SUDAH_ACC ? 'bg-[#CCF4DB] text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $task->status }}</span>
                                            <span
                                                class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">Terlambat</span>
                                        @else
                                            @php
                                                $badgeBg = match ($status) {
                                                    'berjalan' => 'bg-emerald-100 text-emerald-700',
                                                    'menunggu di-acc' => 'bg-amber-100 text-amber-700',
                                                    'sudah di-acc' => 'bg-[#CCF4DB] text-emerald-700',
                                                    \App\Models\Tugas::STATUS_TERLAMBAT => 'bg-red-100 text-red-700',
                                                    default => 'bg-[#C0E7FF] text-blue-700', // baru
                                                };
                                            @endphp
                                            <span
                                                class="px-2.5 py-1 rounded-full text-xs font-bold {{ $badgeBg }}">
                                                {{ $status }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-center whitespace-nowrap">
                                    {{ $task->deadline ? \Illuminate\Support\Carbon::parse($task->deadline)->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-xs">
                                    <a href="{{ url('/kadiv/detailTugas/' . $task->id_tugas) }}"
                                        class="text-[#0c88a9] hover:text-[#104958] transition font-medium">Detail</a>
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
        </div>

        <div>{{ $rows->links('vendor.pagination.custom') }}</div>
    </div>

    {{-- MODAL BUAT TUGAS BARU --}}
    <div id="modalTugasBaru" wire:ignore.self
        class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-4 bg-[rgba(86,94,116,0.4)] backdrop-blur-sm transition-opacity duration-300 ease-in-out">
        <div data-modal-panel
            class="bg-white rounded-xl w-full max-w-[460px] max-h-[90vh] flex flex-col shadow-[0_8px_16px_rgba(0,0,0,0.12)] scale-95 translate-y-2 transition-all duration-300 ease-in-out">

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
                                <div class="flex items-center gap-1.5 mt-1.5 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
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
                            <label for="projectFile" class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">FILE PROJECT (OPSIONAL)</label>
                            <input id="projectFile" type="file" wire:model="projectFile"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                                class="w-full text-sm text-[#283044] file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium">
                            <p class="text-xs text-slate-400 mt-1">PDF, Word, Excel, atau gambar; maks. 200 MB.</p>
                            @error('projectFile')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold tracking-wide text-[#565E74] mb-2">DAFTAR
                                TASK</label>

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
                                                        {{ $option->nama }} ({{ $option->divisi->nama_divisi ?? 'Tanpa divisi' }})
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
