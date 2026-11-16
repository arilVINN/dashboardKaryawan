<?php

namespace App\Actions;

use App\Mail\CalendarNoteReminderMail;
use App\Models\CalendarNote;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCalendarNoteReminder
{
    /**
     * Email penerima untuk sebuah catatan, atau null bila tidak tersedia.
     */
    public function recipientFor(CalendarNote $note): ?string
    {
        $email = $note->user?->karyawan?->email;

        return filled($email) ? $email : null;
    }

    /**
     * Kirim email pengingat untuk satu catatan.
     *
     * @return bool True bila email berhasil dikirim/diantrikan.
     */
    public function handle(CalendarNote $note, ?string $overrideTo = null): bool
    {
        $recipient = $overrideTo ?: $this->recipientFor($note);

        if (! $recipient) {
            Log::warning('Email pengingat catatan dilewati: karyawan tidak memiliki email.', [
                'id_catatan' => $note->id,
                'id_user' => $note->user_id_user,
            ]);

            return false;
        }

        $mailable = new CalendarNoteReminderMail($note);

        if (config('mail.queue_notifications', false)) {
            Mail::to($recipient)->queue($mailable);
        } else {
            Mail::to($recipient)->sendNow($mailable);
        }

        // Override penerima dipakai untuk pengujian, jadi jangan tandai
        // catatan sebagai sudah terkirim.
        if (! $overrideTo) {
            $note->forceFill(['reminder_email_sent_at' => now()])->saveQuietly();
        }

        return true;
    }
}
