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
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'division_id',
        'status',
        'color',
        'pic_user_id',
        'description',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'division_id', 'id_divisi');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User2::class, 'pic_user_id', 'id_user');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User2::class, 'created_by', 'id_user');
    }

    /**
     * Events whose inclusive date span overlaps the given range.
     */
    public function scopeOverlapping(Builder $query, Carbon $rangeStart, Carbon $rangeEnd): Builder
    {
        return $query
            ->whereDate('start_date', '<=', $rangeEnd->toDateString())
            ->where(function (Builder $inner) use ($rangeStart) {
                $inner->whereDate('end_date', '>=', $rangeStart->toDateString())
                    ->orWhere(function (Builder $singleDay) use ($rangeStart) {
                        $singleDay->whereNull('end_date')
                            ->whereDate('start_date', '>=', $rangeStart->toDateString());
                    });
            });
    }

    public function effectiveEndDate(): Carbon
    {
        return ($this->end_date ?? $this->start_date)->copy();
    }

    public function isMultiDay(): bool
    {
        return $this->end_date !== null && $this->end_date->greaterThan($this->start_date);
    }

    public function startTimeLabel(): ?string
    {
        return $this->start_time ? substr((string) $this->start_time, 0, 5) : null;
    }

    public function endTimeLabel(): ?string
    {
        return $this->end_time ? substr((string) $this->end_time, 0, 5) : null;
    }

    public function timeLabel(): ?string
    {
        $start = $this->startTimeLabel();

        if ($start === null) {
            return null;
        }

        $end = $this->endTimeLabel();

        return $end !== null ? "{$start}–{$end}" : $start;
    }
}
