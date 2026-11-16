<?php

namespace App\Mail;

use App\Models\Tugas;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TugasDeadlineMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Retry transient SMTP failures a couple of times before giving up.
     */
    public int $tries = 3;

    /**
     * Seconds to wait between attempts.
     *
     * @var array<int, int>
     */
    public array $backoff = [30, 120];

    public function __construct(
        public Tugas $tugas
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengingat Deadline: '.$this->tugas->judul_tugas,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tugas_deadline',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    public function failed(?\Throwable $exception = null): void
    {
        Log::error('Gagal mengirim email pengingat deadline tugas.', [
            'id_tugas' => $this->tugas->id_tugas ?? null,
            'penerima' => $this->tugas->karyawan?->email,
            'error' => $exception?->getMessage(),
        ]);
    }
}
