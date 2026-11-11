<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentEventComment extends Model
{
    protected $table = 'content_event_comments';

    protected $fillable = [
        'content_event_id',
        'user_id_user',
        'body',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(ContentEvent::class, 'content_event_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User2::class, 'user_id_user', 'id_user');
    }
}
