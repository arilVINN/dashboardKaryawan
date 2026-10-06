<?php

namespace App\Http\Controllers\Kadiv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tugas;
use App\Models\Karyawan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KadivTaskController extends Controller
{
    /**
    * GET /api/kadiv/tugas
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
     * GET /api/kadiv/tugas/{id}
     * Menampilkan tugas divisi beserta pengumpulan terbarunya.
     */
    public function show(Request $request, string $id)
    {
        $divisionId = $request->user()->karyawan->divisi_id_divisi;
        $task = Tugas::with([
            'karyawan',
            'submitTugas' => fn ($query) => $query->latest('created_at'),
        ])->whereKey($id)
            ->whereHas('karyawan', fn ($query) => $query->where('divisi_id_divisi', $divisionId))
            ->first();

        if (! $task) {
            return response()->json(['message' => 'Tugas tidak ditemukan atau di luar divisi Anda.'], 404);
        }

        return response()->json([
            'data' => $task,
        ]);
    }

    /**
    * POST /api/kadiv/tugas
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
            'file_pendukung' => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', // 20 MB max
            'link_pendukung' => 'nullable|url|max:2048',
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

        $filePath = null;
        if ($request->hasFile('file_pendukung')) {
            $filePath = $request->file('file_pendukung')->store('tugas_pendukung', 'public');
        }

        $tugas = Tugas::create([
            'id_tugas' => $newId,
            'karyawan_id_karyawan' => $request->karyawan_id_karyawan,
            'judul_tugas' => $request->judul_tugas,
            'deskripsi' => $request->deskripsi,
            'file_pendukung' => $filePath,
            'link_pendukung' => $request->link_pendukung,
            'deadline' => $request->deadline,
            'progress' => '0',
            'status' => Tugas::STATUS_BARU,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);

        return response()->json([
            'message' => 'Tugas berhasil dibuat',
            'data' => $tugas
        ], 201);
    }

    /**
    * PUT /api/kadiv/tugas/{id}
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
            'karyawan_id_karyawan' => 'sometimes|required|exists:karyawans,id_karyawan',
            'file_pendukung' => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'link_pendukung' => 'nullable|url|max:2048'
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

        $filePath = $tugas->file_pendukung;
        if ($request->hasFile('file_pendukung')) {
            $filePath = $request->file('file_pendukung')->store('tugas_pendukung', 'public');
        }

        $tugas->update([
            'karyawan_id_karyawan' => $request->karyawan_id_karyawan ?? $tugas->karyawan_id_karyawan,
            'judul_tugas' => $request->judul_tugas ?? $tugas->judul_tugas,
            'deskripsi' => $request->deskripsi ?? $tugas->deskripsi,
            'file_pendukung' => $filePath,
            'link_pendukung' => $request->link_pendukung ?? $tugas->link_pendukung,
            'deadline' => $request->deadline ?? $tugas->deadline,
            'tanggal_update' => now(),
        ]);

        return response()->json([
            'message' => 'Tugas berhasil diubah',
            'data' => $tugas
        ]);
    }

    /**
    * DELETE /api/kadiv/tugas/{id}
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
    * POST /api/kadiv/tugas/{id}/review
     * ACC / Approval atau Revisi
     */
    public function review(Request $request, $id)
    {
        $userKadiv = $request->user();
        $divisiId = $userKadiv->karyawan->divisi_id_divisi;

        $tugas = Tugas::with([
            'karyawan',
            'submitTugas' => fn ($query) => $query->orderBy('created_at', 'desc'),
        ])->where('id_tugas', $id)->first();

        if (!$tugas || $tugas->karyawan->divisi_id_divisi !== $divisiId) {
            return response()->json(['message' => 'Tugas tidak ditemukan atau di luar wewenang'], 404);
        }

        if ($tugas->status === Tugas::STATUS_SUDAH_ACC) {
            return response()->json(['message' => 'Tugas ini sudah di-ACC sebelumnya dan tidak dapat direview lagi'], 403);
        }

        $validated = $request->validate([
            'status_review' => 'required|in:acc,revisi',
            'catatan_revisi' => 'required_if:status_review,revisi|nullable|string|max:10000',
            'deadline' => 'required_if:status_review,revisi|nullable|date',
            'file_revisi' => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip',
        ]);

        $latestSubmit = $tugas->submitTugas->first();
        if (!$latestSubmit) {
            return response()->json(['message' => 'Belum ada pengumpulan dari Staff.'], 400);
        }
        if ($latestSubmit->status_review !== 'submitted') {
            return response()->json(['message' => 'Pengumpulan ini sudah direview. Tunggu pengumpulan ulang dari Staff.'], 409);
        }

        $filePath = null;
        if ($request->hasFile('file_revisi')) {
            $filePath = $request->file('file_revisi')->store('revisi', 'public');
        }

        try {
            DB::transaction(function () use ($latestSubmit, $tugas, $validated, $filePath): void {
                $latestSubmit->update([
                    'status_review' => $validated['status_review'],
                    'catatan_revisi' => $validated['status_review'] === 'revisi'
                        ? trim($validated['catatan_revisi'])
                        : $latestSubmit->catatan_revisi,
                    'file_revisi' => $filePath ?? $latestSubmit->file_revisi,
                    'deadline_revisi' => $validated['status_review'] === 'revisi'
                        ? $validated['deadline']
                        : $latestSubmit->deadline_revisi,
                ]);

                $tugas->update([
                    'status' => $validated['status_review'] === 'acc'
                        ? Tugas::STATUS_SUDAH_ACC
                        : Tugas::STATUS_BERJALAN,
                    'deadline' => $validated['status_review'] === 'revisi'
                        ? \Illuminate\Support\Carbon::parse($validated['deadline'])->toDateString()
                        : $tugas->deadline,
                ]);
            });
        } catch (\Throwable $exception) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }

            throw $exception;
        }

        return response()->json([
            'message' => 'Review berhasil disimpan',
            'data' => [
                'tugas' => $tugas,
                'submit' => $latestSubmit
            ]
        ]);
    }
}
