<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User2 extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users2';

    protected $primaryKey = 'id_user';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_user',
        'username',
        'password',
        'role_id_role',
        'karyawan_id_karyawan',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id_role', 'id_role');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id_karyawan', 'id_karyawan');
    }

    public function notifikasis()
    {
        return $this->hasMany(Notifikasi::class, 'user_id_user', 'id_user');
    }

    public function pesanDikirim()
    {
        return $this->hasMany(
            Pesan::class,
            'pengirim_id_user',
            'id_user'
        );
    }

    public function pesanDiterima()
    {
        return $this->hasMany(
            Pesan::class,
            'penerima_id_user',
            'id_user'
        );
    }
}
