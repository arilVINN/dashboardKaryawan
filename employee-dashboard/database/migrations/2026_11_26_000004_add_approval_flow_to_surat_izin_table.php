<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_izin', function (Blueprint $table) {
            $table->string('tahap', 20)->default('hrd')->after('status');
            $table->string('kadiv_id_karyawan', 20)->nullable()->after('tahap');
            $table->string('kadiv_signature_path')->nullable()->after('kadiv_id_karyawan');
            $table->timestamp('kadiv_approved_at')->nullable()->after('kadiv_signature_path');
            $table->timestamp('kadiv_rejected_at')->nullable()->after('kadiv_approved_at');
            $table->string('hrd_id_user', 20)->nullable()->after('kadiv_rejected_at');
            $table->timestamp('hrd_approved_at')->nullable()->after('hrd_id_user');
            $table->timestamp('hrd_rejected_at')->nullable()->after('hrd_approved_at');
        });

        // Existing records were processed under the old HRD-only flow.
        DB::table('surat_izin')
            ->whereIn('status', ['Disetujui', 'Ditolak'])
            ->update(['tahap' => 'selesai']);

        DB::table('surat_izin')
            ->where('status', 'Pending')
            ->update(['tahap' => 'hrd']);
    }

    public function down(): void
    {
        Schema::table('surat_izin', function (Blueprint $table) {
            $table->dropColumn([
                'tahap',
                'kadiv_id_karyawan',
                'kadiv_signature_path',
                'kadiv_approved_at',
                'kadiv_rejected_at',
                'hrd_id_user',
                'hrd_approved_at',
                'hrd_rejected_at',
            ]);
        });
    }
};
