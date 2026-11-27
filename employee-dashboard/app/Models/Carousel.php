<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carousel extends Model
{
    protected $table = 'carousels';

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'category',
        'date',
        'image',
        'initial_likes',
        'user_id_user',
        'divisi_id_divisi',
    ];

    protected $casts = [
        'date' => 'date',
        'initial_likes' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User2::class, 'user_id_user', 'id_user');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id_divisi', 'id_divisi');
    }

    public function likes()
    {
        return $this->hasMany(CarouselLike::class, 'carousel_id');
    }

    public function isLikedBy(?User2 $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->likes()->where('user_id_user', $user->id_user)->exists();
    }

    public function getTotalLikesAttribute(): int
    {
        return ($this->initial_likes ?? 0) + $this->likes()->count();
    }
}
