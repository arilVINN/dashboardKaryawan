<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesan extends Model
{
    protected $table = 'pesans';

    protected $primaryKey = 'id_pesan';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_pesan',
        'judul_pesan',
        'deskripsi',
        'tanggal_pesan',
        'tugas_id_tugas',
        'tugas_karyawan_id_karyawan',
        'pengirim_id_user',
    ];

    public function tugas()
    {
        return $this->belongsTo(
            Tugas::class,
            'tugas_id_tugas',
            'id_tugas'
        );
    }

    public function pengirim()
    {
        return $this->belongsTo(
            User2::class,
            'pengirim_id_user',
            'id_user'
        );
    }
}
