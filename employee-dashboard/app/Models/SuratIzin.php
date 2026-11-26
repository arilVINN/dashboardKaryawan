<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratIzin extends Model
{
    protected $table = 'surat_izin';

    protected $primaryKey = 'id_surat';

    public $incrementing = false;

    protected $keyType = 'string';

    public const KEPERLUAN_OPTIONS = [
        'Dinas',
        'Pribadi',
    ];

    /**
     * Kelompok jenis pengajuan sesuai Form Pengajuan Perizinan Karyawan.
     *
     * @var array<string, list<string>>
     */
    public const JENIS_PENGAJUAN_GROUPS = [
        'Izin' => [
            'Tidak Masuk',
            'Lupa Absen',
            'Datang Terlambat',
            'Pulang Lebih Awal',
            'Sakit',
        ],
        'Cuti' => [
            'Tahunan',
            'Menikah',
            'Melahirkan',
            'Menunggu Istri Melahirkan',
        ],
        'Dinas Luar' => [
            'Kunjungan Klien atau Lapangan',
            'Meeting Luar Kantor / Training',
            'Perjalanan Dinas Luar Kota',
        ],
    ];

    protected $fillable = [
        'id_surat',
        'karyawan_id_karyawan',
        'tanggal_pengajuan',
        'jenis_izin',
        'keperluan',
        'kategori_pengajuan',
        'judul_surat',
        'tanggal_mulai',
        'tanggal_selesai',
        'file',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_izin' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Daftar datar seluruh jenis pengajuan untuk validasi.
     *
     * @return list<string>
     */
    public static function jenisPengajuanOptions(): array
    {
        return array_merge(...array_values(self::JENIS_PENGAJUAN_GROUPS));
    }

    public static function kategoriUntukJenis(string $jenis): ?string
    {
        foreach (self::JENIS_PENGAJUAN_GROUPS as $kategori => $options) {
            if (in_array($jenis, $options, true)) {
                return $kategori;
            }
        }

        return null;
    }

    public static function judulUntuk(string $kategori, string $jenis): string
    {
        return 'Pengajuan '.$kategori.' - '.$jenis;
    }

    public function suratIzins()
    {
        return $this->hasMany(SuratIzin::class, 'karyawan_id_karyawan', 'id_karyawan');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id_karyawan', 'id_karyawan');
    }
}
