<?php

namespace App\Queries;

use App\Models\Pesan;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StaffPesanQuery
{
    private const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    private function __construct(private User2 $staff) {}

    public static function forStaff(User2 $staff): self
    {
        return new self($staff);
    }

    /**
     * Unified staff message list: task threads merged with direct messages.
     *
     * @param  array{jenis?: string, q?: string, sort?: string, dir?: string}  $filters
     * @return Collection<int, array{jenis: string, link_id: string, judul: string, pengirim: string, tanggal_raw: ?string, tanggal: string}>
     */
    public function unified(array $filters): Collection
    {
        $items = $this->threads()->concat($this->directMessages());

        return $this->sort($this->filter($items, $filters), $filters);
    }

    /**
     * Task threads owned by the staff that already have at least one pesan.
     */
    private function threads(): Collection
    {
        return Tugas::with('latestPesan.pengirim')
            ->withCount('pesans')
            ->where('karyawan_id_karyawan', $this->staff->karyawan_id_karyawan)
            ->get()
            ->filter(fn (Tugas $tugas): bool => $tugas->latestPesan !== null)
            ->map(fn (Tugas $tugas): array => [
                'jenis' => 'tugas',
                'link_id' => $tugas->id_tugas,
                'judul' => 'Tugas: '.$tugas->judul_tugas,
                'pengirim' => $tugas->latestPesan->pengirim?->username ?? '-',
                'tanggal_raw' => $tugas->latestPesan->tanggal_pesan,
                'tanggal' => $this->formatDate($tugas->latestPesan->tanggal_pesan),
            ])
            ->values();
    }

    /**
     * Direct messages addressed to the staff (not attached to a tugas).
     */
    private function directMessages(): Collection
    {
        return Pesan::with('pengirim.karyawan')
            ->where('penerima_id_user', $this->staff->id_user)
            ->whereNull('tugas_id_tugas')
            ->get()
            ->map(fn (Pesan $pesan): array => [
                'jenis' => 'langsung',
                'link_id' => $pesan->id_pesan,
                'judul' => $pesan->judul_pesan ?: 'Pesan Langsung',
                'pengirim' => $pesan->pengirim?->username ?? '-',
                'tanggal_raw' => $pesan->tanggal_pesan,
                'tanggal' => $this->formatDate($pesan->tanggal_pesan),
            ])
            ->values();
    }

    private function filter(Collection $items, array $filters): Collection
    {
        $jenis = $filters['jenis'] ?? null;
        if (in_array($jenis, ['tugas', 'langsung'], true)) {
            $items = $items->where('jenis', $jenis)->values();
        }

        $q = $filters['q'] ?? null;
        if ($q !== null && $q !== '') {
            // The old client-side search filtered on the raw (untrimmed) value:
            // only an empty string skips the filter, whitespace-only still filters.
            $needle = mb_strtolower((string) $q);
            $items = $items->filter(function (array $item) use ($needle): bool {
                return str_contains(mb_strtolower((string) $item['judul']), $needle)
                    || str_contains(mb_strtolower((string) $item['pengirim']), $needle);
            })->values();
        }

        return $items;
    }

    private function sort(Collection $items, array $filters): Collection
    {
        $sort = in_array($filters['sort'] ?? null, ['tanggal', 'judul', 'pengirim'], true)
            ? $filters['sort']
            : 'tanggal';
        $dir = ($filters['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $items
            ->sort(function (array $a, array $b) use ($sort, $dir): int {
                if ($sort === 'tanggal') {
                    $at = strtotime((string) ($a['tanggal_raw'] ?? '')) ?: 0;
                    $bt = strtotime((string) ($b['tanggal_raw'] ?? '')) ?: 0;
                    $result = $at <=> $bt;
                } else {
                    $result = strcmp(
                        mb_strtolower((string) ($a[$sort] ?? '')),
                        mb_strtolower((string) ($b[$sort] ?? ''))
                    );
                }

                return $dir === 'asc' ? $result : -$result;
            })
            ->values();
    }

    private function formatDate(?string $date): string
    {
        if ($date === null || $date === '') {
            return '-';
        }

        $parsed = Carbon::parse($date);

        return $parsed->day.' '.self::MONTHS[$parsed->month - 1].' '.$parsed->year;
    }
}
