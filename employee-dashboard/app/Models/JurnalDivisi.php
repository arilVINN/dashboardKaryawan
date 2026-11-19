<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Kode divisi pada Jurnal Umum = SUB-PERUSAHAAN.
 * Tidak terhubung dengan tabel `divisis` (departemen perusahaan).
 */
class JurnalDivisi extends Model
{
    protected $table = 'jurnal_divisis';

    protected $fillable = [
        'kode',
        'nama',
    ];

    public function entries()
    {
        return $this->hasMany(JurnalEntry::class, 'jurnal_divisi_id');
    }
}
