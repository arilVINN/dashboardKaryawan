<?php

namespace Tests\Feature;

use App\Models\Notifikasi;
use Tests\TestCase;

class NotifikasiMessageTest extends TestCase
{
    public function test_display_message_strips_legacy_conversational_prefixes(): void
    {
        $cases = [
            'Anda mendapat tugas baru: Desain Logo' => 'Desain Logo',
            'Anda menerima pesan: Rapat Mingguan' => 'Rapat Mingguan',
            'Anda menerima surat: Permintaan Cuti' => 'Permintaan Cuti',
            'Ada pesan baru pada tugas: Desain Logo' => 'Desain Logo',
            'Pesan tanpa prefix' => 'Pesan tanpa prefix',
        ];

        foreach ($cases as $stored => $expected) {
            $notification = new Notifikasi(['isi_notif' => $stored]);

            $this->assertSame($expected, $notification->displayMessage(), $stored);
        }
    }
}
