<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Tugas;

class HrdDivisiController extends Controller
{
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
}
