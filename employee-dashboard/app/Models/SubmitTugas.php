<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmitTugas extends Model
{
    protected $table = 'submit_tugas';

    protected $primaryKey = 'id_submit_tugas';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_submit_tugas',
        'link_submit',
        'file_hasil',
        'catatan_karyawan',
        'catatan_revisi',
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
