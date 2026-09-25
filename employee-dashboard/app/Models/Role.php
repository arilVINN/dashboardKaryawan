<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'id_role';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_role',
        'nama_role',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id_role', 'id_role');
    }
}