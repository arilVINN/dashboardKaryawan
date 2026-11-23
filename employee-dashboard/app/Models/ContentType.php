<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentType extends Model
{
    protected $table = 'content_types';

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
     * Types in display order, with the colour stored on each row.
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
