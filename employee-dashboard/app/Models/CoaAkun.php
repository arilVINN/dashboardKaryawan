<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoaAkun extends Model
{
    protected $table = 'coa_akuns';

    protected $fillable = [
        'kode',
        'nama',
        'kelompok',
        'kelompok_detail',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public const KELOMPOK = [
        'Aset',
        'Kewajiban',
        'Ekuitas',
        'Pendapatan',
        'Beban',
    ];

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function lines()
    {
        return $this->hasMany(JurnalLine::class, 'account_id');
    }

    public function getLabelAttribute(): string
    {
        return $this->kode . ' ' . $this->nama;
    }
}
