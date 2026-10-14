<?php

namespace App\Presenters;

use App\Models\Pesan;
use App\Models\User2;
use Illuminate\Support\Facades\Storage;

class MessagePresenter
{
    public static function for(Pesan $message, User2 $user): array
    {
        $person = fn (?User2 $account) => $account ? [
            'id_user' => $account->id_user,
            'username' => $account->username,
            'nama' => $account->karyawan?->nama,
        ] : null;

        return [
            'id_pesan' => $message->id_pesan,
            'tipe' => $message->tipe ?? 'pesan',
            'arah' => $message->pengirim_id_user === $user->id_user ? 'keluar' : 'masuk',
            'judul_pesan' => $message->judul_pesan,
            'deskripsi' => $message->deskripsi,
            'tanggal_pesan' => $message->tanggal_pesan,
            'pengirim' => $person($message->pengirim),
            'penerima' => $person($message->penerima),
            'lampiran' => [
                'link' => $message->link_lampiran,
                'file' => $message->file_lampiran
                    ? Storage::disk('public')->url($message->file_lampiran)
                    : null,
            ],
            'tugas' => $message->tugas ? [
                'id_tugas' => $message->tugas->id_tugas,
                'judul_tugas' => $message->tugas->judul_tugas,
            ] : null,
            'balasan_dari_id_pesan' => $message->balasan_dari_id_pesan,
            'created_at' => $message->created_at?->toISOString(),
        ];
    }
}
