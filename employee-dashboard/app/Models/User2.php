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
     * Cek apakah user ini adalah Direktur / Direksi (Super-Admin).
     */
    public function isDirektur(): bool
    {
        $roleName = strtolower($this->role?->nama_role ?? '');
        return $roleName === 'direktur' || $roleName === 'direksi';
    }

    /**
     * Cek apakah user ini memiliki hak akses keuangan penuh.
     * Kadiv Finance ATAU Direktur.
     */
    public function hasFinanceAccess(): bool
    {
        return $this->isKadivFinance() || $this->isDirektur();
    }

    /**
     * Cek apakah user ini adalah Kepala Divisi Finance.
     * Kriteria: role = 'kadiv' DAN divisi kode = 'FIN'
     */
    public function isKadivFinance(): bool
    {
        return (strtolower($this->role?->nama_role ?? '') === 'kadiv'
            && strtoupper($this->karyawan?->divisi?->kode_divisi ?? '') === 'FIN')
            || $this->isDirektur();
    }
}
