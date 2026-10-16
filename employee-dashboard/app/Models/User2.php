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

    // Hapus method casts() dan gunakan properti $casts ini
    protected $casts = [
        'last_login_at' => 'datetime',
    ];

    /**
     * Role names that hold the highest (super-admin) authority. The
     * "Direksi/Pemimpin" role is stored as `direksi`; `pemimpin` is accepted
     * as an equivalent alias.
     */
    public const SUPERADMIN_ROLES = ['direksi', 'pemimpin'];

    /**
     * Roles inherited by a super-admin account: Direksi/Pemimpin may act on
     * behalf of both HRD and Kadiv.
     */
    public const SUPERADMIN_INHERITED_ROLES = ['hrd', 'kadiv'];

    /**
     * Lower-cased role name of this account, or an empty string when the
     * account has no role attached.
     */
    public function roleName(): string
    {
        return strtolower($this->role?->nama_role ?? '');
    }

    /**
     * Whether this account is the super-admin role (Direksi/Pemimpin).
     */
    public function isSuperAdmin(): bool
    {
        return in_array($this->roleName(), self::SUPERADMIN_ROLES, true);
    }

    /**
     * Case-insensitive role check that also honours the super-admin
     * inheritance: Direksi/Pemimpin satisfies every HRD and Kadiv gate.
     *
     * @param  array<int, string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        $roles = array_map('strtolower', $roles);

        if (in_array($this->roleName(), $roles, true)) {
            return true;
        }

        return $this->isSuperAdmin()
            && array_intersect($roles, self::SUPERADMIN_INHERITED_ROLES) !== [];
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
