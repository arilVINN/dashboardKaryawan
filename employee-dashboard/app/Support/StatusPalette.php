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
            'bg' => '#DCEEFE',
            'border' => '#93C5FD',
            'dot' => '#2563EB',
            'text' => '#172B36',
        ],
        'berjalan' => [
            'label' => 'Berjalan',
            'short' => 'Berjalan',
            'bg' => '#D9F5E5',
            'border' => '#86D9AE',
            'dot' => '#059669',
            'text' => '#172B36',
        ],
        'menunggu' => [
            'label' => 'Menunggu persetujuan',
            'short' => 'Menunggu',
            'bg' => '#FEF0C3',
            'border' => '#F5C85C',
            'dot' => '#D97706',
            'text' => '#172B36',
        ],
        'disetujui' => [
            'label' => 'Disetujui',
            'short' => 'Disetujui',
            'bg' => '#D5F3F0',
            'border' => '#7DD3C8',
            'dot' => '#0D9488',
            'text' => '#172B36',
        ],
        'terlambat' => [
            'label' => 'Terlambat',
            'short' => 'Terlambat',
            'bg' => '#FDE0E0',
            'border' => '#F5A3A3',
            'dot' => '#DC2626',
            'text' => '#172B36',
        ],
        'aktif' => [
            'label' => 'Aktif',
            'short' => 'Aktif',
            'bg' => '#D9F5E5',
            'border' => '#86D9AE',
            'dot' => '#059669',
            'text' => '#172B36',
        ],
        'tidak_aktif' => [
            'label' => 'Tidak aktif',
            'short' => 'Tidak aktif',
            'bg' => '#F1F5F9',
            'border' => '#CBD5E1',
            'dot' => '#64748B',
            'text' => '#172B36',
        ],
        // Finance workflow states — same tokens, domain-specific labels.
        'ditolak' => [
            'label' => 'Ditolak',
            'short' => 'Ditolak',
            'bg' => '#FDE0E0',
            'border' => '#F5A3A3',
            'dot' => '#DC2626',
            'text' => '#172B36',
        ],
        'dicairkan' => [
            'label' => 'Dicairkan',
            'short' => 'Dicairkan',
            'bg' => '#E2E8EC',
            'border' => '#D5DEE3',
            'dot' => '#4B5563',
            'text' => '#172B36',
        ],
        'terverifikasi' => [
            'label' => 'Terverifikasi',
            'short' => 'Terverifikasi',
            'bg' => '#D5F3F0',
            'border' => '#7DD3C8',
            'dot' => '#0D9488',
            'text' => '#172B36',
        ],
        'perlu_revisi' => [
            'label' => 'Perlu revisi',
            'short' => 'Perlu revisi',
            'bg' => '#FDE0E0',
            'border' => '#F5A3A3',
            'dot' => '#DC2626',
            'text' => '#172B36',
        ],
    ];

    /**
     * Semantic category pills shown next to status badges. Kept separate from
     * STATUSES so editing a workflow colour never changes a category label.
     *
     * @var array<string, array{bg: string, border: string, dot: string, text: string}>
     */
    public const DIVISION_TYPES = [
        'direktur' => ['bg' => '#FEF0C3', 'border' => '#F5C85C', 'dot' => '#D97706', 'text' => '#172B36'],
        'manajemen' => ['bg' => '#EDE9FE', 'border' => '#C4B5FD', 'dot' => '#8B5CF6', 'text' => '#172B36'],
        'divisi' => ['bg' => '#DCEEFE', 'border' => '#93C5FD', 'dot' => '#2563EB', 'text' => '#172B36'],
    ];

    public const MESSAGE_TYPES = [
        'pesan' => ['bg' => '#DCEEFE', 'border' => '#93C5FD', 'dot' => '#2563EB', 'text' => '#172B36'],
        'surat' => ['bg' => '#FEF0C3', 'border' => '#F5C85C', 'dot' => '#D97706', 'text' => '#172B36'],
    ];

    /**
     * Content workflow statuses, separate from the task statuses in STATUSES so
     * changing the content calendar never alters the rest of the app.
     *
     * @var array<string, array{label: string, bg: string, border: string, dot: string, text: string}>
     */
    public const CONTENT = [
        'revisi' => [
            'label' => 'Revisi',
            'bg' => '#F1F5F9',
            'border' => '#CBD5E1',
            'dot' => '#64748B',
            'text' => '#172B36',
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
     * @return array{bg: string, border: string, dot: string, text: string}
     */
    public static function divisionType(?string $type): array
    {
        $key = strtolower(trim((string) $type));

        return self::DIVISION_TYPES[$key] ?? self::DIVISION_TYPES['divisi'];
    }

    /**
     * @return array{bg: string, border: string, dot: string, text: string}
     */
    public static function messageType(?string $type): array
    {
        $key = strtolower(trim((string) $type));

        return self::MESSAGE_TYPES[$key] ?? self::MESSAGE_TYPES['pesan'];
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
     * solid background with the same dark text used inside the form pill.
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
            $status === 'pending' => 'menunggu',
            str_contains($status, 'menunggu') => 'menunggu',
            $status === 'acc' => 'disetujui',
            str_contains($status, 'sudah'),
            str_contains($status, 'disetujui'),
            str_contains($status, 'selesai') => 'disetujui',
            $status === 'ditolak' || $status === 'tolak' => 'ditolak',
            $status === 'dicairkan' => 'dicairkan',
            $status === 'terverifikasi' => 'terverifikasi',
            $status === 'perlu revisi' || $status === 'perlu_revisi' => 'perlu_revisi',
            str_contains($status, 'terlambat'),
            str_contains($status, 'telat') => 'terlambat',
            default => null,
        };
    }

    /**
     * Canonical pill data for a status, with an optional label override.
     * Use this from both Blade and Livewire so colours never get redefined.
     *
     * @return array{label: string, short: string, bg: string, border: string, dot: string, text: string}|null
     */
    public static function pill(?string $status, ?string $label = null, bool $short = false): ?array
    {
        $palette = self::for($status);

        if ($palette === null) {
            return null;
        }

        if ($label !== null) {
            $palette['label'] = $label;
        } elseif ($short) {
            $palette['label'] = $palette['short'];
        }

        return $palette;
    }

    /**
     * @return array{label: string, short: string, bg: string, border: string, dot: string, text: string}|null
     */
    public static function for(?string $status): ?array
    {
        $key = self::normalize($status);

        return $key ? self::STATUSES[$key] : null;
    }
}
