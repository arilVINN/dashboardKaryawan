<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SubmitTugas extends Model
{
    /**
     * Generate the next sequential submission ID (SB001, SB002, ...).
     *
     * Only IDs that actually follow the "SB" + digits pattern are counted so
     * legacy/seeded identifiers such as "SUB-OLDA-KR001" cannot break the
     * sequence (they used to sort after "SB..." and reset the counter to 1).
     */
    public static function nextId(): string
    {
        return DB::transaction(function (): string {
            $max = 0;

            foreach (static::query()->lockForUpdate()->pluck('id_submit_tugas') as $id) {
                if (preg_match('/^SB(\d+)$/', (string) $id, $matches)) {
                    $max = max($max, (int) $matches[1]);
                }
            }

            return 'SB'.str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
        });
    }

    protected $table = 'submit_tugas';

    protected $primaryKey = 'id_submit_tugas';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_submit_tugas',
        'link_submit',
        'file_hasil',
        'file_hasil_name',
        'catatan_karyawan',
        'catatan_revisi',
        'file_revisi',
        'file_revisi_name',
        'deadline_revisi',
        'tanggal_submit',
        'status_review',
        'tugas_id_tugas',
        'tugas_karyawan_id_karyawan',
    ];

    public function tugas()
    {
        return $this->belongsTo(
            Tugas::class,
            'tugas_id_tugas',
            'id_tugas'
        );
    }
}
