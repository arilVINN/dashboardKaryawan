<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentEvent extends Model
{
    protected $table = 'content_events';

    protected $fillable = [
        'title',
        'event_date',
        'post_time',
        'content_type',
        'status',
        'description',
        'todos',
        'content_url',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date:Y-m-d',
        'todos' => 'array',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ContentType::class, 'content_type', 'slug');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User2::class, 'created_by', 'id_user');
    }

    public function scopeInRange(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->whereBetween('event_date', [
            $start->toDateString(),
            $end->toDateString(),
        ]);
    }

    /**
     * "10 Nov 2026" — never the numeric locale default.
     */
    public function dateLabel(): string
    {
        return $this->event_date->copy()->locale('id')->translatedFormat('j M Y');
    }

    /**
     * "17:00" — 24 hour, empty when no time was set.
     */
    public function timeLabel(): ?string
    {
        if ($this->post_time === null || $this->post_time === '') {
            return null;
        }

        return substr((string) $this->post_time, 0, 5);
    }
}
