<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jabatan extends Model
{
    protected $table = 'jabatan';

    protected $fillable = [
        'division_id',
        'nama',
        'role_id_role',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'division_id', 'id_divisi');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id_role', 'id_role');
    }

    /**
     * Roles a jabatan may grant (superadmin is CLI-managed only).
     */
    public static function roleOptions(): array
    {
        return config('employee.jabatan_role', ['staff', 'kadiv', 'hrd', 'direktur']);
    }

    public function karyawans(): HasMany
    {
        return $this->hasMany(Karyawan::class, 'jabatan_id');
    }

    public function jumlahKaryawan(): int
    {
        return $this->karyawans()
            ->whereNull('tanggal_keluar')
            ->count();
    }

    /**
     * Names are stored trimmed with single spaces; this is the canonical form
     * used for duplicate detection and the unique index.
     */
    public static function normaliseNama(string $nama): string
    {
        return trim(preg_replace('/\s+/u', ' ', $nama) ?? '');
    }

    public function label(): string
    {
        return $this->aktif ? $this->nama : $this->nama.' (nonaktif)';
    }
}
