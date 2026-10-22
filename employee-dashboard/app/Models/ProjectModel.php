<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectModel extends Model
{
    protected $table = 'projects';

    protected $primaryKey = 'id_project';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_project',
        'nama_project',
        'deskripsi',
        'file_path',
        'file_name',
        'dibuat_oleh_id_user',
    ];

    /**
     * Semua task di dalam project ini, urut sesuai kolom `urutan`.
     */
    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'project_id_project', 'id_project')
            ->orderBy('urutan');
    }

    /**
     * User (kadiv) yang membuat project ini.
     */
    public function pembuat()
    {
        return $this->belongsTo(User2::class, 'dibuat_oleh_id_user', 'id_user');
    }

    /**
     * Jumlah task yang sudah selesai, dipakai buat progress bar.
     */
    public function getTaskSelesaiCountAttribute(): int
    {
        return $this->tasks()->where('selesai', true)->count();
    }

    /**
     * Total task dalam project ini.
     */
    public function getTaskTotalCountAttribute(): int
    {
        return $this->tasks()->count();
    }

    /**
     * Persentase progress (0-100), dipakai buat progress bar di UI.
     */
    public function getProgressPercentAttribute(): int
    {
        $total = $this->task_total_count;
        return $total > 0 ? (int) round(($this->task_selesai_count / $total) * 100) : 0;
    }
}