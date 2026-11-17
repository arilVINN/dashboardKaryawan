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

    // ─── Keuangan Relations ───────────────────────────────────────────────────

    public function alokasiDibuat()
    {
        return $this->hasMany(KeuanganAldiv::class, 'user_id_user', 'id_user');
    }

    public function pemasukan()
    {
        return $this->hasMany(KPemasukan::class, 'user_id_user', 'id_user');
    }

    public function laporanLabaRugi()
    {
        return $this->hasMany(KLabaRugi::class, 'user_id_user', 'id_user');
    }

    // ─── Role Helpers ─────────────────────────────────────────────────────────

    /**
     * Cek apakah user ini memiliki hak akses keuangan penuh.
     * Kadiv Finance ATAU Direktur.
     */
    public function hasFinanceAccess(): bool
    {
        return $this->isKadivFinance() || $this->isDirektur();
    }

    /**
     * Role names that hold the highest (super-admin) authority.
     * "Direksi" is accepted as an equivalent alias of "Direktur".
     */
    public const SUPERADMIN_ROLES = ['direktur', 'direksi'];

    /**
     * Cek apakah user ini adalah Direktur / Direksi (super-admin).
     */
    public function isDirektur(): bool
    {
        return in_array(
            strtolower($this->role?->nama_role ?? ''),
            self::SUPERADMIN_ROLES,
            true
        );
    }

    /**
     * Cek apakah user ini adalah Kepala Divisi Finance & Accounting.
     * Criteria: role = 'kadiv' AND the employee's division is Finance.
     * Direktur/Direksi inherits full finance access.
     */
    public function isKadivFinance(): bool
    {
        return $this->isDirektur()
            || (strtolower($this->role?->nama_role ?? '') === 'kadiv'
                && ($this->karyawan?->isFinance() ?? false));
    }
}
