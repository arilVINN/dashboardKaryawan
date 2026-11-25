<?php

namespace App\Http\Controllers;

use App\Models\DailyJournal;
use App\Models\KLabaRugi;
use App\Models\KPermintDana;
use App\Models\KPemasukan;
use App\Models\KPengeluaran;
use App\Models\Pesan;
use App\Models\SubmitTugas;
use App\Models\SuratIzin;
use App\Models\TowerDatabase;
use App\Models\Tugas;
use App\Models\User2;
use App\Support\FileStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves sensitive uploads (finance proofs, message attachments, task files,
 * leave letters, daily-journal evidence, tower documents) from private storage.
 *
 * Existing views keep building /storage/<path> URLs; the web server can no
 * longer serve these files because they live outside storage/app/public, so
 * the request reaches this controller, which re-checks the viewer's access to
 * the owning record before streaming the file.
 */
class SecureFileController extends Controller
{
    public function show(Request $request, string $path): StreamedResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User2, 403);

        $path = FileStorage::normalise($path);

        abort_if($path === null || ! FileStorage::isSensitive($path), 404);

        $prefix = explode('/', $path, 2)[0];
        $record = $this->recordFor($prefix, $path);

        abort_if(! ($record instanceof Model), 404);
        abort_unless($this->canView($user, $prefix, $record), 403);

        $disk = FileStorage::diskFor($path);

        abort_if($disk === null, 404);

        return Storage::disk($disk)->download($path, basename($path), [
            'Content-Type' => $this->mime($path),
            // Sensitive documents must not be cached by shared proxies or the
            // browser history of a shared workstation.
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
            // Neutralise any active content that may have slipped through.
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ]);
    }

    private function recordFor(string $prefix, string $path): ?Model
    {
        return match ($prefix) {
            'keuangan' => $this->financeRecord($path),
            'pesan-lampiran', 'lampiran_pesan' => Pesan::query()
                ->where('file_lampiran', $path)
                ->first(),
            'tugas_pendukung' => Tugas::query()
                ->where('file_pendukung', $path)
                ->first(),
            'submissions', 'revisi' => SubmitTugas::query()
                ->where('file_hasil', $path)
                ->orWhere('file_revisi', $path)
                ->first(),
            'surat_izin' => SuratIzin::query()
                ->where('file', $path)
                ->first(),
            'daily-journals' => DailyJournal::query()
                ->where('file_bukti', $path)
                ->first(),
            'tower_files' => TowerDatabase::query()
                ->where('file_path', $path)
                ->first(),
            default => null,
        };
    }

    private function financeRecord(string $path): ?Model
    {
        return match (true) {
            str_starts_with($path, 'keuangan/proposal/') => KPermintDana::query()
                ->where('lampiran_proposal', $path)
                ->first(),
            str_starts_with($path, 'keuangan/pemasukan/') => KPemasukan::query()
                ->where('bukti_dokumen', $path)
                ->first(),
            str_starts_with($path, 'keuangan/pengeluaran/'),
            str_starts_with($path, 'keuangan/pencairan/') => KPengeluaran::query()
                ->where('bukti_pencairan', $path)
                ->first(),
            str_starts_with($path, 'keuangan/laporan/') => KLabaRugi::query()
                ->where('bukti_laporan', $path)
                ->first(),
            default => null,
        };
    }

    private function canView(User2 $user, string $prefix, Model $record): bool
    {
        $role = strtolower(trim((string) $user->role?->nama_role));
        $isHrd = in_array($role, ['hrd', 'admin'], true);
        // Direktur/Direksi/superadmin share read access to all modules.
        $isExecutive = $user->isDirektur();

        return match ($prefix) {
            'keuangan' => $this->canViewFinance($user, $record),
            'pesan-lampiran', 'lampiran_pesan' => $this->canViewMessage($user, $record, $isExecutive, $isHrd),
            'tugas_pendukung' => $this->canViewTask($user, $record, $isExecutive, $isHrd),
            'submissions', 'revisi' => $this->canViewSubmission($user, $record, $isExecutive, $isHrd),
            'surat_izin' => $this->canViewSuratIzin($user, $record, $isExecutive, $isHrd),
            'daily-journals' => $this->canViewJournal($user, $record, $isExecutive, $isHrd),
            'tower_files' => $this->canViewTower($user, $record, $isExecutive, $isHrd),
            default => false,
        };
    }

    private function canViewFinance(User2 $user, Model $record): bool
    {
        // Kadiv Finance, Direktur/Direksi and superadmin may read finance files.
        if ($user->isKadivFinance()) {
            return true;
        }

        if ($record instanceof KPermintDana) {
            return $record->user_id_user === $user->id_user
                || ($this->isKadiv($user)
                    && $user->karyawan?->divisi_id_divisi === $record->divisi_id_divisi);
        }

        if ($record instanceof KLabaRugi) {
            return $this->isKadiv($user)
                && $user->karyawan?->divisi_id_divisi === $record->divisi_id_divisi;
        }

        return false;
    }

    private function canViewMessage(User2 $user, Model $record, bool $isExecutive, bool $isHrd): bool
    {
        if (! $record instanceof Pesan) {
            return false;
        }

        if ($isExecutive || $isHrd) {
            return true;
        }

        if ($record->pengirim_id_user === $user->id_user
            || $record->penerima_id_user === $user->id_user) {
            return true;
        }

        // A Kadiv may monitor messages tied to tasks in their own division.
        if ($this->isKadiv($user)) {
            $divisionId = $user->karyawan?->divisi_id_divisi;

            return $divisionId !== null
                && $record->tugas?->karyawan?->divisi_id_divisi === $divisionId;
        }

        return false;
    }

    private function canViewTask(User2 $user, Model $record, bool $isExecutive, bool $isHrd): bool
    {
        if (! $record instanceof Tugas) {
            return false;
        }

        if ($isExecutive || $isHrd) {
            return true;
        }

        if ($user->karyawan_id_karyawan !== null
            && $record->karyawan_id_karyawan === $user->karyawan_id_karyawan) {
            return true;
        }

        return $this->isKadiv($user)
            && $user->karyawan?->divisi_id_divisi !== null
            && $record->karyawan?->divisi_id_divisi === $user->karyawan?->divisi_id_divisi;
    }

    private function canViewSubmission(User2 $user, Model $record, bool $isExecutive, bool $isHrd): bool
    {
        if (! $record instanceof SubmitTugas) {
            return false;
        }

        $task = $record->tugas;

        return $task instanceof Tugas
            ? $this->canViewTask($user, $task, $isExecutive, $isHrd)
            : false;
    }

    private function canViewSuratIzin(User2 $user, Model $record, bool $isExecutive, bool $isHrd): bool
    {
        if (! $record instanceof SuratIzin) {
            return false;
        }

        if ($isExecutive || $isHrd) {
            return true;
        }

        if ($user->karyawan_id_karyawan !== null
            && $record->karyawan_id_karyawan === $user->karyawan_id_karyawan) {
            return true;
        }

        return $this->isKadiv($user)
            && $user->karyawan?->divisi_id_divisi !== null
            && $record->karyawan?->divisi_id_divisi === $user->karyawan?->divisi_id_divisi;
    }

    private function canViewJournal(User2 $user, Model $record, bool $isExecutive, bool $isHrd): bool
    {
        if (! $record instanceof DailyJournal) {
            return false;
        }

        if ($isExecutive || $isHrd) {
            return true;
        }

        if ($record->user_id_user === $user->id_user
            || $record->assigned_by_user_id === $user->id_user) {
            return true;
        }

        return $this->isKadiv($user)
            && $user->karyawan?->divisi_id_divisi !== null
            && $record->karyawan?->divisi_id_divisi === $user->karyawan?->divisi_id_divisi;
    }

    private function canViewTower(User2 $user, Model $record, bool $isExecutive, bool $isHrd): bool
    {
        if (! $record instanceof TowerDatabase) {
            return false;
        }

        if ($isExecutive) {
            return true;
        }

        if ($record->isGeneral()) {
            return true;
        }

        return $user->karyawan?->divisi_id_divisi !== null
            && $record->divisi_id_divisi === $user->karyawan?->divisi_id_divisi;
    }

    private function isKadiv(User2 $user): bool
    {
        return strtolower(trim((string) $user->role?->nama_role)) === 'kadiv';
    }

    private function mime(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip',
            default => 'application/octet-stream',
        };
    }
}
