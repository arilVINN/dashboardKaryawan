<?php

namespace App\Console\Commands;

use App\Actions\SendCalendarNoteReminder;
use App\Models\CalendarNote;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendCalendarNoteReminders extends Command
{
    protected $signature = 'catatan:remind-email
                            {--id= : Batasi ke satu ID catatan (berguna untuk pengujian)}
                            {--to= : Kirim ke alamat ini alih-alih email karyawan (tidak menandai terkirim)}
                            {--date= : Tanggal acuan pengingat, format YYYY-MM-DD (default hari ini)}
                            {--dry-run : Tampilkan catatan yang akan dikirimi tanpa mengirim email}
                            {--force : Kirim ulang walaupun pengingat sudah pernah dikirim}';

    protected $description = 'Kirim email pengingat untuk catatan kalender sesuai offset pengingatnya';

    public function handle(SendCalendarNoteReminder $sender): int
    {
        $overrideTo = $this->option('to');
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $noteId = $this->option('id');

        if ($overrideTo && ! filter_var($overrideTo, FILTER_VALIDATE_EMAIL)) {
            $this->error("Alamat email override tidak valid: {$overrideTo}");

            return self::FAILURE;
        }

        try {
            $date = $this->option('date')
                ? Carbon::parse($this->option('date'))->startOfDay()
                : now()->startOfDay();
        } catch (\Throwable) {
            $this->error('Tanggal acuan tidak valid. Gunakan format YYYY-MM-DD.');

            return self::FAILURE;
        }

        $query = CalendarNote::query()->with(['user.karyawan', 'user.role']);

        if ($noteId) {
            // Mode pengujian: kirim untuk catatan tertentu tanpa melihat
            // tanggal pengingat, tetapi tetap menghormati penanda terkirim.
            $query->whereKey($noteId);
        } else {
            $query->dueForEmailReminder($date);
        }

        if (! $force && ! $overrideTo) {
            $query->whereNull('reminder_email_sent_at');
        }

        $notes = $query->orderBy('tanggal')->get();

        if (! $noteId) {
            // Filter hari yang tepat di PHP agar kompatibel dengan PostgreSQL
            // (produksi) dan SQLite (testing).
            $notes = $notes
                ->filter(fn (CalendarNote $note) => $note->remindsOn($date))
                ->values();
        }

        if ($notes->isEmpty()) {
            $this->info('Tidak ada catatan yang perlu diingatkan.');

            return self::SUCCESS;
        }

        $rows = [];
        $sent = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($notes as $note) {
            $recipient = $overrideTo ?: $sender->recipientFor($note);
            $tanggal = $note->tanggal->locale('id')->translatedFormat('d M Y');
            $pengingat = $note->reminderDate()?->locale('id')->translatedFormat('d M Y') ?? '-';

            if (empty($recipient)) {
                $skipped++;
                $rows[] = [$note->id, $note->judul, $tanggal, $pengingat, '-', 'dilewati: tanpa email'];
                $this->warn("Catatan {$note->id} dilewati: karyawan tidak memiliki email.");

                continue;
            }

            if ($dryRun) {
                $rows[] = [$note->id, $note->judul, $tanggal, $pengingat, $recipient, 'dry-run'];

                continue;
            }

            try {
                if (! $sender->handle($note, $overrideTo)) {
                    $skipped++;
                    $rows[] = [$note->id, $note->judul, $tanggal, $pengingat, $recipient, 'dilewati: tanpa email'];

                    continue;
                }

                $sent++;
                $rows[] = [$note->id, $note->judul, $tanggal, $pengingat, $recipient, 'terkirim'];
            } catch (\Throwable $e) {
                $failed++;
                $rows[] = [$note->id, $note->judul, $tanggal, $pengingat, $recipient, 'gagal'];

                Log::error('Gagal mengirim email pengingat catatan kalender: '.$e->getMessage(), [
                    'id_catatan' => $note->id,
                    'penerima' => $recipient,
                ]);
            }
        }

        $this->table(
            ['ID Catatan', 'Judul', 'Tanggal', 'Pengingat', 'Penerima', 'Status'],
            $rows
        );

        $this->line("Terkirim: {$sent} | Dilewati: {$skipped} | Gagal: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
