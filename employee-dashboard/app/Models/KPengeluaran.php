<?php

namespace App\Models;

use App\Helpers\FormatAkuntansi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KPengeluaran extends Model
{
    protected $table      = 'kpengeluaran';
    protected $primaryKey = 'id_kpengeluaran';

    public $incrementing = false;
    protected $keyType   = 'string';

    public const METODE_OPTIONS = [
        'Transfer Bank',
        'Kas Kecil',
        'Talangan Direktur',
    ];

    protected $fillable = [
        'id_kpengeluaran',
        'kode_pengeluaran',
        'kpd_id_kpd',
        'nominal',
        'tanggal_cair',
        'metode_pembayaran',
        'bukti_pencairan',
        'keterangan',
        'divisi_id_divisi',
        'user_id_user',
    ];

    protected $casts = [
        'nominal'      => 'float',
        'tanggal_cair' => 'date',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'divisi_id_divisi', 'id_divisi');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User2::class, 'user_id_user', 'id_user');
    }

    /** Permintaan dana asal pencairan ini (nullable) */
    public function permintaanDana(): BelongsTo
    {
        return $this->belongsTo(KPermintDana::class, 'kpd_id_kpd', 'id_kpd');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getNominalFormattedAttribute(): string
    {
        return FormatAkuntansi::formatRupiah($this->nominal);
    }
}
