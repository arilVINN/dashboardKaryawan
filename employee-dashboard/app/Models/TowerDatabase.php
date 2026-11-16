<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TowerDatabase extends Model
{
    use HasFactory;

    protected $table = 'tower_databases';

    protected $fillable = [
        'nama',
        'keterangan',
        'jenis',
        'file_path',
        'file_name',
        'file_size',
        'file_ext',
        'nilai_kontak',
        'email_kontak',
        'url_link',
        'dibuat_oleh_id_karyawan',
    ];

    public const JENIS_FILE = 'file';
    public const JENIS_NOMOR = 'nomor';
    public const JENIS_LINK = 'link';

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'dibuat_oleh_id_karyawan', 'id_karyawan');
    }
}
