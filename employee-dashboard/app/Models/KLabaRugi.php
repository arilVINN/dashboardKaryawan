<?php

namespace App\Models;

use App\Helpers\FormatAkuntansi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KLabaRugi extends Model
{
    protected $table      = 'klaba_rugi';
    protected $primaryKey = 'id_klr';

    public $incrementing = false;
    protected $keyType   = 'string';

    // Status verifikasi constants
    public const STATUS_MENUNGGU     = 'menunggu';
    public const STATUS_DISETUJUI    = 'disetujui';
    public const STATUS_PERLU_REVISI = 'perlu_revisi';

    protected $fillable = [
        'id_klr',
        'periode',
        'minggu_ke',
        'tanggal_mulai',
        'tanggal_selesai',
        'total_pendapatan',
        'total_pengeluaran',
        'laba_bersih',
        'margin_persen',
        'bukti_laporan',
        'catatan',
        'status_verifikasi',
        'catatan_verifikasi',
        'tanggal_kirim',
        'divisi_id_divisi',
        'user_id_user',
        'diverifikasi_oleh_id_user',
    ];

    protected $casts = [
        'total_pendapatan'  => 'float',
        'total_pengeluaran' => 'float',
        'laba_bersih'       => 'float',
        'margin_persen'     => 'float',
        'tanggal_mulai'     => 'date',
        'tanggal_selesai'   => 'date',
        'tanggal_kirim'     => 'date',
        'minggu_ke'         => 'integer',
    ];

    // ─── Static Helpers ───────────────────────────────────────────────────────

    /**
     * Hitung margin laba sesuai aturan:
     * - 0% jika salah satu komponen (pendapatan ATAU pengeluaran) adalah 0 / tidak ada
     * - Hitung normal hanya jika KEDUA komponen > 0
     */
    public static function hitungMargin(float $pendapatan, float $pengeluaran): float
    {
        return FormatAkuntansi::hitungMargin($pendapatan, $pengeluaran);
    }

    /**
     * Hitung laba bersih (Pendapatan - Pengeluaran).
     * Nilai negatif = rugi (akan ditampilkan dengan notasi kurung di view).
     */
    public static function hitungLaba(float $pendapatan, float $pengeluaran): float
    {
        return FormatAkuntansi::hitungLaba($pendapatan, $pengeluaran);
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'divisi_id_divisi', 'id_divisi');
    }

    /** Kadiv yang mengirim laporan */
    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User2::class, 'user_id_user', 'id_user');
    }

    public function pengirim(): BelongsTo
    {
        return $this->pelapor();
    }

    /** Kadiv Finance yang memverifikasi */
    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User2::class, 'diverifikasi_oleh_id_user', 'id_user');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    /** Laba / (Rugi) diformat dengan notasi akuntansi standar */
    public function getLabaFormattedAttribute(): string
    {
        return FormatAkuntansi::formatRupiah($this->laba_bersih);
    }

    /** Pendapatan diformat */
    public function getPendapatanFormattedAttribute(): string
    {
        return FormatAkuntansi::formatRupiah($this->total_pendapatan);
    }

    /** Pengeluaran diformat */
    public function getPengeluaranFormattedAttribute(): string
    {
        return FormatAkuntansi::formatRupiah($this->total_pengeluaran);
    }

    /** Margin diformat dengan notasi akuntansi */
    public function getMarginFormattedAttribute(): string
    {
        return FormatAkuntansi::formatMargin($this->margin_persen);
    }

    public function isMenunggu(): bool    { return $this->status_verifikasi === self::STATUS_MENUNGGU; }
    public function isDisetujui(): bool   { return $this->status_verifikasi === self::STATUS_DISETUJUI; }
    public function isPerluRevisi(): bool { return $this->status_verifikasi === self::STATUS_PERLU_REVISI; }
}
