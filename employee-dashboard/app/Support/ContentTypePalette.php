<?php

namespace App\Support;

class ContentTypePalette
{
    /**
     * Tag colours, applied by position in the managed list. Types beyond the
     * list cycle back to the first colour.
     *
     * @var array<int, array{fill: string, border: string, text: string}>
     */
    public const COLORS = [
        ['fill' => '#E0EAFD', 'border' => '#9DB9F5', 'text' => '#172B36'],
        ['fill' => '#FEF3E0', 'border' => '#F3C581', 'text' => '#172B36'],
        ['fill' => '#FCE7F1', 'border' => '#F3A8C9', 'text' => '#172B36'],
        ['fill' => '#EEE8FD', 'border' => '#C3ABF7', 'text' => '#172B36'],
        ['fill' => '#DDF5F2', 'border' => '#87D5CC', 'text' => '#172B36'],
    ];

    /**
     * @return array{fill: string, border: string, text: string}
     */
    public static function for(int $index): array
    {
        $colors = self::COLORS;

        return $colors[$index % count($colors)];
    }
}
