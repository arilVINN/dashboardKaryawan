<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'jabatan_id',
        'status_aktif',
        'tanggal_keluar',
        'foto',
        'divisi_id_divisi',
        'atasan_id',
        'pendidikan',
        'job_level',
        'status_karyawan',
        'aktif',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
        'tanggal_akhir_kontrak' => 'date',
        'tanggal_keluar' => 'date',
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
        if (in_array($key, ['tanggal_masuk', 'tanggal_lahir', 'tanggal_akhir_kontrak', 'tanggal_keluar'], true)) {
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
    public const JABATAN_DIREKTUR = 'Direktur Utama';
    public const JABATAN_KEPALA = 'Kepala Divisi';
    public const JABATAN_STAFF = 'Staff';
    public const JABATAN_HRD_MANAGER = 'HRD Manager';
    public const JABATAN_MARKOM = 'Markom';
    public const JABATAN_LAYOUTER = 'Layouter';

    public const JABATAN_OPTIONS = [
        self::JABATAN_DIREKTUR,
        self::JABATAN_KEPALA,
        self::JABATAN_STAFF,
        self::JABATAN_HRD_MANAGER,
        self::JABATAN_MARKOM,
        self::JABATAN_LAYOUTER,
    ];

    public const STATUS_OPTIONS = [
        'Aktif',
        'Tidak Aktif',
    ];

    /**
     * The select options come from the shared config so the form and the API
     * validation can never drift apart.
     */
    public const PENDIDIKAN_OPTIONS = [
        'SMK',
        'D3',
        'S1',
        'S2',
        'S3',
    ];

    public const JOB_LEVEL_OPTIONS = [
        'Magang',
        'Junior',
        'Senior',
        'Manager',
    ];

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(
            Divisi::class,
            'divisi_id_divisi',
            'id_divisi'
        );
    }

    public function jabatanRef(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function jabatanLabel(): string
    {
        $label = $this->jabatanRef?->label() ?? $this->jabatan;
        $label = trim((string) $label);

        return $label !== '' ? $label : '-';
    }

    public static function pendidikanOptions(): array
    {
        return config('employee.pendidikan', self::PENDIDIKAN_OPTIONS);
    }

    public static function jobLevelOptions(): array
    {
        return config('employee.job_level', self::JOB_LEVEL_OPTIONS);
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

    public function isDirektur(): bool
    {
        return $this->hasRole('direktur')
            || $this->hasRole('direksi')
            || strtolower($this->job_level ?? '') === 'direksi'
            || str_contains(strtolower($this->jabatan ?? ''), 'direktur');
    }

    public function isKadiv(): bool
    {
        return $this->hasRole('kadiv')
            || $this->isDirektur()
            || preg_match('/^kepala\s+divisi(?:\s|$)/i', trim($this->jabatan ?? '')) === 1;
    }

    public function isDirekturUtama(): bool
    {
        return preg_match('/^direktur\s+utama(?:\s|$)/i', trim($this->jabatan ?? '')) === 1;
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff') || $this->isDirektur();
    }

    /**
     * Jabatan Markom (bisa diberi keterangan, mis. "Markom Publikasi Buku").
     */
    public function isMarkom(): bool
    {
        // Termasuk variasi "Marcom Buku" yang sudah dipakai di data lama.
        return preg_match('/(^|\s)(?:markom|marcom)(\s|$)/i', trim((string) $this->jabatan)) === 1;
    }

    /**
     * Cek apakah karyawan ini berada di unit Finance & Accounting.
     * Delegated to the division model so the code lives in one place.
     */
    public function isFinance(): bool
    {
        return $this->divisi?->isFinance() ?? false;
    }

    /**
     * Cek apakah karyawan ini adalah Kepala Divisi Finance.
     */
    public function isKadivFinance(): bool
    {
        return ($this->isKadiv() && $this->isFinance()) || $this->isDirektur();
    }

    public function suratIzins(): HasMany
    {
        return $this->hasMany(
            SuratIzin::class,
            'karyawan_id_karyawan',
            'id_karyawan'
        );
    }

    public function atasan(): BelongsTo
    {
        return $this->belongsTo(
            Karyawan::class,
            'atasan_id',
            'id_karyawan'
        );
    }

    public function bawahan(): HasMany
    {
        return $this->hasMany(
            Karyawan::class,
            'atasan_id',
            'id_karyawan'
        );
    }

    public function scopeBirthdayToday(Builder $query): Builder
    {
        return $query
            ->where('aktif', true)
            ->whereNotNull('tanggal_lahir')
            ->whereMonth('tanggal_lahir', now()->month)
            ->whereDay('tanggal_lahir', now()->day);
    }
}
