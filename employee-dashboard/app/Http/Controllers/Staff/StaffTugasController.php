<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tugas;
use App\Models\SubmitTugas;
use Illuminate\Support\Str;

class StaffTugasController extends Controller
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

        $tugas = Tugas::with(['submitTugas', 'pesans.pengirim'])->where('id_tugas', $id)
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

        // Cegah pengiriman ulang jika tugas sudah di-ACC
        if ($tugas->status === Tugas::STATUS_SUDAH_ACC) {
            return response()->json(['message' => 'Tugas ini sudah di-ACC dan tidak bisa dikirim ulang'], 403);
        }
        $validated = $request->validate([
            'file_hasil' => 'nullable|file|max:204800|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip', // max 200 MB (dalam kilobyte)
            'link_submit' => 'nullable|url|max:2048',
            'catatan_karyawan' => 'nullable|string',
            'progress' => 'sometimes|integer|min:0|max:100'
        ]);
        $progress = $validated['progress'] ?? $tugas->progress;
        $filePath = '-'; // Default string '-' karena database Anda melarang NULL
        if ($request->hasFile('file_hasil')) {
            $filePath = $request->file('file_hasil')->store('submissions', 'public');
        }
        // Generate dynamic ID SB001, SB002, etc. (Max 5 chars)
        $lastSubmit = SubmitTugas::orderBy('id_submit_tugas', 'desc')->first();
        $newId = $lastSubmit ? 'SB' . str_pad(intval(substr($lastSubmit->id_submit_tugas, 2)) + 1, 3, '0', STR_PAD_LEFT) : 'SB001';

        // 1. Simpan ke tabel submit_tugas dengan antisipasi NOT NULL SQL
        SubmitTugas::create([
            'id_submit_tugas' => $newId,
            'tugas_id_tugas' => $tugas->id_tugas,
            'tugas_karyawan_id_karyawan' => $karyawanId,
            'file_hasil' => $filePath,
            'link_submit' => $validated['link_submit'] ?? '-', // Cegah error NOT NULL
            'catatan_karyawan' => isset($validated['catatan_karyawan']) ? strip_tags($validated['catatan_karyawan']) : '-', // Cegah error NOT NULL
            'catatan_revisi' => '-', // Pasti '-' saat pertama submit
            'tanggal_submit' => now(),
            'status_review' => 'submitted',
        ]);
        $tugas->update([
            'progress' => $progress,
            'status' => $progress == 100
                ? Tugas::STATUS_MENUNGGU_ACC
                : $tugas->status,

        ]);
        return response()->json(['message' => 'Hasil tugas berhasil dikirim', 'data' => $tugas]);
    }
    
}