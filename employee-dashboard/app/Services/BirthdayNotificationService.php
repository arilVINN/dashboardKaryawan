<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Models\Notifikasi;
use App\Models\User2;
use Illuminate\Database\Eloquent\Collection;

class BirthdayNotificationService
{
    public function forUser(User2 $user): Collection
    {
        $employees = Karyawan::query()
            ->birthdayToday()
            ->with('divisi')
            ->orderBy('nama')
            ->get();

        if ($employees->isEmpty()) {
            return $employees;
        }

        $names = $employees->pluck('nama')->join(', ');
        $divisions = $employees
            ->map(fn (Karyawan $employee) => $employee->divisi?->nama_divisi)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $divisionLabel = count($divisions) === 1
            ? " dari divisi {$divisions[0]}"
            : '';

        Notifikasi::query()->firstOrCreate(
            [
                'id_notifikasi' => 'BD'.substr(hash('sha256', $user->id_user.'|'.now()->toDateString()), 0, 18),
            ],
            [
                'judul_notifikasi' => 'Selamat Ulang Tahun!',
                'isi_notif' => "Hari ini {$names}{$divisionLabel} berulang tahun.",
                'tanggal_notifikasi' => now()->toDateString(),
                'user_id_user' => $user->id_user,
            ]
        );

        return $employees;
    }
}
