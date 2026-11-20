<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Divisi extends Model
{
    protected $table = 'divisis';
    protected $primaryKey = 'id_divisi';

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The three kinds of nodes in the company hierarchy. Only `divisi` nodes
     * are real operating divisions; `direktur` and `manajemen` are structural
     * levels above them.
     */
    public const TIPE_DIREKTUR = 'direktur';
    public const TIPE_MANAJEMEN = 'manajemen';
    public const TIPE_DIVISI = 'divisi';

    public const TIPE_OPTIONS = [
        self::TIPE_DIREKTUR => 'Direktur Utama',
        self::TIPE_MANAJEMEN => 'Manajemen',
        self::TIPE_DIVISI => 'Divisi',
    ];

    /**
     * Stable codes for the units that business logic needs to recognise.
     * Everything else should be driven by `tipe` and `kapasitas`, not names.
     */
    public const KODE_DIREKTUR = 'DIRUT';
    public const KODE_MANAJEMEN = 'MGT';
    public const KODE_HR = 'HR';
    public const KODE_FINANCE = 'FIN';
    public const KODE_IT = 'IT';

    protected $fillable = [
        'id_divisi',
        'parent_id',
        'kode_divisi',
        'nama_divisi',
        'tipe',
        'level',
        'kapasitas',
        'urutan',
        'status_aktif',
    ];

    protected $casts = [
        'level' => 'integer',
        'kapasitas' => 'integer',
        'urutan' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function karyawans(): HasMany
    {
        return $this->hasMany(
            Karyawan::class,
            'divisi_id_divisi',
            'id_divisi'
        );
    }

    public function jabatans(): HasMany
    {
        return $this->hasMany(Jabatan::class, 'division_id', 'id_divisi');
    }

    public function jabatansAktif(): HasMany
    {
        return $this->jabatans()->where('aktif', true)->orderBy('nama');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'id_divisi');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id_divisi')
            ->orderBy('urutan')
            ->orderBy('id_divisi');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /**
     * Hierarchy order: Direktur Utama, Manajemen, then the divisions in the
     * configured order.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('level')
            ->orderBy('urutan')
            ->orderBy('id_divisi');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status_aktif', 'Aktif');
    }

    /**
     * Only real operating divisions (excludes the Direktur Utama and the
     * Manajemen grouping level).
     */
    public function scopeUnit(Builder $query): Builder
    {
        return $query->where('tipe', self::TIPE_DIVISI);
    }

    /**
     * Operating divisions that can own a budget. Finance is excluded because
     * it manages the budget instead of receiving one.
     */
    public function scopeUnitNonFinance(Builder $query): Builder
    {
        return $query->unit()
            ->where('kode_divisi', '!=', self::KODE_FINANCE);
    }

    // ─── Hierarchy helpers ────────────────────────────────────────────────────

    public function isDirekturUtama(): bool
    {
        return $this->tipe === self::TIPE_DIREKTUR;
    }

    public function isManajemen(): bool
    {
        return $this->tipe === self::TIPE_MANAJEMEN;
    }

    public function isDivisi(): bool
    {
        return $this->tipe === self::TIPE_DIVISI;
    }

    public function isFinance(): bool
    {
        return strtoupper((string) $this->kode_divisi) === self::KODE_FINANCE;
    }

    public function isHr(): bool
    {
        return strtoupper((string) $this->kode_divisi) === self::KODE_HR;
    }

    public function isIt(): bool
    {
        return strtoupper((string) $this->kode_divisi) === self::KODE_IT;
    }

    /**
     * A single-person unit (Direktur Utama, HR, Finance & Accounting).
     */
    public function isSinglePerson(): bool
    {
        return $this->kapasitas === 1;
    }

    /**
     * A grouping node such as Manajemen. It never holds employees directly.
     */
    public function isGrouping(): bool
    {
        return $this->kapasitas === 0
            || $this->tipe === self::TIPE_MANAJEMEN;
    }

    /**
     * Whether another employee may be placed in this node. Single-person
     * units refuse a second member and grouping nodes refuse everybody.
     */
    public function canAcceptMember(?string $exceptKaryawanId = null): bool
    {
        if ($this->isGrouping()) {
            return false;
        }

        if ($this->kapasitas === null) {
            return true;
        }

        $members = $this->karyawans()
            ->whereNull('tanggal_keluar')
            ->when($exceptKaryawanId, fn (Builder $query) => $query->where('id_karyawan', '!=', $exceptKaryawanId));

        return $members->count() < $this->kapasitas;
    }

    public function capacityLabel(): string
    {
        if ($this->isGrouping()) {
            return 'Tanpa anggota langsung';
        }

        if ($this->kapasitas === null) {
            return 'Tidak terbatas';
        }

        return 'Maksimal '.$this->kapasitas.' orang';
    }

    /**
     * The person in charge of this node: the division head, or the only
     * occupant of a single-person unit.
     */
    public function penanggungJawab(): ?Karyawan
    {
        $this->loadMissing('karyawans.user.role');

        $members = $this->karyawans
            ->filter(fn (Karyawan $karyawan) => $karyawan->tanggal_keluar === null);

        if ($members->isEmpty()) {
            return null;
        }

        if ($this->isSinglePerson() || $this->isDirekturUtama()) {
            return $members->first();
        }

        return $members->first(fn (Karyawan $karyawan) => $karyawan->isKadiv());
    }

    /**
     * Members below the head. Used to display the "kepala divisi + staff"
     * structure without counting the head as staff.
     */
    public function anggotaStaff(): Collection
    {
        $this->loadMissing('karyawans.user.role');

        $headId = $this->penanggungJawab()?->id_karyawan;

        return $this->karyawans
            ->filter(fn (Karyawan $karyawan) => $karyawan->tanggal_keluar === null)
            ->reject(fn (Karyawan $karyawan) => $karyawan->id_karyawan === $headId)
            ->values();
    }

    public function labelTipe(): string
    {
        return self::TIPE_OPTIONS[$this->tipe] ?? ucfirst((string) $this->tipe);
    }

    // ─── Keuangan Relations ───────────────────────────────────────────────────

    public function alokasiAnggaran(): HasMany
    {
        return $this->hasMany(KeuanganAldiv::class, 'divisi_id_divisi', 'id_divisi');
    }

    public function pemasukan(): HasMany
    {
        return $this->hasMany(KPemasukan::class, 'divisi_id_divisi', 'id_divisi');
    }

    public function pengeluaran(): HasMany
    {
        return $this->hasMany(KPengeluaran::class, 'divisi_id_divisi', 'id_divisi');
    }

    public function permintaanDana(): HasMany
    {
        return $this->hasMany(KPermintDana::class, 'divisi_id_divisi', 'id_divisi');
    }

    public function laporanLabaRugi(): HasMany
    {
        return $this->hasMany(KLabaRugi::class, 'divisi_id_divisi', 'id_divisi');
    }
}
