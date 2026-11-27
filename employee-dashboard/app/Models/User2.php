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
        'must_change_password',
        'session_version',
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
        'must_change_password' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id_role', 'id_role');
    }

    /**
     * Additional (secondary) roles granted to this user. The primary role
     * remains on users2.role_id_role and is kept for backward compatibility.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'id_user', 'id_role');
    }

    /**
     * All role names assigned to this user: primary role + additional roles.
     *
     * @return array<int, string>
     */
    public function daftarRole(): array
    {
        $nama = $this->relationLoaded('role')
            ? strtolower((string) $this->role?->nama_role)
            : strtolower((string) ($this->role()->value('nama_role') ?? ''));

        $daftar = array_filter([$nama]);

        if ($this->relationLoaded('roles')) {
            $tambahan = $this->roles
                ->map(fn (Role $role) => strtolower((string) $role->nama_role));
        } else {
            $tambahan = $this->roles()->pluck('nama_role')
                ->map(fn ($role) => strtolower((string) $role));
        }

        return array_values(array_unique(array_merge($daftar, $tambahan->all())));
    }

    /**
     * True when the user holds the given role as primary or additional.
     */
    public function hasRole(string $namaRole): bool
    {
        return in_array(strtolower(trim($namaRole)), $this->daftarRole(), true);
    }

    /**
     * True when the user holds any of the given roles.
     *
     * @param  array<int, string>  $namaRoles
     */
    public function hasAnyRole(array $namaRoles): bool
    {
        $yangDicari = array_map(fn ($nama) => strtolower(trim((string) $nama)), $namaRoles);

        return (bool) array_intersect($yangDicari, $this->daftarRole());
    }

    /**
     * Only the additional roles (excludes the primary role).
     *
     * @return array<int, string>
     */
    public function peranTambahan(): array
    {
        $utama = strtolower((string) ($this->relationLoaded('role') ? $this->role?->nama_role : $this->role()->value('nama_role')));

        return array_values(array_filter(
            $this->daftarRole(),
            fn (string $nama) => $nama !== $utama,
        ));
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
     *
     * "superadmin" is a real role (created only via the superadmin:create
     * command). It inherits every Direktur-level bypass, but unlike Direktur
     * it is allowed to write data (see DenySuperAdmin).
     */
    public const SUPERADMIN_ROLES = ['direktur', 'direksi', 'superadmin'];

    /**
     * Cek apakah user ini memiliki role superadmin (CLI-managed account).
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * Cek apakah user ini adalah Direktur / Direksi / Superadmin
     * (tingkat akses tertinggi).
     */
    public function isDirektur(): bool
    {
        return (bool) array_intersect($this->daftarRole(), self::SUPERADMIN_ROLES);
    }

    /**
     * Cek apakah user ini adalah Kepala Divisi Finance & Accounting.
     * Criteria: role = 'kadiv' AND the employee's division is Finance.
     * Direktur/Direksi inherits finance *read* access (for monitoring), but
     * the finance policies block them from approving/rejecting/verifying.
     */
    public function isKadivFinance(): bool
    {
        return $this->isDirektur()
            || ($this->hasRole('kadiv') && ($this->karyawan?->isFinance() ?? false));
    }
}
