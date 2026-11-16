<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test
                            {email : Recipient address}
                            {--subject= : Subject line for the test message}';

    protected $description = 'Send a test email to verify the configured mail transport';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Alamat email tidak valid: {$email}");

            return self::FAILURE;
        }

        $mailer = config('mail.default');
        $host = config("mail.mailers.{$mailer}.host");
        $port = config("mail.mailers.{$mailer}.port");

        $this->line('Mailer : '.$mailer);
        $this->line('Host   : '.($host ?: '-').($port ? ':'.$port : ''));
        $this->line('From   : '.config('mail.from.address'));
        $this->newLine();

        if (in_array($mailer, ['log', 'array'], true)) {
            $this->warn("Mailer [{$mailer}] tidak mengirim email sungguhan.");
            $this->warn('Gunakan MAIL_MAILER=smtp dengan kredensial SMTP untuk pengiriman nyata.');
        }

        $subject = (string) ($this->option('subject') ?: 'Tes Email Dashboard Karyawan');
        $sentAt = now()->toDateTimeString();

        try {
            Mail::raw(
                "Ini adalah email uji dari Dashboard Karyawan PT Silindo.\n\nDikirim pada: {$sentAt}\nMailer: {$mailer}",
                function ($message) use ($email, $subject): void {
                    $message->to($email)->subject($subject);
                }
            );
        } catch (\Throwable $e) {
            $this->error('Gagal mengirim email: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Email uji berhasil diproses untuk {$email} (mailer: {$mailer}).");

        if ($mailer === 'log') {
            $this->line('Periksa storage/logs/laravel.log untuk isi email.');
        }

        return self::SUCCESS;
    }
}
