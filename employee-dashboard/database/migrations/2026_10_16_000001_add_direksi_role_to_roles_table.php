<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pastikan role super-admin "direksi" (Direksi/Pemimpin) tersedia pada
     * database yang sudah berjalan. Fresh install mendapatkannya lewat seeder;
     * migrasi ini membuat `php artisan migrate` tetap menambahkannya tanpa
     * menimpa role yang sudah ada.
     */
    public function up(): void
    {
        if (! DB::table('roles')->where('id_role', 'RL004')->exists()) {
            DB::table('roles')->insert([
                'id_role' => 'RL004',
                'nama_role' => 'direksi',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('id_role', 'RL004')
            ->whereRaw('LOWER(nama_role) = ?', ['direksi'])
            ->delete();
    }
};
