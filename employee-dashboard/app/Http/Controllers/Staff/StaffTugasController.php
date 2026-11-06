<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tugas;
use App\Models\SubmitTugas;
use App\Queries\StaffTugasQuery;
use App\Support\FileMetadata;

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

        $tugas->submitTugas->each(function ($submission) {
            $submission->setAttribute('file_hasil_size', FileMetadata::size($submission->file_hasil));
            $submission->setAttribute('file_revisi_size', FileMetadata::size($submission->file_revisi));
        });
        $tugas->setAttribute('file_pendukung_size', FileMetadata::size($tugas->file_pendukung));

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
            'progress' => 'sometimes|integer|min:0|max:100',
        ], [
            'file_hasil.required_without' => 'Lampirkan file hasil atau isi catatan pengumpulan.',
            'file_hasil.file' => 'File hasil tidak valid atau gagal diunggah.',
            'file_hasil.max' => 'Ukuran file hasil maksimal 200 MB.',
            'file_hasil.mimes' => 'Format file hasil harus PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, atau ZIP.',
            'link_submit.url' => 'Link pengumpulan harus berupa URL yang valid.',
            'link_submit.max' => 'Link pengumpulan maksimal 2048 karakter.',
            'catatan_karyawan.required_without' => 'Isi catatan atau lampirkan file hasil.',
            'progress.integer' => 'Nilai progress harus berupa angka.',
            'progress.min' => 'Nilai progress minimal 0.',
            'progress.max' => 'Nilai progress maksimal 100.',
        ]);

        $progress = $validated['progress'] ?? $tugas->progress;
        $filePath = '-'; // Default string '-' karena database Anda melarang NULL
        $fileOriginalName = null;
        if ($request->hasFile('file_hasil')) {
            $uploadedFile = $request->file('file_hasil');
            $filePath = $uploadedFile->store('submissions', 'public');

            if (! $filePath) {
                return response()->json([
                    'message' => 'File hasil gagal disimpan. Silakan coba lagi.',
                    'errors' => ['file_hasil' => ['File hasil gagal disimpan. Silakan coba lagi.']],
                ], 422);
            }

            $fileOriginalName = $uploadedFile->getClientOriginalName();
        }

        $attributes = [
            'tugas_id_tugas' => $tugas->id_tugas,
            'tugas_karyawan_id_karyawan' => $karyawanId,
            'file_hasil' => $filePath,
            'file_hasil_name' => $fileOriginalName,
            'link_submit' => $validated['link_submit'] ?? '-', // Cegah error NOT NULL
            'catatan_karyawan' => isset($validated['catatan_karyawan']) ? strip_tags($validated['catatan_karyawan']) : '-', // Cegah error NOT NULL
            'catatan_revisi' => '-', // Pasti '-' saat pertama submit
            'tanggal_submit' => now(),
            'status_review' => 'submitted',
        ];

        // Retry once or twice if two submissions race for the same ID.
        $attempts = 0;
        while (true) {
            try {
                SubmitTugas::create(['id_submit_tugas' => SubmitTugas::nextId()] + $attributes);
                break;
            } catch (\Illuminate\Database\QueryException $exception) {
                $isDuplicate = in_array((string) $exception->getCode(), ['23505', '23000'], true);
                if (! $isDuplicate || ++$attempts >= 3) {
                    throw $exception;
                }
            }
        }
        $tugas->update([
            'progress' => $progress,
            'status' => $progress == 100
                ? Tugas::STATUS_MENUNGGU_ACC
                : $tugas->status,

        ]);
        return response()->json(['message' => 'Hasil tugas berhasil dikirim', 'data' => $tugas]);
    }
    
}