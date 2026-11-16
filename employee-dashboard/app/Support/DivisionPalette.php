<?php

namespace App\Support;

use App\Models\Divisi;

class DivisionPalette
{
    /**
     * Ordered palette. A division keeps its color by its position in the
     * ascending id_divisi order; the list cycles when there are more divisions.
     *
     * @var array<int, array{fill: string, border: string, dot: string}>
     */
    public const PALETTE = [
        ['fill' => '#DDF5F2', 'border' => '#87D5CC', 'dot' => '#0D9488'],
        ['fill' => '#EEE8FD', 'border' => '#C3ABF7', 'dot' => '#7C3AED'],
        ['fill' => '#FEF3E0', 'border' => '#F3C581', 'dot' => '#D97706'],
        ['fill' => '#E0EAFD', 'border' => '#9DB9F5', 'dot' => '#2563EB'],
        ['fill' => '#DAF5E8', 'border' => '#86D8B5', 'dot' => '#059669'],
        ['fill' => '#FCE7F1', 'border' => '#F3A8C9', 'dot' => '#DB2777'],
    ];

    /**
     * Events without a division are company-wide ("Umum").
     */
    public const UMUM = [
        'fill' => '#EEF1F5',
        'border' => '#CBD2DB',
        'dot' => '#64748B',
    ];

    /**
     * Read-only national holiday entries.
     */
    public const HOLIDAY = [
        'fill' => '#F4F1E6',
        'border' => '#E0D6A8',
        'dot' => '#A39161',
        'text' => '#5C4D14',
    ];

    /**
     * Selectable event colors. The keys are stored on content_events.color.
     *
     * @var array<string, array{label: string, fill: string, border: string, dot: string}>
     */
    public const EVENT_COLORS = [
        'teal' => ['label' => 'Teal', 'fill' => '#DDF5F2', 'border' => '#87D5CC', 'dot' => '#0D9488'],
        'blue' => ['label' => 'Biru', 'fill' => '#E0EAFD', 'border' => '#9DB9F5', 'dot' => '#2563EB'],
        'light_purple' => ['label' => 'Ungu muda', 'fill' => '#EEE8FD', 'border' => '#C3ABF7', 'dot' => '#7C3AED'],
        'gray' => ['label' => 'Abu-abu', 'fill' => '#EEF1F5', 'border' => '#CBD2DB', 'dot' => '#64748B'],
        'light_green' => ['label' => 'Hijau muda', 'fill' => '#DAF5E8', 'border' => '#86D8B5', 'dot' => '#059669'],
        'orange' => ['label' => 'Oranye', 'fill' => '#FEF3E0', 'border' => '#F3C581', 'dot' => '#D97706'],
        'red' => ['label' => 'Merah', 'fill' => '#FEE2E2', 'border' => '#FCA5A5', 'dot' => '#DC2626'],
    ];

    /**
     * Colors for one stored event color, or null when the event should keep
     * following its division color.
     *
     * @return array{fill: string, border: string, dot: string}|null
     */
    public static function event(?string $color): ?array
    {
        if ($color === null || $color === '' || ! isset(self::EVENT_COLORS[$color])) {
            return null;
        }

        $palette = self::EVENT_COLORS[$color];
        unset($palette['label']);

        return $palette;
    }

    /**
     * Pick the closest palette key for an event that has no stored color yet.
     */
    public static function eventColorForDivision(?string $divisionId, ?array $map = null): string
    {
        $current = self::for($divisionId, $map);

        foreach (self::EVENT_COLORS as $key => $palette) {
            if ($palette['dot'] === $current['dot']) {
                return $key;
            }
        }

        return 'gray';
    }

    /**
     * Build the division id => color map once per call. The component passes
     * this map to the view so pills, legend, filter and drawer stay in sync.
     *
     * @return array<string, array{fill: string, border: string, dot: string}>
     */
    public static function map(): array
    {
        $ids = Divisi::query()
            ->orderBy('id_divisi')
            ->pluck('id_divisi');

        $map = [];

        foreach ($ids as $index => $id) {
            $map[$id] = self::PALETTE[$index % count(self::PALETTE)];
        }

        return $map;
    }

    /**
     * @param  array<string, array{fill: string, border: string, dot: string}>|null  $map
     * @return array{fill: string, border: string, dot: string}
     */
    public static function for(?string $divisionId, ?array $map = null): array
    {
        if ($divisionId === null || $divisionId === '') {
            return self::UMUM;
        }

        $map ??= self::map();

        return $map[$divisionId] ?? self::UMUM;
    }
}
