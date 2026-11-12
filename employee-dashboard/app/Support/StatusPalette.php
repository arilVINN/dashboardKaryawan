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
     * Content workflow statuses, separate from the task statuses in STATUSES so
     * changing the content calendar never alters the rest of the app.
     *
     * @var array<string, array{label: string, bg: string, border: string, dot: string, text: string}>
     */
    public const CONTENT = [
        'plan' => [
            'label' => 'Plan',
            'bg' => '#FFFFFF',
            'border' => '#94A3B8',
            'dot' => '#64748B',
            'text' => '#172B36',
        ],
        'revisi' => [
            'label' => 'Revisi',
            'bg' => '#4B5563',
            'border' => '#6B7280',
            'dot' => '#F3F4F6',
            'text' => '#FFFFFF',
        ],
        'cancel' => [
            'label' => 'Cancel',
            'bg' => '#FEE2E2',
            'border' => '#FCA5A5',
            'dot' => '#B91C1C',
            'text' => '#172B36',
        ],
        'approved' => [
            'label' => 'Approved',
            'bg' => '#DCEEFE',
            'border' => '#93C5FD',
            'dot' => '#2563EB',
            'text' => '#172B36',
        ],
        'process' => [
            'label' => 'On process',
            'bg' => '#FEF0C3',
            'border' => '#F5C85C',
            'dot' => '#D97706',
            'text' => '#172B36',
        ],
        'publish' => [
            'label' => 'Publish',
            'bg' => '#D9F5E5',
            'border' => '#86D9AE',
            'dot' => '#059669',
            'text' => '#172B36',
        ],
    ];

    /**
     * Content event statuses, in the order they appear in the form and filter.
     *
     * @return array<string, string>
     */
    public static function contentStatuses(): array
    {
        return array_map(fn (array $status) => $status['label'], self::CONTENT);
    }

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
        $key = self::contentKey($status);

        return $key ? self::CONTENT[$key] : null;
    }

    /**
     * Calendar-card pill variant. It uses the form pill's border colour as the
     * solid background and the same text colour as the form pill.
     *
     * @return array{label: string, bg: string, border: string, dot: string, text: string}|null
     */
    public static function contentSolid(?string $status): ?array
    {
        $key = self::contentKey($status);

        if (! $key) {
            return null;
        }

        $base = self::CONTENT[$key];

        return [
            'label' => $base['label'],
            'bg' => $base['border'],
            'border' => $base['border'],
            'dot' => $base['dot'],
            'text' => $base['text'],
        ];
    }

    /**
     * Normalize a raw content status to its CONTENT palette key.
     */
    public static function contentKey(?string $status): ?string
    {
        $key = strtolower(trim((string) $status));

        if ($key === '') {
            return null;
        }

        $key = match ($key) {
            'baru', 'berjalan' => 'process',
            'menunggu' => 'revisi',
            'disetujui' => 'approved',
            'terlambat' => 'cancel',
            'on process', 'on-process', 'onprocess', 'on_progress', 'on progress' => 'process',
            default => $key,
        };

        return isset(self::CONTENT[$key]) ? $key : null;
    }

    public static function normalize(?string $status): ?string
    {
        $status = strtolower(trim((string) $status));

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
