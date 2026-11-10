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

    /**
     * Lighter badge palette used by the public content calendar. The status
     * keys and labels still come from STATUSES, so there is one status list.
     *
     * @var array<string, array{bg: string, border: string, dot: string, text: string}>
     */
    public const CONTENT = [
        'baru' => [
            'bg' => '#DCEEFE',
            'border' => '#93C5FD',
            'dot' => '#2563EB',
            'text' => '#172B36',
        ],
        'berjalan' => [
            'bg' => '#D9F5E5',
            'border' => '#86D9AE',
            'dot' => '#059669',
            'text' => '#172B36',
        ],
        'menunggu' => [
            'bg' => '#FEF0C3',
            'border' => '#F5C85C',
            'dot' => '#D97706',
            'text' => '#172B36',
        ],
        'disetujui' => [
            'bg' => '#D5F3F0',
            'border' => '#7DD3C8',
            'dot' => '#0D9488',
            'text' => '#172B36',
        ],
    ];

    public static function all(): array
    {
        return self::STATUSES;
    }

    /**
     * Statuses that can be chosen for a content event. "Terlambat" is derived
     * for tasks, not a selectable content status.
     *
     * @return array<string, string> normalized key => label
     */
    public static function selectable(): array
    {
        return [
            'baru' => self::STATUSES['baru']['label'],
            'berjalan' => self::STATUSES['berjalan']['label'],
            'menunggu' => self::STATUSES['menunggu']['label'],
            'disetujui' => self::STATUSES['disetujui']['label'],
        ];
    }

    /**
     * @return array{label: string, bg: string, border: string, dot: string, text: string}|null
     */
    public static function content(?string $status): ?array
    {
        $key = self::normalize($status);

        if (! $key || ! isset(self::CONTENT[$key])) {
            return null;
        }

        return self::CONTENT[$key] + ['label' => self::STATUSES[$key]['label']];
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
