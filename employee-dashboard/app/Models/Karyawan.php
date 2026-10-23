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
        'tanggal_masuk',
        'tanggal_rekrut',
        'tanggal_akhir_kontrak',
        'no_rekening',
        'no_telepon',
        'email',
        'alamat',
        'jabatan',
        'status_aktif',
        'foto',
        'divisi_id_divisi',
        'pendidikan',
        'job_level',
        'status_karyawan',
        'aktif',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
        'tanggal_akhir_kontrak' => 'date',
        'aktif' => 'boolean',
    ];

    public function getTanggalRekrutAttribute()
    {
        return $this->attributes['tanggal_masuk'] ?? null;
    }

    public function setTanggalRekrutAttribute($value): void
    {
        $this->attributes['tanggal_masuk'] = $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, ['tanggal_masuk', 'tanggal_lahir', 'tanggal_akhir_kontrak'], true)) {
            $this->attributes[$key] = $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null;
            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Jabatan titles without division qualifiers. The division is stored
     * separately in divisi_id_divisi; the forms offer these as a select and
     * the staff API validates against this list.
     */
    public const JABATAN_OPTIONS = [
        'Kepala Divisi',
        'Wakil Kepala Divisi',
        'Staff',
    ];

    public const STATUS_OPTIONS = [
        'Aktif',
        'Tidak Aktif',
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

    public function lampiran(): HasMany
    {
        return $this->hasMany(
            LampiranKaryawan::class,
            'karyawan_id_karyawan',
            'id_karyawan'
        );
    }

    public function lampiranKaryawan(): HasMany
    {
        return $this->lampiran();
    }

    public function hasRole(string $roleName): bool
    {
        return strtolower($this->user?->role?->nama_role ?? '') === strtolower($roleName);
    }

    public function isKadiv(): bool
    {
        return $this->hasRole('kadiv')
            || preg_match('/^kepala\s+divisi(?:\s|$)/i', trim($this->jabatan ?? '')) === 1;
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }
}
