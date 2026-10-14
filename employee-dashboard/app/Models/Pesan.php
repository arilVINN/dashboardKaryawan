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
        if (empty($tanggal)) {
            return '-';
        }

        try {
            $d = $tanggal instanceof \DateTimeInterface
                ? Carbon::parse($tanggal->format('Y-m-d'))
                : Carbon::parse($tanggal);
        } catch (\Throwable) {
            return '-';
        }

        static $bulan = [
            1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
        ];

        $out = $d->day.' '.$bulan[$d->month].' '.$d->year;

        if (! empty($waktu)) {
            try {
                // Instants (e.g. ISO "Z" strings) convert to the app timezone;
                // naive strings are already in it, so this is a no-op for them.
                $out .= ' '.Carbon::parse($waktu)->setTimezone(config('app.timezone', 'UTC'))->format('H:i');
            } catch (\Throwable) {
            }
        }

        return $out;
    }
}
