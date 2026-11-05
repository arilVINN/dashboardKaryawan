<?php

namespace App\Actions;

use App\Events\PesanDikirim;
use App\Models\Pesan;
use App\Models\User2;

class SendStaffMessage
{
    public function handle(User2 $sender, User2 $recipient, string $judul, string $isi): Pesan
    {
        $pesan = Pesan::create([
            'id_pesan' => $this->generatePesanId(),
            'judul_pesan' => strip_tags(trim($judul)),
            'deskripsi' => trim($isi),
            'tipe' => 'pesan',
            'tanggal_pesan' => now()->toDateString(),
            'pengirim_id_user' => $sender->id_user,
            'penerima_id_user' => $recipient->id_user,
        ]);

        PesanDikirim::dispatch($pesan);

        return $pesan;
    }

    private function generatePesanId(): string
    {
        do {
            $id = 'PSN'.strtoupper(substr(uniqid(), -8));
        } while (Pesan::where('id_pesan', $id)->exists());

        return $id;
    }
}
