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
}