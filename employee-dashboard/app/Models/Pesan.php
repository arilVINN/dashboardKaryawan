<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'tipe',
        'link_lampiran',
        'file_lampiran',
        'tanggal_pesan',
        'tugas_id_tugas',
        'tugas_karyawan_id_karyawan',
        'pengirim_id_user',
        'penerima_id_user',
        'balasan_dari_id_pesan',
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

    public function penerima()
    {
        return $this->belongsTo(
            User2::class,
            'penerima_id_user',
            'id_user'
        );
    }

    public function balasanDari(): BelongsTo
    {
        return $this->belongsTo(self::class, 'balasan_dari_id_pesan', 'id_pesan');
    }

    public function balasan(): HasMany
    {
        return $this->hasMany(self::class, 'balasan_dari_id_pesan', 'id_pesan');
    }
}
