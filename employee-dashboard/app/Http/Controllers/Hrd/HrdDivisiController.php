<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Divisi;
use App\Models\Tugas;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

class HrdDivisiController extends Controller
{
    public function detailPage($id)
    {
        $divisi = Divisi::with(['karyawans.user.role', 'karyawans.jabatanRef'])
            ->where('id_divisi', $id)
            ->firstOrFail();

        $anggotaDivisi = $divisi->karyawans->sortBy('nama')->values();
        $ketuaDivisi = $divisi->penanggungJawab();
        $daftarDivisi = Divisi::query()->ordered()->get();
        $bisaTambahAnggota = $divisi->canAcceptMember();

        return view('hrd.detailDivisi', compact(
            'divisi',
            'anggotaDivisi',
            'ketuaDivisi',
            'daftarDivisi',
            'bisaTambahAnggota',
        ));
    }

    /**
     * POST /api/hrd/divisi
     * Tambah divisi baru.
     */
    public function store(Request $request)
    {
        if ($request->filled('kode_divisi')) {
            $request->merge(['kode_divisi' => strtoupper(trim((string) $request->input('kode_divisi')))]);
        }

        $validated = $request->validate([
            'kode_divisi' => 'required|string|max:20|unique:divisis,kode_divisi',
            'nama_divisi' => 'required|string|max:100|unique:divisis,nama_divisi',
            'tipe' => ['nullable', Rule::in(array_keys(Divisi::TIPE_OPTIONS))],
            'parent_id' => 'nullable|string|max:20|exists:divisis,id_divisi',
            'kapasitas' => 'nullable|integer|min:0|max:255',
            'urutan' => 'nullable|integer|min:0',
            'status_aktif' => 'nullable|string|max:20',
        ]);

        // Cek apakah nama divisi sudah ada (cegah duplikat, respons 409 agar kompatibel)
        $exists = Divisi::where('nama_divisi', $validated['nama_divisi'])->first();
        if ($exists) {
            return response()->json([
                'message' => 'Divisi dengan nama "' . $validated['nama_divisi'] . '" sudah ada'
            ], 409); // 409 Conflict
        }

        $tipe = $validated['tipe'] ?? Divisi::TIPE_DIVISI;
        $parentId = $validated['parent_id'] ?? null;

        // A company has exactly one Direktur Utama and one Manajemen level.
        if (in_array($tipe, [Divisi::TIPE_DIREKTUR, Divisi::TIPE_MANAJEMEN], true)
            && Divisi::where('tipe', $tipe)->exists()) {
            return response()->json([
                'message' => Divisi::TIPE_OPTIONS[$tipe].' sudah ada.',
            ], 409);
        }

        if ($tipe === Divisi::TIPE_DIREKTUR) {
            $parentId = null;
        } elseif ($tipe === Divisi::TIPE_MANAJEMEN) {
            if ($parentId) {
                $parent = Divisi::where('id_divisi', $parentId)->first();
                if (! $parent || ! $parent->isDirekturUtama()) {
                    return response()->json([
                        'message' => 'Manajemen hanya dapat berada di bawah Direktur Utama.',
                    ], 422);
                }
            } else {
                $parentId = Divisi::where('tipe', Divisi::TIPE_DIREKTUR)->value('id_divisi');
            }
        } else {
            // Divisions live under Manajemen. Fall back to the Direktur Utama
            // when the Manajemen level has not been created yet.
            $parentId = $parentId
                ?: Divisi::where('tipe', Divisi::TIPE_MANAJEMEN)->value('id_divisi')
                ?: Divisi::where('tipe', Divisi::TIPE_DIREKTUR)->value('id_divisi');

            if ($parentId) {
                $parent = Divisi::where('id_divisi', $parentId)->first();
                if (! $parent || $parent->isDivisi()) {
                    return response()->json([
                        'message' => 'Divisi hanya dapat berada di bawah Manajemen atau Direktur Utama.',
                    ], 422);
                }
            }
        }

        $level = $this->levelForTipe($tipe);

        // Structural levels always have a fixed capacity, regardless of what
        // the form sends: one leader at the top and no direct members for
        // the management grouping level.
        $kapasitas = match ($tipe) {
            Divisi::TIPE_DIREKTUR => 1,
            Divisi::TIPE_MANAJEMEN => 0,
            default => $validated['kapasitas'] ?? null,
        };

        $urutan = $validated['urutan'] ?? ((int) Divisi::max('urutan') + 1);

        try {
            $divisi = Divisi::create([
                'id_divisi' => $this->nextDivisiId(),
                'kode_divisi' => $validated['kode_divisi'],
                'nama_divisi' => $validated['nama_divisi'],
                'parent_id' => $parentId,
                'tipe' => $tipe,
                'level' => $level,
                'kapasitas' => $kapasitas,
                'urutan' => $urutan,
                'status_aktif' => $validated['status_aktif'] ?? 'Aktif',
            ]);
        } catch (QueryException $e) {
            // Race condition: duplikat lolos validasi (kode/nama/ID)
            report($e);
            return response()->json([
                'message' => 'Divisi dengan kode atau nama tersebut sudah ada'
            ], 409);
        }

        return response()->json([
            'message' => 'Divisi berhasil ditambahkan',
            'data' => $divisi
        ], 201);
    }

