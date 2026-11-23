<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StrukturOrganisasi extends Model
{
    protected $table = 'struktur_organisasis';

    protected $fillable = [
        'nama',
        'jabatan',
        'tipe_node',
        'status_kerja',
        'garis_putus',
        'divisi',
        'parent_id',
        'urutan',
        'level',
        'foto',
        'email',
        'no_telepon',
        'keterangan',
        'id_karyawan',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'level' => 'integer',
        'parent_id' => 'integer',
        'garis_putus' => 'boolean',
    ];

    /**
     * Relasi ke atasan (parent node).
     */
    public function parent()
    {
        return $this->belongsTo(StrukturOrganisasi::class, 'parent_id');
    }

    /**
     * Relasi ke bawahan langsung (child nodes) berurutan.
     */
    public function children()
    {
        return $this->hasMany(StrukturOrganisasi::class, 'parent_id')
            ->orderBy('urutan', 'asc')
            ->orderBy('id', 'asc');
    }

    /**
     * Relasi hierarkis ke seluruh turunan secara rekursif.
     */
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Relasi ke data Karyawan (jika ditautkan).
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }

    /**
     * Hitung level kedalaman hierarki node ini.
     */
    public function calculateLevel(): int
    {
        if (empty($this->parent_id)) {
            return 1;
        }

        $parent = $this->parent ?: static::find($this->parent_id);
        return $parent ? $parent->level + 1 : 1;
    }

    /**
     * Perbarui level node ini dan seluruh turunannya secara rekursif.
     */
    public function updateHierarchyLevels(): void
    {
        $this->level = $this->calculateLevel();
        $this->saveQuietly();

        foreach ($this->children as $child) {
            $child->updateHierarchyLevels();
        }
    }

    /**
     * Periksa apakah suatu ID merupakan turunan (descendant) dari node ini.
     * Digunakan untuk mencegah siklus circular dependency saat mengubah parent_id.
     */
    public function isDescendantOf(int|string $targetId): bool
    {
        $current = $this->parent;
        $visited = [];

        while ($current) {
            if ((int)$current->id === (int)$targetId) {
                return true;
            }
            if (in_array($current->id, $visited, true)) {
                break; // cegah loop tak terhingga
            }
            $visited[] = $current->id;
            $current = $current->parent;
        }

        return false;
    }
}
