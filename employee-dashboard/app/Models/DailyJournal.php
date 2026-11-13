<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyJournal extends Model
{
    protected $table = 'daily_journals';

    protected $fillable = [
        'user_id_user',
        'karyawan_id_karyawan',
        'tanggal',
        'aktivitas',
        'prioritas',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User2::class, 'user_id_user', 'id_user');
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id_karyawan', 'id_karyawan');
    }

    /**
     * Label prioritas
     */
    public function getPrioritasLabelAttribute(): string
    {
        return match ($this->prioritas) {
            'tinggi' => 'Tinggi',
            'sedang' => 'Sedang',
            'rendah' => 'Rendah',
            default => ucfirst((string) $this->prioritas),
        };
    }

    /**
     * Label status
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'selesai' => 'Selesai',
            'on_progress' => 'On Progress',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }
}
