<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Karyawan extends Model
{
    protected $table = 'karyawans';

    protected $primaryKey = 'id_karyawan';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_karyawan',
        'nama',
        'jenis_kelamin',
        'tanggal_lahir',
        'tanggal_rekrut',
        'no_telepon',
        'email',
        'alamat',
        'jabatan',
        'divisi_id_divisi',
    ];

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(
            Divisi::class,
            'divisi_id_divisi',
            'id_divisi'
        );
    }

    public function user(): HasOne
    {
        return $this->hasOne(
            User2::class,
            'karyawan_id_karyawan',
            'id_karyawan'
        );
    }

    public function tugas(): HasMany
    {
        return $this->hasMany(
            Tugas::class,
            'karyawan_id_karyawan',
            'id_karyawan'
        );
    }

    public function hasRole(string $roleName): bool
    {
        return strtolower($this->user?->role?->nama_role ?? '') === strtolower($roleName);
    }

    public function isKadiv(): bool
    {
        return $this->hasRole('kadiv');
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }
}
