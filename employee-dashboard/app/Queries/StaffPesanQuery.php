<?php

namespace App\Queries;

use App\Models\Pesan;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class StaffPesanQuery
{
    private function __construct(private User2 $staff) {}

    public static function forStaff(User2 $staff): self
    {
        return new self($staff);
    }

    /**
     * Unified staff message list: task threads merged with direct messages.
     *
     * @param  array{arah?: string, q?: string, sort?: string, dir?: string, dari?: string, sampai?: string}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function unified(array $filters): Collection
    {
        $items = $this->threads()->concat($this->directMessages());

        return $this->sort($this->filter($items, $filters), $filters);
    }

    /**
     * Header summary for the whole inbox (filters intentionally ignored):
     * total messages and how many are still unread.
     *
     * @return array{total: int, belum_dibaca: int}
     */
    public function ringkasan(): array
    {
        $items = $this->unified([]);

        return [
            'total' => $items->count(),
            'belum_dibaca' => $items->where('unread', true)->count(),
        ];
    }

    /**
     * Task threads owned by the staff that already have at least one pesan.
     */
    private function threads(): Collection
    {
        return Tugas::with(['latestPesan.pengirim.karyawan', 'latestPesan.penerima.karyawan'])
            ->withCount('pesans')
            ->where('karyawan_id_karyawan', $this->staff->karyawan_id_karyawan)
            ->get()
            ->filter(fn (Tugas $tugas): bool => $tugas->latestPesan !== null)
            ->map(function (Tugas $tugas): array {
                $pesan = $tugas->latestPesan;
                $isIncoming = $pesan->pengirim_id_user !== $this->staff->id_user;

                return $this->row([
                    'jenis' => 'tugas',
                    'link_id' => $tugas->id_tugas,
                    'pesan_id' => $pesan->id_pesan,
                    'judul' => 'Tugas: '.$tugas->judul_tugas,
                    'preview' => $pesan->deskripsi,
                    'arah' => $isIncoming ? 'masuk' : 'keluar',
                    'pengirim' => $pesan->pengirim?->username ?? '-',
                    'penerima' => $pesan->penerima?->karyawan?->nama
                        ?? $pesan->penerima?->username ?? '-',
                    'kontak' => $isIncoming ? $pesan->pengirim : $pesan->penerima,
                    'incoming' => $isIncoming,
                    'tanggal' => $pesan->tanggal_pesan,
                    'waktu' => $pesan->created_at,
                    'dibaca_at' => $pesan->dibaca_at,
                ]);
            })
            ->values();
    }

    /**
     * Direct messages to or from the staff (not attached to a tugas).
     */
    private function directMessages(): Collection
    {
        return Pesan::with(['pengirim.karyawan', 'penerima.karyawan'])
            ->whereNull('tugas_id_tugas')
            ->where(function ($query): void {
                $query->where('penerima_id_user', $this->staff->id_user)
                    ->orWhere('pengirim_id_user', $this->staff->id_user);
            })
            ->get()
            ->map(function (Pesan $pesan): array {
                $isIncoming = $pesan->pengirim_id_user !== $this->staff->id_user;

                return $this->row([
                    'jenis' => 'langsung',
                    'link_id' => $pesan->id_pesan,
                    'pesan_id' => $pesan->id_pesan,
                    'judul' => $pesan->judul_pesan ?: 'Pesan Langsung',
                    'preview' => $pesan->deskripsi,
                    'arah' => $isIncoming ? 'masuk' : 'keluar',
                    'pengirim' => $pesan->pengirim?->username ?? '-',
                    'penerima' => $pesan->penerima?->karyawan?->nama
                        ?? $pesan->penerima?->username ?? '-',
                    'kontak' => $isIncoming ? $pesan->pengirim : $pesan->penerima,
                    'incoming' => $isIncoming,
                    'tanggal' => $pesan->tanggal_pesan,
                    'waktu' => $pesan->created_at,
                    'dibaca_at' => $pesan->dibaca_at,
                ]);
            })
            ->values();
    }

    /**
     * Normalise the internal row into the shape consumed by the list view.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function row(array $data): array
    {
        $contact = $data['kontak'];
        $incoming = (bool) $data['incoming'];
        $tanggal = $data['tanggal'];
        $waktu = $data['waktu'] ?? null;

        return [
            'jenis' => $data['jenis'],
            'link_id' => $data['link_id'],
            'pesan_id' => $data['pesan_id'],
            'judul' => $data['judul'],
            'preview' => Str::limit((string) ($data['preview'] ?? ''), 90),
            'arah' => $data['arah'],
            'pengirim' => $data['pengirim'],
            'penerima' => $data['penerima'],
            'kontak_peran' => $incoming ? 'Dari' : 'Kepada',
            'kontak_nama' => $contact?->karyawan?->nama ?? $contact?->username ?? 'Pengguna tidak tersedia',
            'kontak_username' => $contact?->username,
            'unread' => $incoming && empty($data['dibaca_at']),
            'tanggal_raw' => $tanggal,
            'tanggal' => Pesan::formatTanggalJam($tanggal, $waktu),
            'tanggal_relatif' => Pesan::formatTanggalRelatif($tanggal, $waktu),
            'tanggal_lengkap' => Pesan::formatTanggalJam($tanggal, $waktu),
        ];
    }

    private function filter(Collection $items, array $filters): Collection
    {
        $arah = $filters['arah'] ?? null;
        if (in_array($arah, ['masuk', 'keluar'], true)) {
            $items = $items->where('arah', $arah)->values();
        }

        $dari = $filters['dari'] ?? null;
        if (! empty($dari)) {
            $items = $items->filter(fn (array $item): bool => ($item['tanggal_raw'] ?? '') >= $dari)->values();
        }

        $sampai = $filters['sampai'] ?? null;
        if (! empty($sampai)) {
            $items = $items->filter(fn (array $item): bool => ($item['tanggal_raw'] ?? '') <= $sampai)->values();
        }

        $q = $filters['q'] ?? null;
        if ($q !== null && $q !== '') {
            // The old client-side search filtered on the raw (untrimmed) value:
            // only an empty string skips the filter, whitespace-only still filters.
            $needle = mb_strtolower((string) $q);
            $items = $items->filter(function (array $item) use ($needle): bool {
                return str_contains(mb_strtolower((string) $item['judul']), $needle)
                    || str_contains(mb_strtolower((string) $item['pengirim']), $needle)
                    || str_contains(mb_strtolower((string) ($item['kontak_nama'] ?? '')), $needle)
                    || str_contains(mb_strtolower((string) ($item['kontak_username'] ?? '')), $needle);
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
}
