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

    /**
     * Jenis pengajuan yang membutuhkan isian jam.
     *
     * @var list<string>
     */
    public const JENIS_DENGAN_JAM = [
        'Lupa Absen',
        'Datang Terlambat',
        'Pulang Lebih Awal',
    ];

    protected $fillable = [
        'id_surat',
        'karyawan_id_karyawan',
        'tanggal_pengajuan',
        'jenis_izin',
        'jam_pengajuan',
        'keperluan',
        'kategori_pengajuan',
        'judul_surat',
        'tanggal_mulai',
        'tanggal_selesai',
        'file',
        'keterangan',
        'status',
        'tahap',
        'kadiv_id_karyawan',
        'kadiv_signature_path',
        'kadiv_approved_at',
        'kadiv_rejected_at',
        'hrd_id_user',
        'hrd_signature_path',
        'hrd_approved_at',
        'hrd_rejected_at',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_izin' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'kadiv_approved_at' => 'datetime',
        'kadiv_rejected_at' => 'datetime',
        'hrd_approved_at' => 'datetime',
        'hrd_rejected_at' => 'datetime',
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

    public function getJamPengajuanLabelAttribute(): ?string
    {
        $value = $this->attributes['jam_pengajuan'] ?? null;

        if (! $value) {
            return null;
        }

        return substr((string) $value, 0, 5);
    }

    public function getTahapLabelAttribute(): string
    {
        if ($this->status === 'Disetujui') {
            return 'Disetujui';
        }

        if ($this->status === 'Ditolak') {
            return 'Ditolak';
        }

        return $this->tahap === 'kadiv' ? 'Menunggu Kadiv' : 'Menunggu HRD';
    }

    public function kadiv()
    {
        return $this->belongsTo(Karyawan::class, 'kadiv_id_karyawan', 'id_karyawan');
    }

    public function hrd()
    {
        return $this->belongsTo(User2::class, 'hrd_id_user', 'id_user');
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
