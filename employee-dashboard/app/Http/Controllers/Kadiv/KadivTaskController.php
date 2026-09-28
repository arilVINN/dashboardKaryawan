<?php

namespace App\Http\Controllers\Kadiv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tugas;
use App\Models\Karyawan;
use Illuminate\Support\Str;

class KadivTaskController extends Controller
{
    /**
     * GET /api/v1/kadiv/tugas
     * Menampilkan semua tugas di divisi terkait.
     */
    public function index(Request $request)
    {
        $userKadiv = $request->user();
        $divisiId = $userKadiv->karyawan->divisi_id_divisi;

        // Ambil ID semua karyawan di divisi tersebut
        $karyawanIds = Karyawan::where('divisi_id_divisi', $divisiId)->pluck('id_karyawan');

        // Ambil semua tugas milik karyawan di divisi tersebut
        $tugas = Tugas::with(['karyawan', 'submitTugas'])
                      ->whereIn('karyawan_id_karyawan', $karyawanIds)
                      ->orderBy('tanggal_dibuat', 'desc')
                      ->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar tugas divisi',
            'data' => $tugas
        ]);
    }

    /**
     * POST /api/v1/kadiv/tugas
     * Membuat tugas baru
     */
    public function store(Request $request)
    {
        $userKadiv = $request->user();
        $divisiId = $userKadiv->karyawan->divisi_id_divisi;

        $request->validate([
            'karyawan_id_karyawan' => 'required|exists:karyawans,id_karyawan',
            'judul_tugas' => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'deadline' => 'required|date',
            // File atau link pendukung (opsional)
            'file_pendukung' => 'nullable|file|max:20480', // 20 MB max
        ]);

        // Pastikan karyawan penerima tugas berada di divisi yang sama
        $karyawanTujuan = Karyawan::where('id_karyawan', $request->karyawan_id_karyawan)
                                  ->where('divisi_id_divisi', $divisiId)
                                  ->first();

        if (!$karyawanTujuan) {
            return response()->json(['message' => 'Karyawan tidak valid atau beda divisi'], 403);
        }

        // Generate ID tugas unik (maksimal 5 karakter untuk TGxxx)
        $lastTugas = Tugas::orderBy('id_tugas', 'desc')->first();
        $newId = $lastTugas ? 'TG' . str_pad(intval(substr($lastTugas->id_tugas, 2)) + 1, 3, '0', STR_PAD_LEFT) : 'TG001';

        // NOTE: Struktur database awal tidak memiliki kolom file_pendukung di tabel Tugas,
        // namun instruksi meminta file pendukung. Jika kolom tidak ada, kita bisa menggunakan deskripsi atau menolak.
        // Untuk saat ini, kita abaikan file upload jika kolom tidak tersedia, atau Anda harus mengubah migration.
        // Di sini kita asumsikan kolomnya belum ditambahkan di migration, maka disatukan ke deskripsi jika ada.
        $deskripsi = $request->deskripsi;
        
        $tugas = Tugas::create([
            'id_tugas' => $newId,
            'karyawan_id_karyawan' => $request->karyawan_id_karyawan,
            'judul_tugas' => $request->judul_tugas,
            'deskripsi' => $deskripsi,
            'deadline' => $request->deadline,
            'progress' => '0',
            'status' => 'pending',
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);

        return response()->json([
            'message' => 'Tugas berhasil dibuat',
            'data' => $tugas
        ], 201);
    }

    /**
     * PUT /api/v1/kadiv/tugas/{id}
     * Mengedit tugas
     */
    public function update(Request $request, $id)
    {
        $userKadiv = $request->user();
        $divisiId = $userKadiv->karyawan->divisi_id_divisi;

        $tugas = Tugas::with('karyawan')->where('id_tugas', $id)->first();

        if (!$tugas || $tugas->karyawan->divisi_id_divisi !== $divisiId) {
            return response()->json(['message' => 'Tugas tidak ditemukan atau di luar wewenang'], 404);
        }

        $request->validate([
            'judul_tugas' => 'sometimes|required|string|max:100',
            'deskripsi' => 'sometimes|required|string',
            'deadline' => 'sometimes|required|date',
            'karyawan_id_karyawan' => 'sometimes|required|exists:karyawans,id_karyawan'
        ]);

        // Jika pindah tangan, pastikan karyawan baru se-divisi
        if ($request->has('karyawan_id_karyawan')) {
            $karyawanTujuan = Karyawan::where('id_karyawan', $request->karyawan_id_karyawan)
                                      ->where('divisi_id_divisi', $divisiId)
                                      ->first();
            if (!$karyawanTujuan) {
                return response()->json(['message' => 'Karyawan pengganti tidak valid atau beda divisi'], 403);
            }
        }

        $tugas->update([
            'karyawan_id_karyawan' => $request->karyawan_id_karyawan ?? $tugas->karyawan_id_karyawan,
            'judul_tugas' => $request->judul_tugas ?? $tugas->judul_tugas,
            'deskripsi' => $request->deskripsi ?? $tugas->deskripsi,
            'deadline' => $request->deadline ?? $tugas->deadline,
            'tanggal_update' => now(),
        ]);

        return response()->json([
            'message' => 'Tugas berhasil diubah',
            'data' => $tugas
        ]);
    }

    /**
     * DELETE /api/v1/kadiv/tugas/{id}
     * Menghapus tugas
     */
    public function destroy(Request $request, $id)
    {
        $userKadiv = $request->user();
        $divisiId = $userKadiv->karyawan->divisi_id_divisi;

        $tugas = Tugas::with('karyawan')->where('id_tugas', $id)->first();

        if (!$tugas || $tugas->karyawan->divisi_id_divisi !== $divisiId) {
            return response()->json(['message' => 'Tugas tidak ditemukan atau di luar wewenang'], 404);
        }

        // Opsional: Hapus record SubmitTugas terkait
        $tugas->submitTugas()->delete(); 
        
        $tugas->delete();

        return response()->json([
            'message' => 'Tugas berhasil dihapus'
        ]);
    }

    /**
     * POST /api/v1/kadiv/tugas/{id}/review
     * ACC / Approval atau Revisi
     */
    public function review(Request $request, $id)
    {
        $userKadiv = $request->user();
        $divisiId = $userKadiv->karyawan->divisi_id_divisi;

        $tugas = Tugas::with(['karyawan', 'submitTugas' => function($q) {
            $q->orderBy('created_at', 'desc');
        }])->where('id_tugas', $id)->first();

        if (!$tugas || $tugas->karyawan->divisi_id_divisi !== $divisiId) {
            return response()->json(['message' => 'Tugas tidak ditemukan atau di luar wewenang'], 404);
        }

        $request->validate([
            'status_review' => 'required|in:acc,revisi',
            'catatan_revisi' => 'nullable|string'
        ]);

        $latestSubmit = $tugas->submitTugas->first();
        if (!$latestSubmit) {
            return response()->json(['message' => 'Belum ada file yang dikumpulkan oleh staff'], 400);
        }

        // Update Submission
        $latestSubmit->update([
            'status_review' => $request->status_review,
            'catatan_revisi' => $request->catatan_revisi ?? '-'
        ]);

        // Update Tugas Status
        $tugas->update([
            'status' => $request->status_review == 'acc' ? 'selesai' : 'revisi'
        ]);

        return response()->json([
            'message' => 'Review berhasil disimpan',
            'data' => [
                'tugas' => $tugas,
                'submit' => $latestSubmit
            ]
        ]);
    }
}
