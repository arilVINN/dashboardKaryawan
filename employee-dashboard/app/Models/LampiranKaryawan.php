<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LampiranKaryawan extends Model
{
    protected $table = 'lampiran_karyawan';

    protected $primaryKey = 'id_lampiran';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_lampiran',
        'karyawan_id_karyawan',
        'jenis_lampiran',
        'nama_file',
        'path_file',
    ];

    public const JENIS_OPTIONS = [
        'lamaran' => 'Surat Lamaran',
        'ijazah' => 'Ijazah',
        'transkrip' => 'Transkrip Nilai',
        'ktp' => 'KTP',
        'kk' => 'Kartu Keluarga (KK)',
        'offering_letter' => 'Offering Letter',
        'lainnya' => 'Dokumen Lainnya / SK',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model): void {
            if (empty($model->id_lampiran)) {
                $model->id_lampiran = (string) Str::uuid();
            }
        });
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(
            Karyawan::class,
            'karyawan_id_karyawan',
            'id_karyawan'
        );
    }
}

