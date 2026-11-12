<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRequest extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'kategori',
        'jenis',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'keperluan',
        'status',
        'pemohon_id_user',
        'diproses_oleh_id_user',
        'diproses_pada',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'diproses_pada' => 'datetime',
    ];

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User2::class, 'pemohon_id_user', 'id_user');
    }

    public function pemroses(): BelongsTo
    {
        return $this->belongsTo(User2::class, 'diproses_oleh_id_user', 'id_user');
    }
}
