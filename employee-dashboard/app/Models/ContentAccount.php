<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentAccount extends Model
{
    protected $table = 'content_accounts';

    protected $fillable = [
        'slug',
        'name',
        'handle',
        'platform',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(ContentEvent::class, 'content_account_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Resolve a slug to an active account, falling back to the first account so
     * a stale or missing ?account= value still opens a usable calendar.
     */
    public static function resolve(?string $slug): ?self
    {
        $query = static::query()->active()->ordered();

        if ($slug !== null && $slug !== '') {
            $match = (clone $query)->where('slug', $slug)->first();

            if ($match) {
                return $match;
            }
        }

        return $query->first();
    }
}
