<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentType extends Model
{
    protected $table = 'content_types';

    protected $fillable = [
        'slug',
        'name',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Types in display order. The tag colours cycle through a fixed palette,
     * so new rows can be added without touching code.
     */
    public static function ordered(): array
    {
        return static::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['slug', 'name'])
            ->all();
    }
}
