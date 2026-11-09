<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id_user',
        'aksi',
        'target_tipe',
        'target_id',
        'detail',
    ];

    protected $casts = [
        'detail' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User2::class, 'user_id_user', 'id_user');
    }

    /**
     * Helper untuk merekam log audit secara praktis.
     */
    public static function catat(?string $userId, string $aksi, string $targetTipe, ?string $targetId, array $detail = []): self
    {
        return self::create([
            'user_id_user' => $userId,
            'aksi'         => $aksi,
            'target_tipe'  => $targetTipe,
            'target_id'    => $targetId,
            'detail'       => $detail,
        ]);
    }
}
