<?php

namespace App\Console\Commands;

use App\Mail\TugasDeadlineMail;
use App\Models\Tugas;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTaskDeadlineReminders extends Command
{
    protected $signature = 'tugas:remind-deadline
                            {--id= : Batasi ke satu ID tugas (berguna untuk pengujian)}
                            {--to= : Kirim ke alamat ini alih-alih email karyawan (tidak menandai terkirim)}
                            {--hours=24 : Jendela waktu ke depan dalam jam}
                            {--dry-run : Tampilkan tugas yang akan dikirimi tanpa mengirim email}
                            {--force : Kirim ulang walaupun pengingat sudah pernah dikirim}';

    protected $description = 'Kirim email pengingat untuk tugas yang mendekati deadline (default 24 jam)';

    public function handle(): int
    {
        $overrideTo = $this->option('to');
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $taskId = $this->option('id');
        $hours = max(1, (int) $this->option('hours'));

        if ($overrideTo && ! filter_var($overrideTo, FILTER_VALIDATE_EMAIL)) {
            $this->error("Alamat email override tidak valid: {$overrideTo}");

            return self::FAILURE;
        }

        $query = Tugas::query()->with('karyawan');

        if ($taskId) {
            // Mode pengujian: kirim untuk tugas tertentu tanpa melihat jendela
            // deadline, tetapi tetap menghormati penanda "sudah terkirim".
            $query->whereKey($taskId);
        } else {
            $now = now();

            $query->whereNotNull('deadline')
                ->where('deadline', '>', $now)
                ->where('deadline', '<=', $now->copy()->addHours($hours))
                ->whereNotIn('status', [
                    Tugas::STATUS_MENUNGGU_ACC,
                    Tugas::STATUS_SUDAH_ACC,
                ]);
        }

        if (! $force && ! $overrideTo) {
            $query->whereNull('deadline_reminder_sent_at');
        }

        $tasks = $query->orderBy('deadline')->get();

        if ($tasks->isEmpty()) {
            $this->info('Tidak ada tugas yang perlu diingatkan.');

            return self::SUCCESS;
        }

        $rows = [];
        $sent = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($tasks as $tugas) {
            $recipient = $overrideTo ?: $tugas->karyawan?->email;
            $deadline = $tugas->deadline
                ? Carbon::parse($tugas->deadline)->locale('id')->translatedFormat('d M Y, H:i')
                : '-';

            if (empty($recipient)) {
                $skipped++;
                $rows[] = [$tugas->id_tugas, $tugas->judul_tugas, $deadline, '-', 'dilewati: tanpa email'];
                $this->warn("Tugas {$tugas->id_tugas} dilewati: karyawan tidak memiliki email.");

                continue;
            }

            if ($dryRun) {
                $rows[] = [$tugas->id_tugas, $tugas->judul_tugas, $deadline, $recipient, 'dry-run'];

                continue;
            }

            try {
                $mailable = new TugasDeadlineMail($tugas);

                if (config('mail.queue_notifications', false)) {
                    Mail::to($recipient)->queue($mailable);
                } else {
                    Mail::to($recipient)->sendNow($mailable);
                }

                if (! $overrideTo) {
                    $tugas->forceFill(['deadline_reminder_sent_at' => now()])->saveQuietly();
                }

                $sent++;
                $rows[] = [$tugas->id_tugas, $tugas->judul_tugas, $deadline, $recipient, 'terkirim'];
            } catch (\Throwable $e) {
                $failed++;
                $rows[] = [$tugas->id_tugas, $tugas->judul_tugas, $deadline, $recipient, 'gagal'];

                Log::error('Gagal mengirim email pengingat deadline tugas: '.$e->getMessage(), [
                    'id_tugas' => $tugas->id_tugas,
                    'penerima' => $recipient,
                ]);
            }
        }

        $this->table(
            ['ID Tugas', 'Judul', 'Deadline', 'Penerima', 'Status'],
            $rows
        );

        $this->line("Terkirim: {$sent} | Dilewati: {$skipped} | Gagal: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
