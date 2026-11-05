<?php

namespace App\Support;

class StatusPalette
{
    /**
     * Shared high-contrast status colors used by the calendar, dashboard and tables.
     *
     * @var array<string, array{label: string, short: string, bg: string, border: string, dot: string, text: string}>
     */
    public const STATUSES = [
        'baru' => [
            'label' => 'Baru',
            'short' => 'Baru',
            'bg' => '#C0E7FF',
            'border' => '#3B82F6',
            'dot' => '#1D4ED8',
            'text' => '#1D4ED8',
        ],
        'berjalan' => [
            'label' => 'Berjalan',
            'short' => 'Berjalan',
            'bg' => '#D1FAE5',
            'border' => '#10B981',
            'dot' => '#047857',
            'text' => '#047857',
        ],
        'menunggu' => [
            'label' => 'Menunggu persetujuan',
            'short' => 'Menunggu',
            'bg' => '#FEF3C7',
            'border' => '#F59E0B',
            'dot' => '#B45309',
            'text' => '#B45309',
        ],
        'disetujui' => [
            'label' => 'Disetujui',
            'short' => 'Disetujui',
            'bg' => '#047857',
            'border' => '#065F46',
            'dot' => '#D1FAE5',
            'text' => '#FFFFFF',
        ],
        'terlambat' => [
            'label' => 'Terlambat',
            'short' => 'Terlambat',
            'bg' => '#FEE2E2',
            'border' => '#EF4444',
            'dot' => '#B91C1C',
            'text' => '#B91C1C',
        ],
        'aktif' => [
            'label' => 'Aktif',
            'short' => 'Aktif',
            'bg' => '#D1FAE5',
            'border' => '#10B981',
            'dot' => '#047857',
            'text' => '#047857',
        ],
        'tidak_aktif' => [
            'label' => 'Tidak aktif',
            'short' => 'Tidak aktif',
            'bg' => '#F1F5F9',
            'border' => '#94A3B8',
            'dot' => '#475569',
            'text' => '#475569',
        ],
    ];

    public static function all(): array
    {
        return self::STATUSES;
    }

    public static function normalize(?string $status): ?string
    {        $status = strtolower(trim((string) $status));

        if ($status === '') {
            return null;
        }

        return match (true) {
            str_contains($status, 'tidak') && str_contains($status, 'aktif') => 'tidak_aktif',
            $status === 'aktif' => 'aktif',
            $status === 'baru' => 'baru',
            $status === 'berjalan' => 'berjalan',
            str_contains($status, 'menunggu') => 'menunggu',
            str_contains($status, 'sudah'),
            str_contains($status, 'disetujui'),
            str_contains($status, 'selesai') => 'disetujui',
            str_contains($status, 'terlambat'),
            str_contains($status, 'telat') => 'terlambat',
            default => null,
        };
    }

    /**
     * @return array{label: string, short: string, bg: string, border: string, dot: string}|null
     */
    public static function for(?string $status): ?array
    {
        $key = self::normalize($status);

        return $key ? self::STATUSES[$key] : null;
    }
}
