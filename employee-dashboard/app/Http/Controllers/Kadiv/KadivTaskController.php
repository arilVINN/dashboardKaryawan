<?php

namespace App\Http\Controllers\Kadiv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tugas;
use App\Models\Karyawan;
use App\Models\Notifikasi;
use App\Queries\KadivTugasQuery;
use App\Support\FileMetadata;
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

        $tugas = KadivTugasQuery::forDivision($divisiId)
            ->apply($request->only(['sort', 'dir', 'status', 'staff', 'q']))
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

        $task->submitTugas->each(function ($submission) {
            $submission->setAttribute('file_hasil_size', FileMetadata::size($submission->file_hasil));
            $submission->setAttribute('file_revisi_size', FileMetadata::size($submission->file_revisi));
        });
        $task->setAttribute('file_pendukung_size', FileMetadata::size($task->file_pendukung));

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
            'deadline' => ['required', 'date_format:Y-m-d\TH:i,Y-m-d\TH:i:s,Y-m-d H:i:s'],
            // File atau link pendukung (opsional)
            'file_pendukung' => 'nullable|file|max:204800|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', // 200 MB max
            'link_pendukung' => 'nullable|url|max:2048',
        ]);

        // Pastikan karyawan penerima tugas berada di divisi yang sama
        $karyawanTujuan = Karyawan::where('id_karyawan', $request->karyawan_id_karyawan)
                                  ->where('divisi_id_divisi', $divisiId)
                                  ->first();

        if (!$karyawanTujuan) {
            return response()->json(['message' => 'Karyawan tidak valid atau beda divisi'], 403);
        }

        // Generate ID tugas unik (TG001, TG002, ...). Handles legacy
        // "TGS-*" identifiers and locks the table to avoid collisions.
        $newId = Tugas::nextId();

        $filePath = null;
        $fileName = null;
        if ($request->hasFile('file_pendukung')) {
            $uploadedFile = $request->file('file_pendukung');
            $filePath = $uploadedFile->store('tugas_pendukung', 'public');
            $fileName = $uploadedFile->getClientOriginalName();
        }

        $tugas = Tugas::create([
            'id_tugas' => $newId,
            'karyawan_id_karyawan' => $request->karyawan_id_karyawan,
            'judul_tugas' => $request->judul_tugas,
            'deskripsi' => $request->deskripsi,
            'file_pendukung' => $filePath,
            'file_pendukung_name' => $fileName,
            'link_pendukung' => $request->link_pendukung,
            'deadline' => \Illuminate\Support\Carbon::parse($request->deadline)->format('Y-m-d H:i:s'),
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

        if ($tugas->status === Tugas::STATUS_SUDAH_ACC) {
            return response()->json(['message' => 'Tugas ini sudah di-ACC dan tidak bisa diubah'], 403);
        }

        $request->validate([
            'judul_tugas' => 'sometimes|required|string|max:100',
            'deskripsi' => 'sometimes|required|string',
            'deadline' => ['sometimes', 'required', 'date_format:Y-m-d\TH:i,Y-m-d\TH:i:s,Y-m-d H:i:s'],
            'karyawan_id_karyawan' => 'sometimes|required|exists:karyawans,id_karyawan',
            'file_pendukung' => 'nullable|file|max:204800|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
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
        $fileName = $tugas->file_pendukung_name;
        if ($request->hasFile('file_pendukung')) {
            $uploadedFile = $request->file('file_pendukung');
            $filePath = $uploadedFile->store('tugas_pendukung', 'public');
            $fileName = $uploadedFile->getClientOriginalName();
        }

        $tugas->update([
            'karyawan_id_karyawan' => $request->karyawan_id_karyawan ?? $tugas->karyawan_id_karyawan,
            'judul_tugas' => $request->judul_tugas ?? $tugas->judul_tugas,
            'deskripsi' => $request->deskripsi ?? $tugas->deskripsi,
            'file_pendukung' => $filePath,
            'file_pendukung_name' => $fileName,
            'link_pendukung' => $request->link_pendukung ?? $tugas->link_pendukung,
            'deadline' => $request->filled('deadline')
                ? \Illuminate\Support\Carbon::parse($request->deadline)->format('Y-m-d H:i:s')
                : $tugas->deadline,
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
            'karyawan.user',
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
            'deadline' => ['required_if:status_review,revisi', 'nullable', 'date_format:Y-m-d\TH:i,Y-m-d\TH:i:s,Y-m-d H:i:s'],
            'file_revisi' => 'nullable|file|max:204800|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip',
        ], [
            'status_review.required' => 'Pilih tindakan review (acc atau revisi).',
            'status_review.in' => 'Tindakan review tidak valid.',
            'catatan_revisi.required_if' => 'Catatan revisi wajib diisi saat meminta revisi.',
            'catatan_revisi.max' => 'Catatan revisi maksimal 10.000 karakter.',
            'deadline.required_if' => 'Tenggat revisi wajib ditentukan.',
            'deadline.date_format' => 'Format tenggat revisi tidak valid.',
            'file_revisi.file' => 'Lampiran revisi tidak valid atau gagal diunggah.',
            'file_revisi.max' => 'Ukuran lampiran revisi maksimal 200 MB.',
            'file_revisi.mimes' => 'Format lampiran harus PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, atau ZIP.',
        ]);

        $latestSubmit = $tugas->submitTugas->first();
        if (!$latestSubmit) {
            return response()->json(['message' => 'Belum ada pengumpulan dari Staff.'], 400);
        }
        if ($latestSubmit->status_review !== 'submitted') {
            return response()->json(['message' => 'Pengumpulan ini sudah direview. Tunggu pengumpulan ulang dari Staff.'], 409);
        }

        $filePath = null;
        $fileName = null;
        if ($request->hasFile('file_revisi')) {
            $uploadedFile = $request->file('file_revisi');
            $filePath = $uploadedFile->store('revisi', 'public');
            $fileName = $uploadedFile->getClientOriginalName();
        }

        try {
            DB::transaction(function () use ($latestSubmit, $tugas, $validated, $filePath, $fileName): void {
                $latestSubmit->update([
                    'status_review' => $validated['status_review'],
                    'catatan_revisi' => $validated['status_review'] === 'revisi'
                        ? trim($validated['catatan_revisi'])
                        : $latestSubmit->catatan_revisi,
                    'file_revisi' => $filePath ?? $latestSubmit->file_revisi,
                    'file_revisi_name' => $filePath ? $fileName : $latestSubmit->file_revisi_name,
                    'deadline_revisi' => $validated['status_review'] === 'revisi'
                        ? \Illuminate\Support\Carbon::parse($validated['deadline'])->format('Y-m-d H:i:s')
                        : $latestSubmit->deadline_revisi,
                ]);

                $tugas->update([
                    'status' => $validated['status_review'] === 'acc'
                        ? Tugas::STATUS_SUDAH_ACC
                        : Tugas::STATUS_BERJALAN,
                    'deadline' => $validated['status_review'] === 'revisi'
                        ? \Illuminate\Support\Carbon::parse($validated['deadline'])->format('Y-m-d H:i:s')
                        : $tugas->deadline,
                ]);

                if ($validated['status_review'] === 'revisi' && $tugas->karyawan->user) {
                    Notifikasi::create([
                        'id_notifikasi' => $this->generateNotifikasiId(),
                        'judul_notifikasi' => 'Revisi Tugas',
                        'isi_notif' => 'Tugas "'.$tugas->judul_tugas.'" mendapat revisi baru: '
                            .trim($validated['catatan_revisi']),
                        'tanggal_notifikasi' => now()->toDateString(),
                        'user_id_user' => $tugas->karyawan->user->id_user,
                    ]);
                }
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

    private function generateNotifikasiId(): string
    {
        do {
            $id = 'NTF'.strtoupper(substr(uniqid(), -8));
        } while (Notifikasi::where('id_notifikasi', $id)->exists());

        return $id;
    }
}
