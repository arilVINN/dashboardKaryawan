<?php

namespace App\Support;

class StatusPalette
{
    /**
     * Shared status colors used by the calendar, dashboard and tables.
     *
     * @var array<string, array{label: string, short: string, bg: string, border: string, dot: string}>
     */
    public const STATUSES = [
        'baru' => [
            'label' => 'Baru',
            'short' => 'Baru',
            'bg' => '#DCEEFE',
            'border' => '#93C5FD',
            'dot' => '#2563EB',
        ],
        'berjalan' => [
            'label' => 'Berjalan',
            'short' => 'Berjalan',
            'bg' => '#D9F5E5',
            'border' => '#86D9AE',
            'dot' => '#059669',
        ],
        'menunggu' => [
            'label' => 'Menunggu persetujuan',
            'short' => 'Menunggu',
            'bg' => '#FEF0C3',
            'border' => '#F5C85C',
            'dot' => '#D97706',
        ],
        'disetujui' => [
            'label' => 'Disetujui',
            'short' => 'Disetujui',
            'bg' => '#D5F3F0',
            'border' => '#7DD3C8',
            'dot' => '#0D9488',
        ],
        'terlambat' => [
            'label' => 'Terlambat',
            'short' => 'Terlambat',
            'bg' => '#FDE0E0',
            'border' => '#F5A3A3',
            'dot' => '#DC2626',
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
