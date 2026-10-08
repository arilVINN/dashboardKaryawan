<?php

namespace App\Queries;

use App\Models\Karyawan;
use Illuminate\Database\Eloquent\Builder;

class KadivStaffQuery
{
    private function __construct(private string $divisiId)
    {
    }

    public static function forDivision(string $divisiId): self
    {
        return new self($divisiId);
    }

    public function apply(array $filters): Builder
    {
        $dir = ($filters['dir'] ?? null) === 'desc' ? 'desc' : 'asc';

        $query = Karyawan::query()
            ->where('divisi_id_divisi', $this->divisiId)
            ->whereHas('user.role', fn ($q) => $q->whereRaw('LOWER(nama_role) = ?', ['staff']))
            ->with('user:id_user,karyawan_id_karyawan,last_login_at')
            ->withCount('tugas');

        $q = $filters['q'] ?? null;
        if ($q !== null && $q !== '') {
            $query->whereRaw('LOWER(nama) LIKE ?', ['%' . mb_strtolower($q) . '%']);
        }

        switch ($filters['sort'] ?? null) {
            case 'tugas':
                $query->orderBy('tugas_count', $dir)->orderBy('nama');
                break;
            case 'login':
                $query->orderByRaw(
                    '(select last_login_at from users2 where users2.karyawan_id_karyawan = karyawans.id_karyawan) ' . $dir
                )->orderBy('nama');
                break;
            default:
                $query->orderBy('nama', $dir);
        }

        return $query;
    }
}
