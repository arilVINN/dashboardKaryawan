<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\SubmitTugas;
use App\Models\Tugas;
use App\Models\User2;
use App\Queries\StaffTugasQuery;
<<<<<<< HEAD
<<<<<<< HEAD
use App\Support\FileMetadata;
use Illuminate\Support\Facades\Log;
=======
=======
use App\Support\FileMetadata;
use Illuminate\Http\Request;
>>>>>>> 836b8f440990db1100aa6dd63c29b9441194226d
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;
<<<<<<< HEAD
>>>>>>> 91a7df3ef54465de22a30f7c1b4fe653111049a2
=======
>>>>>>> 836b8f440990db1100aa6dd63c29b9441194226d

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

        // Submission sebelumnya berstatus "revisi" berarti ini pengumpulan ulang.
        $previousSubmission = SubmitTugas::query()
            ->where('tugas_id_tugas', $tugas->id_tugas)
            ->latest('created_at')
            ->first();
        $isRevisionSubmission = $previousSubmission?->status_review === 'revisi';

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
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 836b8f440990db1100aa6dd63c29b9441194226d
            $uploadedFile = $request->file('file_hasil');
            $filePath = $uploadedFile->store('submissions', 'public');

            if (! $filePath) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'file_hasil' => 'File gagal disimpan. Silakan coba lagi.',
                ]);
            }

            $fileOriginalName = $uploadedFile->getClientOriginalName();
        }

        try {
            DB::transaction(function () use ($tugas, $karyawanId, $filePath, $fileOriginalName, $validated, $progress): void {
                SubmitTugas::create([
                    'id_submit_tugas' => SubmitTugas::nextId(),
                    'tugas_id_tugas' => $tugas->id_tugas,
                    'tugas_karyawan_id_karyawan' => $karyawanId,
                    'file_hasil' => $filePath,
                    'file_hasil_name' => $fileOriginalName,
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

        try {
            $this->notifyKadivOnSubmission($tugas, $request->user(), $isRevisionSubmission);
        } catch (\Throwable $exception) {
            Log::error('Gagal membuat notifikasi pengumpulan tugas: '.$exception->getMessage(), [
                'id_tugas' => $tugas->id_tugas,
            ]);
        }

        return response()->json(['message' => 'Hasil tugas berhasil dikirim', 'data' => $tugas]);
    }

    private function notifyKadivOnSubmission(Tugas $tugas, User2 $staff, bool $isRevision): void
    {
        $tugas->loadMissing('karyawan');
        $divisiId = $tugas->karyawan?->divisi_id_divisi;

        if (! $divisiId) {
            return;
        }

        $kadivUsers = User2::query()
            ->whereHas('role', fn ($query) => $query->whereRaw('LOWER(nama_role) = ?', ['kadiv']))
            ->whereHas('karyawan', fn ($query) => $query->where('divisi_id_divisi', $divisiId))
            ->get();

        if ($kadivUsers->isEmpty()) {
            return;
        }

        $staffName = $staff->karyawan?->nama ?? $staff->username;
        $judul = $isRevision ? 'Pengumpulan Revisi' : 'Pengumpulan Tugas';
        $isiNotif = $isRevision
            ? $staffName.' mengirim revisi tugas "'.$tugas->judul_tugas.'".'
            : $staffName.' mengumpulkan tugas "'.$tugas->judul_tugas.'".';

        foreach ($kadivUsers as $kadiv) {
            Notifikasi::create([
                'id_notifikasi' => $this->generateNotifikasiId(),
                'judul_notifikasi' => $judul,
                'isi_notif' => $isiNotif,
                'tugas_id_tugas' => $tugas->id_tugas,
                'tanggal_notifikasi' => now()->toDateString(),
                'user_id_user' => $kadiv->id_user,
            ]);
        }
    }

    private function generateNotifikasiId(): string
    {
        do {
            $id = 'NTF'.strtoupper(substr(uniqid(), -8));
        } while (Notifikasi::where('id_notifikasi', $id)->exists());

        return $id;
<<<<<<< HEAD
=======
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
>>>>>>> 91a7df3ef54465de22a30f7c1b4fe653111049a2
    }
}
=======
    }
}
>>>>>>> 836b8f440990db1100aa6dd63c29b9441194226d
