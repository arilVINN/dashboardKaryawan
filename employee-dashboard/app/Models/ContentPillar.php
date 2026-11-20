<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentPillar extends Model
{
    protected $table = 'content_pillars';

    protected $fillable = [
        'slug',
        'name',
        'color',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Used by the cards and form when an event has no pillar yet.
     *
     * @return array{name: string, color: string}
     */
    public static function fallback(): array
    {
        return [
            'name' => 'Tanpa pillar',
            'color' => '#94A3B8',
        ];
    }

    /**
     * Pillars in display order.
     *
     * @return array<int, array{slug: string, name: string, color: string}>
     */
    public static function ordered(): array
    {
        return static::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['slug', 'name', 'color'])
            ->all();
    }
}
