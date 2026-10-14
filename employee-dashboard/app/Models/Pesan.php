<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesan extends Model
{
    protected $table = 'pesans';

    protected $primaryKey = 'id_pesan';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_pesan',
        'judul_pesan',
        'deskripsi',
        'tipe',
        'link_lampiran',
        'file_lampiran',
        'tanggal_pesan',
        'tugas_id_tugas',
        'tugas_karyawan_id_karyawan',
        'pengirim_id_user',
        'penerima_id_user',
        'balasan_dari_id_pesan',
        'dibaca_at',
    ];

    protected $casts = [
        'dibaca_at' => 'datetime',
    ];

    /** Indonesian short month names used by the date formatters below. */
    private const BULAN_SINGKAT = [
        1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
    ];

    public function tugas()
    {
        return $this->belongsTo(
            Tugas::class,
            'tugas_id_tugas',
            'id_tugas'
        );
    }

    public function pengirim()
    {
        return $this->belongsTo(
            User2::class,
            'pengirim_id_user',
            'id_user'
        );
    }

    public function penerima()
    {
        return $this->belongsTo(
            User2::class,
            'penerima_id_user',
            'id_user'
        );
    }

    public function balasanDari(): BelongsTo
    {
        return $this->belongsTo(self::class, 'balasan_dari_id_pesan', 'id_pesan');
    }

    public function balasan(): HasMany
    {
        return $this->hasMany(self::class, 'balasan_dari_id_pesan', 'id_pesan');
    }

    /**
     * "8 Okt 2026" plus " 12:56" when a time is given. Indonesian month
     * abbreviations; "-" when the date is missing or unparseable.
     */
    public static function formatTanggalJam($tanggal, $waktu = null): string
    {
        $d = self::parseTanggal($tanggal);

        if ($d === null) {
            return '-';
        }

        $out = self::formatTanggal($d);

        $jam = self::formatJam($waktu);
        if ($jam !== null) {
            $out .= ' '.$jam;
        }

        return $out;
    }

    /**
     * Compact list format: "Hari ini, 12:56" for messages from today, otherwise
     * the bare date "8 Okt 2026". Falls back to '-' when there is no date.
     */
    public static function formatTanggalRelatif($tanggal, $waktu = null): string
    {
        $d = self::parseTanggal($tanggal);

        if ($d === null) {
            return '-';
        }

        if ($d->isToday()) {
            $jam = self::formatJam($waktu);

            return $jam !== null ? 'Hari ini, '.$jam : 'Hari ini';
        }

        return self::formatTanggal($d);
    }

    private static function parseTanggal($tanggal): ?Carbon
    {
        if (empty($tanggal)) {
            return null;
        }

        try {
            return $tanggal instanceof \DateTimeInterface
                ? Carbon::parse($tanggal->format('Y-m-d'))
                : Carbon::parse($tanggal);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function formatTanggal(Carbon $d): string
    {
        return $d->day.' '.self::BULAN_SINGKAT[$d->month].' '.$d->year;
    }

    /** "12:56" in the app timezone, or null when the time is missing/invalid. */
    private static function formatJam($waktu): ?string
    {
        if (empty($waktu)) {
            return null;
        }

        try {
            // Instants (e.g. ISO "Z" strings) convert to the app timezone;
            // naive strings are already in it, so this is a no-op for them.
            return Carbon::parse($waktu)->setTimezone(config('app.timezone', 'UTC'))->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }
}
