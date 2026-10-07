<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Tugas;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

class HrdDivisiController extends Controller
{
    public function detailPage($id)
    {
        $divisi = Divisi::with(['karyawans.user.role'])
            ->where('id_divisi', $id)
            ->firstOrFail();

        $anggotaDivisi = $divisi->karyawans;
        $ketuaDivisi = $anggotaDivisi->first(function ($karyawan) {
            return strtolower($karyawan->user->role->nama_role ?? '') === 'kadiv';
        });

        return view('hrd.detailDivisi', compact('divisi', 'anggotaDivisi', 'ketuaDivisi'));
    }

    /**
     * POST /api/hrd/divisi
     * Tambah divisi baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_divisi' => 'required|string|max:20|unique:divisis,kode_divisi',
            'nama_divisi' => 'required|string|max:100|unique:divisis,nama_divisi',
            'status_aktif' => 'nullable|string|max:20',
        ]);

        // Cek apakah nama divisi sudah ada (cegah duplikat, respons 409 agar kompatibel)
        $exists = Divisi::where('nama_divisi', $request->nama_divisi)->first();
        if ($exists) {
            return response()->json([
                'message' => 'Divisi dengan nama "' . $request->nama_divisi . '" sudah ada'
            ], 409); // 409 Conflict
        }

        // Generate ID divisi dinamis (DVxxx, max 5 karakter)
        $lastDivisi = Divisi::orderBy('id_divisi', 'desc')->first();
        $newId = $lastDivisi
            ? 'DV' . str_pad(intval(substr($lastDivisi->id_divisi, 2)) + 1, 3, '0', STR_PAD_LEFT)
            : 'DV001';

        try {
            $divisi = Divisi::create([
                'id_divisi' => $newId,
                'kode_divisi' => $request->kode_divisi,
                'nama_divisi' => $request->nama_divisi,
                'status_aktif' => $request->status_aktif ?? 'Aktif',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
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
     * Daftar seluruh divisi perusahaan.
     */
    public function index()
    {
        $divisis = Divisi::withCount('karyawans')->get();

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

        $validated = $request->validate([
            'kode_divisi' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('divisis', 'kode_divisi')->ignore($divisi->id_divisi, 'id_divisi')],
            'nama_divisi' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('divisis', 'nama_divisi')->ignore($divisi->id_divisi, 'id_divisi')],
            'status_aktif' => 'sometimes|required|string|max:20',
        ]);

        $divisi->update($validated);

        return response()->json([
            'message' => 'Divisi berhasil diubah',
            'data' => $divisi->fresh(),
        ]);
    }

    /**
     * DELETE /api/hrd/divisi/{id_divisi}
     * Menghapus divisi yang belum memiliki karyawan.
     */
    public function destroy($id)
    {
        $divisi = Divisi::where('id_divisi', $id)->first();

        if (!$divisi) {
            return response()->json(['message' => 'Divisi tidak ditemukan'], 404);
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
                'message' => 'Divisi tidak dapat dihapus karena masih memiliki karyawan',
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
        $divisi = Divisi::with(['karyawans' => function ($q) {
            $q->with(['user.role', 'tugas']);
        }])->where('id_divisi', $id)->first();

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

            // Identifikasi Ketua Divisi (role kadiv)
            if ($karyawan->user && $karyawan->user->role && $karyawan->user->role->nama_role === 'kadiv') {
                $ketuaDivisi = $karyawan->nama;
            }
        }

        $persentase = $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100, 2) : 0;

        return response()->json([
            'message' => 'Berhasil mengambil detail divisi',
            'data' => [
                'divisi' => $divisi,
                'ketua_divisi' => $ketuaDivisi ?? 'Belum ditentukan',
                'total_staff' => $divisi->karyawans->count(),
                'total_tugas' => $totalTugas,
                'tugas_selesai' => $tugasSelesai,
                'persentase_selesai' => $persentase . '%',
            ]
        ]);
    }

    /**
     * Query daftar divisi dengan sortir + filter dari query string.
     * Dipakai bersama oleh daftarDivisi dan manajemenDivisi.
     */
    private function divisiListQuery(Request $request): Builder
    {
        $sort = $request->input('sort');
        $dir = $request->input('dir') === 'desc' ? 'desc' : 'asc';

        $query = Divisi::withCount('karyawans');

        if ($request->filled('q')) {
            // LOWER(...) so the search is case-insensitive on Postgres too.
            $kata = mb_strtolower((string) $request->input('q'));
            $query->where(function ($q) use ($kata): void {
                $q->whereRaw('LOWER(kode_divisi) LIKE ?', ['%' . $kata . '%'])
                    ->orWhereRaw('LOWER(nama_divisi) LIKE ?', ['%' . $kata . '%']);
            });
        }

        if (in_array($request->input('status'), ['aktif', 'nonaktif'], true)) {
            $query->whereRaw('LOWER(status_aktif) = ?', [$request->input('status')]);
        }

        switch ($sort) {
            case 'kode':
                $query->orderBy('kode_divisi', $dir)->orderBy('id_divisi');
                break;
            case 'nama':
                $query->orderBy('nama_divisi', $dir)->orderBy('id_divisi');
                break;
            case 'staff':
                $query->orderBy('karyawans_count', $dir)->orderBy('id_divisi');
                break;
            default:
                $query->orderBy('id_divisi', 'desc');
        }

        return $query;
    }

    public function listPage(Request $request)
    {
        $divisis = $this->divisiListQuery($request)->paginate(5)->withQueryString();

        return view('hrd.daftarDivisi', compact('divisis'));
    }

    public function manajemenPage(Request $request)
    {
        $divisis = $this->divisiListQuery($request)->get();

        return view('hrd.manajemenDivisi', compact('divisis'));
    }
}
