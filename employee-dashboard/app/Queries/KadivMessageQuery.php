<?php

namespace App\Queries;

use App\Models\Pesan;
use App\Models\User2;
use Illuminate\Database\Eloquent\Builder;

class KadivMessageQuery
{
    private function __construct(private User2 $kadiv)
    {
    }

    public static function forUser(User2 $kadiv): self
    {
        return new self($kadiv);
    }

    public function apply(array $filters): Builder
    {
        $query = Pesan::query()
            ->with(['pengirim.karyawan', 'penerima.karyawan', 'tugas'])
            ->where(function ($q): void {
                $q->where('pengirim_id_user', $this->kadiv->id_user)
                    ->orWhere('penerima_id_user', $this->kadiv->id_user);
            });

        $tipe = $filters['tipe'] ?? null;
        if (in_array($tipe, ['pesan', 'surat'], true)) {
            $query->where('tipe', $tipe);
        }

        $arah = $filters['arah'] ?? null;
        if ($arah === 'masuk') {
            $query->where('penerima_id_user', $this->kadiv->id_user);
        } elseif ($arah === 'keluar') {
            $query->where('pengirim_id_user', $this->kadiv->id_user);
        }

        $q = $filters['q'] ?? null;
        if ($q !== null && $q !== '') {
            $kata = mb_strtolower($q);
            $query->where(function ($w) use ($kata): void {
                $w->whereRaw('LOWER(judul_pesan) LIKE ?', ['%' . $kata . '%'])
                    ->orWhereRaw('LOWER(deskripsi) LIKE ?', ['%' . $kata . '%']);
            });
        }

        $dir = ($filters['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        switch ($filters['sort'] ?? null) {
            case 'tanggal':
                $query->orderBy('tanggal_pesan', $dir)->orderBy('id_pesan', $dir);
                break;
            case 'judul':
                $query->orderBy('judul_pesan', $dir)->orderBy('id_pesan');
                break;
            case 'pengirim':
                $query->orderByRaw(
                    '(select k.nama from karyawans k inner join users2 u on u.karyawan_id_karyawan = k.id_karyawan where u.id_user = pesans.pengirim_id_user) ' . $dir
                );
                break;
            default:
                $query->latest('created_at');
        }

        return $query;
    }
}
