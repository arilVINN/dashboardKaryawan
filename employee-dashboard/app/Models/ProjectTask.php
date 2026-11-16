<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    protected $table = 'project_tasks';

    protected $primaryKey = 'id_task';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_task',
        'project_id_project',
        'label',
        'karyawan_id_karyawan',
        'selesai',
        'urutan',
        'file_hasil_path',
        'file_hasil_name',
        'tanggal_submit',
    ];

    protected $casts = [
        'selesai' => 'boolean',
        'tanggal_submit' => 'datetime',
    ];

    /**
     * Project induk dari task ini.
     */
    public function project()
    {
        return $this->belongsTo(ProjectModel::class, 'project_id_project', 'id_project');
    }

    /**
     * Staff yang ditugaskan mengerjakan task ini.
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id_karyawan', 'id_karyawan');
    }
}