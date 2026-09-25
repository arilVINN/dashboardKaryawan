<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User2 extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $table = 'users2'; // Sesuaikan jika nama tabel Anda berbeda (misal 'users2')

    protected $primaryKey = 'id_user';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_user',
        'username',
        'password',
        'role_id_role',
        'karyawan_id_karyawan',
    ];

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
}