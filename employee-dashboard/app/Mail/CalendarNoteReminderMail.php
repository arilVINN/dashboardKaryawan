<?php

namespace App\Mail;

use App\Models\CalendarNote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalendarNoteReminderMail extends Mailable implements ShouldQueue
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
        public CalendarNote $note
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengingat Catatan: '.$this->note->judul,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.calendar_note_reminder',
            with: [
                'calendarUrl' => $this->calendarUrl(),
            ],
        );
    }

    /**
     * URL kalender sesuai role pemilik catatan.
     */
    public function calendarUrl(): string
    {
        $role = strtolower((string) $this->note->user?->role?->nama_role);

        return match (true) {
            str_contains($role, 'kadiv') => url('/kadiv/kalender'),
            str_contains($role, 'hrd') => url('/hrd/kalender'),
            default => url('/kalender'),
        };
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
        Log::error('Gagal mengirim email pengingat catatan kalender.', [
            'id_catatan' => $this->note->id ?? null,
            'penerima' => $this->note->user?->karyawan?->email,
            'error' => $exception?->getMessage(),
        ]);
    }
}
