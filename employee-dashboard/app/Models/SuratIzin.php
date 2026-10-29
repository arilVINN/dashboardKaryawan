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
        'jenis_izin',       // <-- Tambahkan ini
        'judul_surat',
        'tanggal_mulai',    // <-- Tambahkan ini
        'tanggal_selesai',  // <-- Tambahkan ini
        'file',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'tanggal_izin' => 'date',
        'tanggal_mulai' => 'date',   // <-- Tambahkan ini
        'tanggal_selesai' => 'date', // <-- Tambahkan ini
    ];

    public function suratIzins()
    {
        return $this->hasMany(SuratIzin::class, 'karyawan_id_karyawan', 'id_karyawan');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id_karyawan', 'id_karyawan');
    }
}
