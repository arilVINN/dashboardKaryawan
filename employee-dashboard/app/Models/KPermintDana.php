<?php

namespace App\Models;

use App\Helpers\FormatAkuntansi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KPermintDana extends Model
{
    protected $table      = 'kpermint_dana';
    protected $primaryKey = 'id_kpd';

    public $incrementing = false;
    protected $keyType   = 'string';

    // Status constants
    public const STATUS_MENUNGGU  = 'menunggu';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK   = 'ditolak';
    public const STATUS_DICAIRKAN = 'dicairkan';

    protected $fillable = [
        'id_kpd',
        'nomor_pengajuan',
        'judul_pengajuan',
        'keperluan',
        'nominal_diajukan',
        'lampiran_proposal',
        'status',
        'catatan_finance',
        'tanggal_disetujui',
        'divisi_id_divisi',
        'user_id_user',
        'disetujui_oleh_id_user',
    ];

    protected $casts = [
        'nominal_diajukan' => 'float',
        'tanggal_disetujui' => 'date',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'divisi_id_divisi', 'id_divisi');
    }

    /** Kadiv yang mengajukan */
    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User2::class, 'user_id_user', 'id_user');
    }

    public function diajukanOleh(): BelongsTo
    {
        return $this->pemohon();
    }

    /** Kadiv Finance yang menyetujui */
    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User2::class, 'disetujui_oleh_id_user', 'id_user');
    }

    /** Realisasi pencairan dana */
    public function pengeluaran(): HasOne
    {
        return $this->hasOne(KPengeluaran::class, 'kpd_id_kpd', 'id_kpd');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getNominalFormattedAttribute(): string
    {
        return FormatAkuntansi::formatRupiah($this->nominal_diajukan);
    }

    public function isMenunggu(): bool   { return $this->status === self::STATUS_MENUNGGU; }
    public function isDisetujui(): bool  { return $this->status === self::STATUS_DISETUJUI; }
    public function isDitolak(): bool    { return $this->status === self::STATUS_DITOLAK; }
    public function isDicairkan(): bool  { return $this->status === self::STATUS_DICAIRKAN; }
}
