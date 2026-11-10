<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Divisi extends Model
{
    protected $table = 'divisis';
    protected $primaryKey = 'id_divisi';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_divisi',
        'kode_divisi',
        'nama_divisi',
        'status_aktif',
    ];

    public function karyawans(): HasMany
    {
        return $this->hasMany(
            Karyawan::class,
            'divisi_id_divisi',
            'id_divisi'
        );
    }

    // ─── Keuangan Relations ───────────────────────────────────────────────────

    public function alokasiAnggaran(): HasMany
    {
        return $this->hasMany(KeuanganAldiv::class, 'divisi_id_divisi', 'id_divisi');
    }

    public function pemasukan(): HasMany
    {
        return $this->hasMany(KPemasukan::class, 'divisi_id_divisi', 'id_divisi');
    }

    public function pengeluaran(): HasMany
    {
        return $this->hasMany(KPengeluaran::class, 'divisi_id_divisi', 'id_divisi');
    }

    public function permintaanDana(): HasMany
    {
        return $this->hasMany(KPermintDana::class, 'divisi_id_divisi', 'id_divisi');
    }

    public function laporanLabaRugi(): HasMany
    {
        return $this->hasMany(KLabaRugi::class, 'divisi_id_divisi', 'id_divisi');
    }
}