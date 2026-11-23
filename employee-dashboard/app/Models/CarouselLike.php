<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarouselLike extends Model
{
    protected $table = 'carousel_likes';

    protected $fillable = [
        'carousel_id',
        'user_id_user',
    ];

    public function carousel()
    {
        return $this->belongsTo(Carousel::class, 'carousel_id');
    }

    public function user()
    {
        return $this->belongsTo(User2::class, 'user_id_user', 'id_user');
    }
}
