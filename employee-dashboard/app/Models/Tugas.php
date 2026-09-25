<?php

namespace App\Models;

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
        'deadline',
        'progress',
        'status',
        'tanggal_dibuat',
        'tanggal_update',
    ];

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

    public function submitTugas()
    {
        return $this->hasMany(
            SubmitTugas::class,
            'tugas_id_tugas'
        );
    }
}
