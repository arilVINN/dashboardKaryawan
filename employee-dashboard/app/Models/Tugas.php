<?php

namespace App\Models;

use App\Events\TugasDitugaskan;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    /**
     * Status tugas sesuai hasil revisi UI/UX (5 status).
     * Status yang dapat disimpan berada di kolom `status`;
     * `telat` tidak pernah disimpan — dihitung dari deadline
     * untuk tugas yang belum selesai.
     */
    public const STATUS_BARU = 'baru';

    public const STATUS_BERJALAN = 'berjalan';

    public const STATUS_MENUNGGU_ACC = 'menunggu di-acc';

    public const STATUS_SUDAH_ACC = 'sudah di-acc';

    public const STATUS_TELAT = 'telat';

    /** Status yang dapat disimpan ke database. */
    public const STATUSES = [
        self::STATUS_BARU,
        self::STATUS_BERJALAN,
        self::STATUS_MENUNGGU_ACC,
        self::STATUS_SUDAH_ACC,
    ];

    /** Semua status yang ditampilkan ke pengguna, termasuk status turunan. */
    public const ALL_STATUSES = [
        self::STATUS_BARU,
        self::STATUS_BERJALAN,
        self::STATUS_MENUNGGU_ACC,
        self::STATUS_SUDAH_ACC,
        self::STATUS_TELAT,
    ];

    protected $table = 'tugas';

    /** Selalu sertakan status efektif (termasuk turunan "telat") di tiap respons. */
    protected $appends = ['status_efektif', 'is_terlambat'];

    protected $primaryKey = 'id_tugas';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_tugas',
        'karyawan_id_karyawan',
        'judul_tugas',
        'deskripsi',
        'file_pendukung',
        'link_pendukung',
        'deadline',
        'progress',
        'status',
        'tanggal_dibuat',
        'tanggal_update',
    ];

    protected static function booted(): void
    {
        static::created(function (Tugas $tugas): void {
            TugasDitugaskan::dispatch($tugas);
        });
    }

    /**
     * For completed work, lateness is based on when it was submitted, even after approval.
     * For unfinished work, lateness is based on the current time and deadline.
     */
    public function isTelat(): bool
    {
        if (empty($this->deadline)) {
            return false;
        }

        if (in_array($this->status, [self::STATUS_MENUNGGU_ACC, self::STATUS_SUDAH_ACC], true)) {
            $submission = $this->relationLoaded('latestSubmitTugas')
                ? $this->latestSubmitTugas
                : $this->latestSubmitTugas()->first();

            return $submission !== null
                && \Illuminate\Support\Carbon::parse($submission->created_at)
                    ->gt(\Illuminate\Support\Carbon::parse($this->deadline));
        }

        return \Illuminate\Support\Carbon::parse($this->deadline)->lt(now());
    }

    /**
     * Status yang ditampilkan: "telat" bila deadline lewat, selain itu status tersimpan.
     */
    public function getStatusEfektifAttribute(): string
    {
        return $this->isTelat() && ! in_array($this->status, [
            self::STATUS_MENUNGGU_ACC,
            self::STATUS_SUDAH_ACC,
        ], true)
            ? self::STATUS_TELAT
            : $this->status;
    }

    public function getIsTerlambatAttribute(): bool
    {
        return $this->isTelat();
    }

    /**
     * Scope: tugas dengan status efektif tertentu (termasuk turunan "telat").
     * Dipakai dashboard untuk menghitung per bucket status.
     */
    public function scopeStatusEfektif($query, string $status)
    {
        if ($status === self::STATUS_TELAT) {
            return $query->where(function ($lateTasks): void {
                $lateTasks->where(function ($unfinished): void {
                    $unfinished->whereNotNull('deadline')
                        ->where('deadline', '<', now())
                        ->whereNotIn('status', [self::STATUS_SUDAH_ACC, self::STATUS_MENUNGGU_ACC]);
                })->orWhere(function ($lateSubmissions): void {
                    $lateSubmissions->whereIn('status', [
                        self::STATUS_MENUNGGU_ACC,
                        self::STATUS_SUDAH_ACC,
                    ])
                        ->whereHas('latestSubmitTugas', function ($submission): void {
                            $submission->whereColumn('submit_tugas.created_at', '>', 'tugas.deadline');
                        });
                });
            });
        }

        // "sudah di-acc" tidak pernah telat, jadi tanpa syarat deadline.
        if ($status === self::STATUS_SUDAH_ACC) {
            return $query->where('status', $status);
        }

        //kalo status nungggu acc, lewat dl tidak dianggap telat
        if ($status === self::STATUS_MENUNGGU_ACC) {
            return $query->where('status', $status);
        }

        // Bucket non-telat lainnya: status tersimpan + tidak sedang telat.
        return $query->where('status', $status)
            ->where(function ($q): void {
                $q->whereNull('deadline')->orWhere('deadline', '>=', now());
            });
    }

    public function karyawan()
    {
        return $this->belongsTo(
            Karyawan::class,
            'karyawan_id_karyawan',
            'id_karyawan'
        );
    }

    public function pesans()
    {
        return $this->hasMany(Pesan::class, 'tugas_id_tugas');
    }

    public function latestPesan()
    {
        return $this->hasOne(
            Pesan::class,
            'tugas_id_tugas'
        )->latestOfMany('created_at');
    }

    public function submitTugas()
    {
        return $this->hasMany(
            SubmitTugas::class,
            'tugas_id_tugas'
        );
    }

    public function latestSubmitTugas()
    {
        return $this->hasOne(
            SubmitTugas::class,
            'tugas_id_tugas',
            'id_tugas'
        )->latestOfMany('created_at');
    }

    public function scopeSelesaiTerlambat($query)
    {
        return $query->whereIn('status', [self::STATUS_MENUNGGU_ACC, self::STATUS_SUDAH_ACC])
            ->whereHas('latestSubmitTugas', function ($submission): void {
                $submission->whereColumn('submit_tugas.created_at', '>', 'tugas.deadline');
            });
    }
}
