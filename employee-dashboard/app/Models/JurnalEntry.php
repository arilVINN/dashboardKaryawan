<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalEntry extends Model
{
    protected $table = 'jurnal_entries';

    protected $fillable = [
        'entry_date',
        'ref_code',
        'source_type',
        'source_id',
        'jurnal_divisi_id',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date:Y-m-d',
    ];

    public function source()
    {
        return $this->morphTo();
    }

    public function divisi()
    {
        return $this->belongsTo(JurnalDivisi::class, 'jurnal_divisi_id');
    }

    public function lines()
    {
        return $this->hasMany(JurnalLine::class, 'jurnal_entry_id')
            ->orderBy('sort_order');
    }

    public function creator()
    {
        return $this->belongsTo(User2::class, 'created_by', 'id_user');
    }

    public function getSourceUrlAttribute(): ?string
    {
        $source = $this->source;

        if (! $source) {
            return null;
        }

        if ($source instanceof KPemasukan) {
            return route('kadiv.keuangan.pemasukan');
        }

        if ($source instanceof KPengeluaran) {
            return $source->kpd_id_kpd
                ? route('kadiv.keuangan.permintaan.show', $source->kpd_id_kpd)
                : route('kadiv.keuangan.pengeluaran');
        }

        return null;
    }

    public function getSourceLabelAttribute(): ?string
    {
        return match (true) {
            $this->source_type === KPemasukan::class => 'Kas masuk',
            $this->source_type === KPengeluaran::class && $this->source?->kpd_id_kpd => 'Pencairan dana',
            $this->source_type === KPengeluaran::class => 'Kas keluar',
            $this->source_type === self::class => 'Jurnal pembatalan',
            default => 'Jurnal manual',
        };
    }
}
