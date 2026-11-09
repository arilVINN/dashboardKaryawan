<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tugas;
use App\Models\SubmitTugas;
use App\Queries\StaffTugasQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StaffTugasController extends Controller
{
    // GET /staff/tugas
    public function index(Request $request)
    {
        $tugas = StaffTugasQuery::forStaff($request->user())
            ->apply($request->only(['sort', 'dir', 'status', 'q']))
            ->get();

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

        // Jika status masih 'baru', ubah menjadi 'berjalan' ketika staff melihat tugas
        if ($tugas->status === Tugas::STATUS_BARU) {
            $tugas->update([
                'status' => Tugas::STATUS_BERJALAN,
                'tanggal_update' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Detail tugas',
            'data' => $tugas
        ]);
    }

    // PATCH /staff/tugas/{id}/toggle-checklist
    public function toggleChecklist(Request $request, $id)
    {
        $validated = $request->validate([
            'selesai' => 'required|boolean',
        ]);

        $tugas = Tugas::where('id_tugas', $id)
            ->where('karyawan_id_karyawan', $request->user()->karyawan_id_karyawan)
            ->first();

        if (! $tugas) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }

        $tugas->update(['checklist_selesai' => $validated['selesai']]);

        return response()->json([
            'message' => 'Checklist tugas berhasil diperbarui',
            'data' => $tugas,
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
            'file_hasil' => 'required_without:catatan_karyawan|file|max:204800|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip', // max 200 MB (dalam kilobyte)
            'link_submit' => 'nullable|url|max:2048',
            'catatan_karyawan' => 'required_without:file_hasil|string',
            'progress' => 'sometimes|integer|min:0|max:100'
        ]);
        $progress = $validated['progress'] ?? $tugas->progress;
        $filePath = '-'; // Default string '-' karena database Anda melarang NULL
        if ($request->hasFile('file_hasil')) {
            $filePath = $request->file('file_hasil')->store('submissions', 'public');

            if (! $filePath) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'file_hasil' => 'File gagal disimpan. Silakan coba lagi.',
                ]);
            }
        }

        try {
            DB::transaction(function () use ($tugas, $karyawanId, $filePath, $validated, $progress): void {
                SubmitTugas::create([
                    'id_submit_tugas' => $this->nextSubmitId(),
                    'tugas_id_tugas' => $tugas->id_tugas,
                    'tugas_karyawan_id_karyawan' => $karyawanId,
                    'file_hasil' => $filePath,
                    'link_submit' => $validated['link_submit'] ?? '-',
                    'catatan_karyawan' => isset($validated['catatan_karyawan'])
                        ? strip_tags($validated['catatan_karyawan'])
                        : '-',
                    'catatan_revisi' => '-',
                    'tanggal_submit' => now(),
                    'status_review' => 'submitted',
                ]);

                $tugas->update([
                    'progress' => $progress,
                    'status' => $progress == 100
                        ? Tugas::STATUS_MENUNGGU_ACC
                        : $tugas->status,
                ]);
            });
        } catch (Throwable $exception) {
            if ($filePath !== '-') {
                Storage::disk('public')->delete($filePath);
            }

            throw $exception;
        }

        return response()->json(['message' => 'Hasil tugas berhasil dikirim', 'data' => $tugas]);
    }

    private function nextSubmitId(): string
    {
        $max = 0;

        foreach (SubmitTugas::query()->lockForUpdate()->pluck('id_submit_tugas') as $id) {
            if (preg_match('/^SB(\d+)$/', (string) $id, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return 'SB'.str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }
}