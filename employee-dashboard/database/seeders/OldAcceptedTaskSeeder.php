<?php

namespace Database\Seeders;

use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OldAcceptedTaskSeeder extends Seeder
{
    /**
     * Seed old tasks for testing the "accepted but shown as late" dashboard bug.
     *
     * - TGS-OLDA-*: accepted, submitted before the deadline -> must NOT show "Terlambat".
     * - TGS-OLDB-*: accepted, submitted after the deadline -> still legitimately late.
     * - TGS-OLDC-*: still in progress, deadline passed -> late.
     */
    public function run(): void
    {
        $staffUsers = User2::whereHas('role', fn ($query) => $query->where('nama_role', 'staff'))
            ->with('karyawan')
            ->get();

        if ($staffUsers->isEmpty()) {
            $this->command->warn('Tidak ada user dengan role staff ditemukan.');

            return;
        }

        $deadline = now()->subDays(5)->setHour(17)->setMinute(0)->setSecond(0);
        $onTime = now()->subDays(7)->setHour(10)->setMinute(0)->setSecond(0);
        $late = now()->subDays(3)->setHour(10)->setMinute(0)->setSecond(0);

        foreach ($staffUsers as $user) {
            $karyawanId = $user->karyawan_id_karyawan;

            if (! $karyawanId) {
                continue;
            }

            $base = [
                'karyawan_id_karyawan' => $karyawanId,
                'deadline' => $deadline,
                'tanggal_dibuat' => now()->subDays(10)->toDateString(),
                'tanggal_update' => now()->toDateString(),
                'deadline_reminder_sent_at' => null,
            ];

            // 1. Accepted and submitted on time: the regression case.
            $onTimeTaskId = 'TGS-OLDA-'.$karyawanId;

            Tugas::withoutEvents(fn () => Tugas::updateOrCreate(
                ['id_tugas' => $onTimeTaskId],
                array_merge($base, [
                    'judul_tugas' => 'Tugas lama sudah di-ACC (submit tepat waktu)',
                    'deskripsi' => 'Tugas lama yang sudah disetujui dan dikumpulkan sebelum tenggat. Tidak boleh tampil sebagai Terlambat.',
                    'progress' => '100',
                    'status' => Tugas::STATUS_SUDAH_ACC,
                    'checklist_selesai' => true,
                ])
            ));

            DB::table('submit_tugas')->updateOrInsert(
                ['id_submit_tugas' => 'SUB-OLDA-'.$karyawanId],
                [
                    'tugas_id_tugas' => $onTimeTaskId,
                    'tugas_karyawan_id_karyawan' => $karyawanId,
                    'file_hasil' => 'tugas_hasil/old-accepted-on-time.pdf',
                    'catatan_karyawan' => 'Dikumpulkan sebelum tenggat.',
                    'tanggal_submit' => $onTime->toDateString(),
                    'status_review' => 'acc',
                    'created_at' => $onTime,
                    'updated_at' => $onTime,
                ]
            );

            // 2. Accepted but submitted late: should still show as late.
            $lateTaskId = 'TGS-OLDB-'.$karyawanId;

            Tugas::withoutEvents(fn () => Tugas::updateOrCreate(
                ['id_tugas' => $lateTaskId],
                array_merge($base, [
                    'judul_tugas' => 'Tugas lama sudah di-ACC (submit terlambat)',
                    'deskripsi' => 'Tugas lama yang disetujui tetapi dikumpulkan setelah tenggat. Ini tetap dianggap Terlambat.',
                    'progress' => '100',
                    'status' => Tugas::STATUS_SUDAH_ACC,
                    'checklist_selesai' => true,
                ])
            ));

            DB::table('submit_tugas')->updateOrInsert(
                ['id_submit_tugas' => 'SUB-OLDB-'.$karyawanId],
                [
                    'tugas_id_tugas' => $lateTaskId,
                    'tugas_karyawan_id_karyawan' => $karyawanId,
                    'file_hasil' => 'tugas_hasil/old-accepted-late.pdf',
                    'catatan_karyawan' => 'Dikumpulkan setelah tenggat.',
                    'tanggal_submit' => $late->toDateString(),
                    'status_review' => 'acc',
                    'created_at' => $late,
                    'updated_at' => $late,
                ]
            );

            // 3. Still in progress with a past deadline: should show as late.
            Tugas::withoutEvents(fn () => Tugas::updateOrCreate(
                ['id_tugas' => 'TGS-OLDC-'.$karyawanId],
                array_merge($base, [
                    'judul_tugas' => 'Tugas lama masih berjalan (lewat tenggat)',
                    'deskripsi' => 'Tugas yang belum dikumpulkan dan tenggatnya sudah lewat. Harus tampil Terlambat.',
                    'progress' => '40',
                    'status' => Tugas::STATUS_BERJALAN,
                    'checklist_selesai' => false,
                ])
            ));
        }

        $this->command->info('Seeded old accepted tasks for '.$staffUsers->count().' staff users.');
    }
}
