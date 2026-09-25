<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tugas;
use App\Models\SubmitTugas;
use Illuminate\Support\Str;

class TugasController extends Controller
{
    // GET /staff/tugas
    public function index(Request $request)
    {
        $karyawanId = $request->user()->karyawan_id_karyawan;

        $tugas = Tugas::where('karyawan_id_karyawan', $karyawanId)->get();

        return response()->json([
            'message' => 'Daftar tugas Anda',
            'data' => $tugas
        ]);
    }

    // GET /staff/tugas/{id}
    public function show(Request $request, $id)
    {
        $karyawanId = $request->user()->karyawan_id_karyawan;

        $tugas = Tugas::with('submitTugas')->where('id_tugas', $id)
                      ->where('karyawan_id_karyawan', $karyawanId)
                      ->first();

        if (!$tugas) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail tugas',
            'data' => $tugas
        ]);
    }

    // POST /staff/tugas/{id}/submit
     public function submit(Request $request, $id)
    {
        $karyawanId = $request->user()->karyawan_id_karyawan;
        
        $tugas = Tugas::where('id_tugas', $id)
                      ->where('karyawan_id_karyawan', $karyawanId)
                      ->first();
        if (!$tugas) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }
        $request->validate([
            'file_hasil' => 'nullable|file',
            'link_submit' => 'nullable|string',
            'catatan_karyawan' => 'nullable|string',
            'progress' => 'required|integer|min:0|max:100'
        ]);
        $filePath = '-'; // Default string '-' karena database Anda melarang NULL
        if ($request->hasFile('file_hasil')) {
            $filePath = $request->file('file_hasil')->store('submissions', 'public');
        }
        // 1. Simpan ke tabel submit_tugas dengan antisipasi NOT NULL SQL
        SubmitTugas::create([
            'id_submit_tugas' => 'SB001', // Sesuaikan logika ID 5 karakter
            'tugas_id_tugas' => $tugas->id_tugas,
            'tugas_karyawan_id_karyawan' => $karyawanId,
            'file_hasil' => $filePath,
            'link_submit' => $request->link_submit ?? '-', // Cegah error NOT NULL
            'catatan_karyawan' => $request->catatan_karyawan ?? '-', // Cegah error NOT NULL
            'catatan_revisi' => '-', // Pasti '-' saat pertama submit
            'tanggal_submit' => now(),
            'status_review' => 'submitted',
        ]);
        $tugas->update([
            'progress' => $request->progress,
            'status' => $request->progress == 100 ? 'submitted' : $tugas->status,
        ]);
        return response()->json(['message' => 'Hasil tugas berhasil dikirim', 'data' => $tugas]);
    }
    
}