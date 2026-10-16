<?php

namespace App\Models;

use App\Events\TugasDitugaskan;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    /**
     * Status tugas sesuai hasil revisi UI/UX (5 status).
     * Status yang dapat disimpan berada di kolom `status`;
     * `terlambat` tidak pernah disimpan — dihitung dari deadline
     * untuk tugas yang belum selesai.
     */
    public const STATUS_BARU = 'baru';

    public const STATUS_BERJALAN = 'berjalan';

    public const STATUS_MENUNGGU_ACC = 'menunggu di-acc';

    public const STATUS_SUDAH_ACC = 'sudah di-acc';

    public const STATUS_TERLAMBAT = 'terlambat';

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
        self::STATUS_TERLAMBAT,
    ];

    protected $table = 'tugas';

    /** Selalu sertakan status efektif (termasuk turunan "terlambat") di tiap respons. */
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
    public function isTerlambat(): bool
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
     * Status yang ditampilkan: "terlambat" bila deadline lewat, selain itu status tersimpan.
     * Jika status masih "baru" tetapi sudah lebih dari 1 hari sejak dibuat, otomatis dianggap "berjalan".
     */
    public function getStatusEfektifAttribute(): string
    {
        if ($this->isTerlambat() && ! in_array($this->status, [
            self::STATUS_MENUNGGU_ACC,
            self::STATUS_SUDAH_ACC,
        ], true)) {
            return self::STATUS_TERLAMBAT;
        }

        if ($this->status === self::STATUS_BARU && $this->tanggal_dibuat) {
            $created = \Illuminate\Support\Carbon::parse($this->tanggal_dibuat);
            if ($created->diffInDays(now()) >= 1) {
                return self::STATUS_BERJALAN;
            }
        }

        return $this->status;
    }

    public function getIsTerlambatAttribute(): bool
    {
        return $this->isTerlambat();
    }

    /**
     * Scope: tugas dengan status efektif tertentu (termasuk turunan "terlambat").
     * Dipakai dashboard untuk menghitung per bucket status.
     */
    public function scopeStatusEfektif($query, string $status)
    {
        if ($status === self::STATUS_TERLAMBAT) {
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

        // "sudah di-acc" tidak pernah terlambat, jadi tanpa syarat deadline.
        if ($status === self::STATUS_SUDAH_ACC) {
            return $query->where('status', $status);
        }

        //kalo status nungggu acc, lewat dl tidak dianggap terlambat
        if ($status === self::STATUS_MENUNGGU_ACC) {
            return $query->where('status', $status);
        }

        // Bucket non-terlambat:
        if ($status === self::STATUS_BARU) {
            return $query->where('status', self::STATUS_BARU)
                ->where(function ($q): void {
                    $q->whereNull('deadline')->orWhere('deadline', '>=', now());
                })
                ->where(function ($q): void {
                    $q->whereNull('tanggal_dibuat')->orWhere('tanggal_dibuat', '>=', now()->subDay()->toDateString());
                });
        }

        if ($status === self::STATUS_BERJALAN) {
            return $query->where(function ($q): void {
                $q->where(function ($sub): void {
                    $sub->where('status', self::STATUS_BERJALAN)
                        ->where(function ($dead): void {
                            $dead->whereNull('deadline')->orWhere('deadline', '>=', now());
                        });
                })->orWhere(function ($sub): void {
                    $sub->where('status', self::STATUS_BARU)
                        ->where('tanggal_dibuat', '<', now()->subDay()->toDateString())
                        ->where(function ($dead): void {
                            $dead->whereNull('deadline')->orWhere('deadline', '>=', now());
                        });
                });
            });
        }

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
