<?php

namespace App\Models;

use App\Helpers\FormatAkuntansi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KPemasukan extends Model
{
    protected $table      = 'kpemasukan';
    protected $primaryKey = 'id_kp';

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'id_kp',
        'kode_transaksi',
        'kategori_pemasukan',
        'nominal',
        'tanggal_masuk',
        'keterangan',
        'bukti_dokumen',
        'divisi_id_divisi',
        'user_id_user',
    ];

    protected $casts = [
        'nominal'      => 'float',
        'tanggal_masuk' => 'date',
    ];

    public const KATEGORI_OPTIONS = [
        'Proyek',
        'Layanan / Jasa',
        'Suntikan Modal',
        'Investasi',
        'Lain-lain',
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

    public function getNominalFormattedAttribute(): string
    {
        return FormatAkuntansi::formatRupiah($this->nominal);
    }
}
