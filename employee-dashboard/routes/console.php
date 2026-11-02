<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Kirim pengingat email untuk tugas yang deadline-nya kurang dari 24 jam.
// Dijalankan tiap jam agar tugas yang baru masuk jendela 24 jam tetap terkirim
// tepat satu kali (lihat kolom tugas.deadline_reminder_sent_at).
Schedule::command('tugas:remind-deadline')->hourly()->withoutOverlapping();
