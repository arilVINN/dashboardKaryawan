<?php

namespace App\Support;

/**
 * Shared colour rules for the user-managed content options. Each option stores
 * one accent hex; the form pill, card tag and text contrast are derived here so
 * both "jenis konten" and "konten pillar" render consistently.
 */
class OptionColor
{
    public const FALLBACK = '#94A3B8';

    /** Preserve the exact tints of the original seeded content types. */
    private const LEGACY_FILLS = [
        '#9DB9F5' => '#E0EAFD',
        '#F3C581' => '#FEF3E0',
        '#F3A8C9' => '#FCE7F1',
        '#C3ABF7' => '#EEE8FD',
        '#87D5CC' => '#DDF5F2',
    ];

    /** Curated swatches offered in the create modal. */
    public const SWATCHES = [
        '#2563EB' => 'Biru',
        '#059669' => 'Hijau',
        '#7C3AED' => 'Ungu',
        '#EA580C' => 'Oranye',
        '#DC2626' => 'Merah',
        '#0891B2' => 'Sian',
        '#DB2777' => 'Pink',
        '#D97706' => 'Amber',
        '#4F46E5' => 'Indigo',
        '#475569' => 'Abu',
    ];

    public static function normalize(?string $color): string
    {
        $color = strtoupper(trim((string) $color));

        return preg_match('/^#[0-9A-F]{6}$/', $color) === 1 ? $color : self::FALLBACK;
    }

    /**
     * @return array{color: string, fill: string, border: string, text: string, fill_text: string}
     */
    public static function for(?string $color): array
    {
        $hex = self::normalize($color);

        return [
            'color' => $hex,
            'fill' => self::LEGACY_FILLS[$hex] ?? self::tint($hex),
            'border' => $hex,
            'text' => self::isDark($hex) ? '#FFFFFF' : '#172B36',
            'fill_text' => '#172B36',
        ];
    }

    /** Mix the accent with white so the form pill stays light. */
    private static function tint(string $hex): string
    {
        [$r, $g, $b] = self::rgb($hex);

        $mix = fn (int $channel) => (int) round($channel + (255 - $channel) * 0.82);

        return sprintf('#%02X%02X%02X', $mix($r), $mix($g), $mix($b));
    }

    /** Readable text on the solid accent (WCAG-style relative luminance). */
    private static function isDark(string $hex): bool
    {
        [$r, $g, $b] = self::rgb($hex);

        $channels = array_map(
            fn (int $channel) => $channel / 255,
            [$r, $g, $b]
        );

        $linear = array_map(
            fn (float $channel) => $channel <= 0.03928
                ? $channel / 12.92
                : (($channel + 0.055) / 1.055) ** 2.4,
            $channels
        );

        $luminance = 0.2126 * $linear[0] + 0.7152 * $linear[1] + 0.0722 * $linear[2];

        return $luminance < 0.35;
    }

    /** @return array{0: int, 1: int, 2: int} */
    private static function rgb(string $hex): array
    {
        return [
            hexdec(substr($hex, 1, 2)),
            hexdec(substr($hex, 3, 2)),
            hexdec(substr($hex, 5, 2)),
        ];
    }
}
