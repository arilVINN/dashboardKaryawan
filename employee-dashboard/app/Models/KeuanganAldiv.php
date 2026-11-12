<?php

namespace App\Models;

use App\Helpers\FormatAkuntansi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeuanganAldiv extends Model
{
    protected $table      = 'keuangan_aldiv';
    protected $primaryKey = 'id_kad';

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'id_kad',
        'periode',
        'nominal_alokasi',
        'keterangan',
        'divisi_id_divisi',
        'user_id_user',
    ];

    protected $casts = [
        'nominal_alokasi' => 'float',
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

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Nominal alokasi diformat dengan notasi akuntansi rupiah.
     */
    public function getNominalFormattedAttribute(): string
    {
        return FormatAkuntansi::formatRupiah($this->nominal_alokasi);
    }
}
