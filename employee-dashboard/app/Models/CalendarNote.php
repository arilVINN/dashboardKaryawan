<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarNote extends Model
{
    use HasUuids;

    protected $table = 'calendar_notes';

    protected $fillable = [
        'user_id_user',
        'tanggal',
        'judul',
        'detail',
        'warna',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
    ];

    /**
     * Warna yang tersedia untuk note.
     * Key = nilai di DB, Value = label display
     */
    public const WARNA_OPTIONS = [
        'slate'  => ['bg' => '#F1F5F9', 'border' => '#94A3B8', 'text' => '#475569', 'dot' => '#64748B'],
        'blue'   => ['bg' => '#DBEAFE', 'border' => '#3B82F6', 'text' => '#1D4ED8', 'dot' => '#3B82F6'],
        'green'  => ['bg' => '#D1FAE5', 'border' => '#10B981', 'text' => '#065F46', 'dot' => '#10B981'],
        'amber'  => ['bg' => '#FEF3C7', 'border' => '#F59E0B', 'text' => '#92400E', 'dot' => '#F59E0B'],
        'red'    => ['bg' => '#FEE2E2', 'border' => '#EF4444', 'text' => '#991B1B', 'dot' => '#EF4444'],
        'purple' => ['bg' => '#EDE9FE', 'border' => '#8B5CF6', 'text' => '#5B21B6', 'dot' => '#8B5CF6'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User2::class, 'user_id_user', 'id_user');
    }
}