    /**
     * GET /api/hrd/divisi
     * Daftar seluruh divisi perusahaan dalam urutan hierarki.
     */
    public function index()
    {
        $divisis = Divisi::withCount('karyawans')
            ->ordered()
            ->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar divisi',
            'data' => $divisis
        ]);
    }

    /**
     * PUT/PATCH /api/hrd/divisi/{id_divisi}
     * Mengubah data divisi.
     */
    public function update(Request $request, $id)
    {
        $divisi = Divisi::where('id_divisi', $id)->first();

        if (!$divisi) {
            return response()->json(['message' => 'Divisi tidak ditemukan'], 404);
        }

        if ($request->filled('kode_divisi')) {
            $request->merge(['kode_divisi' => strtoupper(trim((string) $request->input('kode_divisi')))]);
        }

        $validated = $request->validate([
            'kode_divisi' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('divisis', 'kode_divisi')->ignore($divisi->id_divisi, 'id_divisi')],
            'nama_divisi' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('divisis', 'nama_divisi')->ignore($divisi->id_divisi, 'id_divisi')],
            'tipe' => ['sometimes', 'required', Rule::in(array_keys(Divisi::TIPE_OPTIONS))],
            'parent_id' => ['sometimes', 'nullable', 'string', 'max:20', 'exists:divisis,id_divisi'],
            'kapasitas' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:255'],
            'urutan' => ['sometimes', 'required', 'integer', 'min:0'],
            'status_aktif' => ['sometimes', 'required', 'string', 'max:20'],
        ]);

        if (isset($validated['tipe'])) {
            $newTipe = $validated['tipe'];

            if (in_array($newTipe, [Divisi::TIPE_DIREKTUR, Divisi::TIPE_MANAJEMEN], true)
                && Divisi::where('tipe', $newTipe)->where('id_divisi', '!=', $divisi->id_divisi)->exists()) {
                return response()->json([
                    'message' => Divisi::TIPE_OPTIONS[$newTipe].' sudah ada.',
                ], 409);
            }

            $validated['level'] = $this->levelForTipe($newTipe);

            if ($newTipe === Divisi::TIPE_MANAJEMEN && $divisi->karyawans()->exists()) {
                return response()->json([
                    'message' => 'Pindahkan anggota terlebih dahulu sebelum menjadikan unit ini Manajemen.',
                ], 422);
            }

            if ($newTipe === Divisi::TIPE_DIREKTUR && $divisi->karyawans()->count() > 1) {
                return response()->json([
                    'message' => 'Direktur Utama hanya boleh memiliki satu orang.',
                ], 422);
            }

            if ($newTipe === Divisi::TIPE_DIVISI && $divisi->children()->exists()) {
                return response()->json([
                    'message' => 'Pindahkan sub-divisi terlebih dahulu sebelum menjadikan unit ini divisi.',
                ], 422);
            }

            if ($newTipe === Divisi::TIPE_DIREKTUR) {
                $validated['parent_id'] = null;
                $validated['kapasitas'] = 1;
            } elseif ($newTipe === Divisi::TIPE_MANAJEMEN) {
                $validated['kapasitas'] = 0;

                if (empty($validated['parent_id'])) {
                    $validated['parent_id'] = Divisi::where('tipe', Divisi::TIPE_DIREKTUR)->value('id_divisi');
                }
            }
        }

        $actualTipe = $validated['tipe'] ?? $divisi->tipe;

        if (array_key_exists('kapasitas', $validated) && $validated['kapasitas'] === null) {
            $validated['kapasitas'] = match ($actualTipe) {
                Divisi::TIPE_DIREKTUR => 1,
                Divisi::TIPE_MANAJEMEN => 0,
                default => null,
            };
        }

        $parentId = array_key_exists('parent_id', $validated)
            ? $validated['parent_id']
            : $divisi->parent_id;

        if (! empty($parentId)) {
            if ($parentId === $divisi->id_divisi) {
                return response()->json([
                    'message' => 'Divisi tidak dapat menjadi induk untuk dirinya sendiri.',
                ], 422);
            }

            $parent = Divisi::where('id_divisi', $parentId)->first();

            if (! $parent || $parent->isDivisi()) {
                return response()->json([
                    'message' => 'Induk divisi harus Manajemen atau Direktur Utama.',
                ], 422);
            }

            if ($this->wouldCreateCycle($divisi, $parentId)) {
                return response()->json([
                    'message' => 'Tidak dapat memindahkan divisi di bawah turunannya sendiri.',
                ], 422);
            }

            if ($actualTipe === Divisi::TIPE_MANAJEMEN && ! $parent->isDirekturUtama()) {
                return response()->json([
                    'message' => 'Manajemen hanya dapat berada di bawah Direktur Utama.',
                ], 422);
            }
        }

        $divisi->update($validated);

        return response()->json([
            'message' => 'Divisi berhasil diubah',
            'data' => $divisi->fresh(),
        ]);
    }

    /**
     * DELETE /api/hrd/divisi/{id_divisi}
     * Menghapus divisi yang belum memiliki karyawan maupun sub-divisi.
     */
    public function destroy($id)
    {
        $divisi = Divisi::where('id_divisi', $id)->first();

        if (!$divisi) {
            return response()->json(['message' => 'Divisi tidak ditemukan'], 404);
        }

        if ($divisi->children()->exists()) {
            return response()->json([
                'message' => 'Divisi tidak dapat dihapus karena masih memiliki sub-divisi',
            ], 409);
        }

        if ($divisi->karyawans()->exists()) {
            return response()->json([
                'message' => 'Divisi tidak dapat dihapus karena masih memiliki karyawan',
            ], 409);
        }

        try {
            $divisi->delete();
        } catch (QueryException $e) {
            report($e);
            return response()->json([
                'message' => 'Divisi tidak dapat dihapus karena masih memiliki data terkait',
            ], 409);
        }

        return response()->json(['message' => 'Divisi berhasil dihapus']);
    }

    /**
     * GET /api/hrd/divisi/{id_divisi}
     * Detail divisi (daftar staff, posisi, persentase tugas).
     */
    public function show($id)
    {
        $divisi = Divisi::with([
            'parent',
            'children',
            'karyawans' => function ($q) {
                $q->with(['user.role', 'tugas']);
            },
        ])->where('id_divisi', $id)->first();

        if (!$divisi) {
            return response()->json(['message' => 'Divisi tidak ditemukan'], 404);
        }

        // Hitung statistik tugas divisi
        $totalTugas = 0;
        $tugasSelesai = 0;
        $ketuaDivisi = null;

        foreach ($divisi->karyawans as $karyawan) {
            $totalTugas += $karyawan->tugas->count();
            $tugasSelesai += $karyawan->tugas->where('status', Tugas::STATUS_SUDAH_ACC)->count();

            if ($karyawan->isKadiv()) {
                $ketuaDivisi = $karyawan->nama;
            }
        }

        $persentase = $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100, 2) : 0;

        return response()->json([
            'message' => 'Berhasil mengambil detail divisi',
            'data' => [
                'divisi' => $divisi,
                'induk' => $divisi->parent,
                'sub_divisi' => $divisi->children,
                'tipe' => $divisi->tipe,
                'level' => $divisi->level,
                'kapasitas' => $divisi->kapasitas,
                'ketua_divisi' => $divisi->penanggungJawab()?->nama ?? $ketuaDivisi ?? 'Belum ditentukan',
                'total_staff' => $divisi->karyawans->count(),
                'total_tugas' => $totalTugas,
                'tugas_selesai' => $tugasSelesai,
                'persentase_selesai' => $persentase . '%',
            ]
        ]);
    }

    public function listPage()
    {
        // Rows, filters, sorting, and pagination now live in the
        // App\Livewire\Hrd\DivisiTable component.
        return view('hrd.daftarDivisi');
    }

    public function manajemenPage()
    {
        // Same component in manage mode (see the view).
        return view('hrd.manajemenDivisi');
    }

    private function levelForTipe(string $tipe): int
    {
        return match ($tipe) {
            Divisi::TIPE_DIREKTUR => 1,
            Divisi::TIPE_MANAJEMEN => 2,
            default => 3,
        };
    }

    private function nextDivisiId(): string
    {
        $max = 0;

        foreach (Divisi::pluck('id_divisi') as $id) {
            if (preg_match('/^DV(\d+)$/', (string) $id, $matches) === 1) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return 'DV'.str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Walk up from $parentId and make sure $divisi does not appear.
     */
    private function wouldCreateCycle(Divisi $divisi, string $parentId): bool
    {
        $current = Divisi::where('id_divisi', $parentId)->first();
        $visited = [];

        while ($current) {
            if ($current->id_divisi === $divisi->id_divisi) {
                return true;
            }

            if (in_array($current->id_divisi, $visited, true)) {
                break;
            }

            $visited[] = $current->id_divisi;
            $current = $current->parent;
        }

        return false;
    }
}
