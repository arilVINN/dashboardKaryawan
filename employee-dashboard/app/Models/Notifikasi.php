<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasis';

    protected $primaryKey = 'id_notifikasi';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_notifikasi',
        'judul_notifikasi',
        'isi_notif',
        'tugas_id_tugas',
        'pesan_id_pesan',
        'tanggal_notifikasi',
        'user_id_user',
    ];

    public function user()
    {
        return $this->belongsTo(
            User2::class,
            'user_id_user',
            'id_user'
        );
    }

    public function tugas()
    {
        return $this->belongsTo(
            Tugas::class,
            'tugas_id_tugas',
            'id_tugas'
        );
    }

    public function pesan()
    {
        return $this->belongsTo(
            Pesan::class,
            'pesan_id_pesan',
            'id_pesan'
        );
    }

    /**
     * Notification body without legacy conversational prefixes.
     */
    public function displayMessage(): string
    {
        $message = preg_replace([
            '/^Anda mendapat tugas baru:\s*/i',
            '/^Anda menerima (?:pesan|surat):\s*/i',
            '/^Ada pesan baru pada tugas:\s*/i',
        ], '', (string) $this->isi_notif);

        return trim((string) $message);
    }
}
