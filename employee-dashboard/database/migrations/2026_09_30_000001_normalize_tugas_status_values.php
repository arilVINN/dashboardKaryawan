<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normalisasi nilai `tugas.status` lama ke kosakata status baru (5 status UI/UX).
     *
     * Pemetaan:
     *   pending                → baru
     *   submitted              → menunggu di-acc
     *   selesai / acc          → sudah di-acc
     *   jeda / kendala / revisi → berjalan
     *
     * "telat" tidak pernah disimpan (status turunan dari deadline).
     * Perbandingan case-insensitive agar nilai lama berganda huruf ikut terpetakan.
     */
    private const MAP = [
        'pending' => 'baru',
        'submitted' => 'menunggu di-acc',
        'selesai' => 'sudah di-acc',
        'acc' => 'sudah di-acc',
        'jeda' => 'berjalan',
        'kendala' => 'berjalan',
        'revisi' => 'berjalan',
    ];

    public function up(): void
    {
        foreach (self::MAP as $old => $new) {
            DB::table('tugas')
                ->whereRaw('LOWER(status) = ?', [$old])
                ->update(['status' => $new]);
        }
    }

    /**
     * Best-effort: hanya membalikkan pemetaan yang tidak ambigu.
     * Baris "berjalan" tidak dikembalikan — "berjalan" sendiri adalah nilai lama
     * yang sah, sehingga tidak bisa dibedakan dari hasil map jeda/kendala/revisi.
     */
    public function down(): void
    {
        $reverse = [
            'baru' => 'pending',
            'menunggu di-acc' => 'submitted',
            'sudah di-acc' => 'selesai',
        ];

        foreach ($reverse as $new => $old) {
            DB::table('tugas')
                ->where('status', $new)
                ->update(['status' => $old]);
        }
    }
};
