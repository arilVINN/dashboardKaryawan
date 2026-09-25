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
        'tanggal_notifikasi',
        'user_id_user',
    ];

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id_user',
            'id_user'
        );
    }
}
