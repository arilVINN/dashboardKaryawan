<?php

namespace App\Queries;

use App\Models\Pesan;
use App\Models\User2;
use Illuminate\Database\Eloquent\Builder;

class HrdPesanQuery
{
    private function __construct(private User2 $hrd) {}

    public static function forHrd(User2 $hrd): self
    {
        return new self($hrd);
    }

    public function apply(array $filters): Builder
    {
        $query = Pesan::with(['pengirim.karyawan', 'penerima.karyawan']);

        $q = $filters['q'] ?? null;
        if ($this->hasFilter($q)) {
            // LOWER(...) so the search is case-insensitive on Postgres too.
            $kata = '%'.mb_strtolower((string) $q).'%';
            $query->where(function (Builder $query) use ($kata): void {
                $query->whereRaw('LOWER(judul_pesan) LIKE ?', [$kata])
                    ->orWhereHas('pengirim', fn (Builder $q2) => $this->contactName($q2, $kata))
                    ->orWhereHas('penerima', fn (Builder $q2) => $this->contactName($q2, $kata));
            });
        }

        $arah = $filters['arah'] ?? null;
        if ($arah === 'masuk') {
            $query->where('penerima_id_user', $this->hrd->id_user);
        } elseif ($arah === 'keluar') {
            $query->where('pengirim_id_user', $this->hrd->id_user);
        }

        $tipe = $filters['tipe'] ?? null;
        if (in_array($tipe, ['pesan', 'surat'], true)) {
            $query->where('tipe', $tipe);
        }

        $status = $filters['status'] ?? null;
        if (in_array($status, ['belum_dibaca', 'dibaca'], true)) {
            $query->where('status', $status);
        }

        $dir = ($filters['dir'] ?? null) === 'asc' ? 'asc' : 'desc';

        switch ($filters['sort'] ?? null) {
            case 'judul':
                $query->orderBy('judul_pesan', $dir)->orderBy('id_pesan');
                break;
            case 'pengirim':
                $query->orderByRaw(
                    "(select username from users2 where users2.id_user = pesans.pengirim_id_user) {$dir}"
                )->orderBy('id_pesan');
                break;
            case 'tanggal':
                $query->orderBy('tanggal_pesan', $dir)->orderBy('created_at', $dir);
                break;
            default:
                $query->orderByDesc('tanggal_pesan')->orderByDesc('created_at');
        }

        return $query;
    }

    private function contactName(Builder $query, string $kata): void
    {
        $query->whereRaw('LOWER(username) LIKE ?', [$kata])
            ->orWhereHas('karyawan', fn (Builder $k) => $k->whereRaw('LOWER(nama) LIKE ?', [$kata]));
    }

    /**
     * Mirrors Request::filled(): a string filter is absent when it is null or blank.
     */
    private function hasFilter(mixed $value): bool
    {
        return $value !== null && trim((string) $value) !== '';
    }
}
