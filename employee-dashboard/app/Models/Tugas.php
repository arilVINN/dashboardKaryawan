<?php

namespace App\Models;

use App\Events\TugasDitugaskan;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    protected $table = 'tugas';

    protected $primaryKey = 'id_tugas';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_tugas',
        'karyawan_id_karyawan',
        'judul_tugas',
        'deskripsi',
        'file_pendukung',
        'link_pendukung',
        'deadline',
        'progress',
        'status',
        'tanggal_dibuat',
        'tanggal_update',
    ];

    protected static function booted(): void
    {
        static::created(function (Tugas $tugas): void {
            TugasDitugaskan::dispatch($tugas);
        });
    }

    public function karyawan()
    {
        return $this->belongsTo(
            Karyawan::class,
            'karyawan_id_karyawan',
            'id_karyawan'
        );
    }

    public function pesans()
    {
        return $this->hasMany(Pesan::class, 'tugas_id_tugas');
    }

    public function latestPesan()
    {
        return $this->hasOne(
            Pesan::class,
            'tugas_id_tugas'
        )->latestOfMany('created_at');
    }

    public function submitTugas()
    {
        return $this->hasMany(
            SubmitTugas::class,
            'tugas_id_tugas'
        );
    }
}
