<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratIzin extends Model
{
    protected $table = 'surat_izin';
    protected $primaryKey = 'id_surat';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_surat',
        'karyawan_id_karyawan',
        'judul_surat',
        'tanggal_izin',
        'file',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'tanggal_izin' => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id_karyawan', 'id_karyawan');
    }
}