<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Models\User2;

class ContractExpiryReminder
{
    public function forHrd(): array
    {
        $tanggalPengingat = today()->addDays(14);
        $tanggalLabel = $tanggalPengingat->locale('id')->isoFormat('D MMMM Y');

        return Karyawan::query()
            ->where('aktif', true)
            ->whereDate('tanggal_akhir_kontrak', $tanggalPengingat->toDateString())
            ->orderBy('nama')
            ->get(['id_karyawan', 'nama', 'tanggal_akhir_kontrak'])
            ->map(function (Karyawan $karyawan) use ($tanggalLabel) {
                return [
                    'id_notifikasi' => 'kontrak-reminder-'.$karyawan->id_karyawan.'-'.$karyawan->tanggal_akhir_kontrak->toDateString(),
                    'judul_notifikasi' => 'Pengingat Kontrak Kerja',
                    'isi_notif' => "Kontrak kerja {$karyawan->nama} akan berakhir pada {$tanggalLabel} (14 hari lagi).",
                    'created_at' => now()->toISOString(),
                ];
            })
            ->all();
    }

    public function forUser(User2 $user): ?array
    {
        $karyawan = $user->karyawan;

        if (! $karyawan || ! $karyawan->aktif || ! $karyawan->tanggal_akhir_kontrak) {
            return null;
        }

        $tanggalAkhirKontrak = $karyawan->tanggal_akhir_kontrak;

        if (! $tanggalAkhirKontrak->isSameDay(today()->addDays(14))) {
            return null;
        }

        $tanggalLabel = $tanggalAkhirKontrak->locale('id')->isoFormat('D MMMM Y');

        return [
            'id_notifikasi' => 'kontrak-reminder-'.$karyawan->id_karyawan.'-'.$tanggalAkhirKontrak->toDateString(),
            'judul_notifikasi' => 'Pengingat Kontrak Kerja',
            'isi_notif' => "Kontrak kerja Anda akan berakhir pada {$tanggalLabel} (14 hari lagi). Harap hubungi HRD untuk tindak lanjut.",
            'tanggal_notifikasi' => today()->toDateString(),
            'created_at' => now()->toISOString(),
            'is_contract_reminder' => true,
        ];
    }
}
