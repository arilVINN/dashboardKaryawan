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
        'assigned_by_user_id',
        'tanggal',
        'aktivitas',
        'prioritas',
        'status',
        'catatan',
        'file_bukti',
        'file_nama_asli',
        'link_bukti',
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

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User2::class, 'assigned_by_user_id', 'id_user');
    }

    public function getIsAssignedByOtherAttribute(): bool
    {
        return ! empty($this->assigned_by_user_id) && $this->assigned_by_user_id !== $this->user_id_user;
    }

    public function getAssignedByLabelAttribute(): ?string
    {
        if (! $this->is_assigned_by_other) {
            return null;
        }

        $assigner = $this->assignedBy;
        if (! $assigner) {
            return 'Pimpinan';
        }

        $nama = $assigner->karyawan?->nama ?? $assigner->username;
        $role = strtoupper($assigner->role?->nama_role ?? 'ATASAN');

        return "{$nama} ({$role})";
    }

    /**
     * Konversi status ke persentase progres sesuai aturan bisnis
     */
    public static function statusToPercentage(?string $status): int
    {
        return match (strtolower((string) $status)) {
            'disetujui', 'approved', 'complete_100', 'fix' => 100,
            'selesai', 'complete', 'completed' => 90,
            'on_progress', 'revisi' => 50,
            default => 0, // 'pending', 'belum', dll.
        };
    }

    /**
     * Persentase progres to do
     */
    public function getPercentageAttribute(): int
    {
        return self::statusToPercentage($this->status);
    }

    /**
     * Label prioritas
     */
    public function getPrioritasLabelAttribute(): string
    {
        return match ($this->prioritas) {
            'tinggi' => 'High',
            'sedang' => 'Medium',
            'rendah' => 'Low',
            default => ucfirst((string) $this->prioritas),
        };
    }

    /**
     * Label status dengan persentase
     */
    public function getStatusLabelAttribute(): string
    {
        return match (strtolower((string) $this->status)) {
            'disetujui', 'approved', 'complete_100', 'fix' => 'Complete 100% (100%)',
            'selesai', 'complete', 'completed' => 'Completed (90%)',
            'revisi' => 'Revisi (50%)',
            'on_progress' => 'In Progress (50%)',
            default => 'Pending (0%)',
        };
    }
}
