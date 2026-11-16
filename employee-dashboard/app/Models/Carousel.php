<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carousel extends Model
{
    protected $table = 'carousels';

    protected $fillable = [
        'title',
        'category',
        'date',
        'image',
        'user_id_user',
        'divisi_id_divisi',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User2::class, 'user_id_user', 'id_user');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id_divisi', 'id_divisi');
    }
}
