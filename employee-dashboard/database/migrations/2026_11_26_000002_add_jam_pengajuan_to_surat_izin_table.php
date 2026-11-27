<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_izin', function (Blueprint $table) {
            $table->time('jam_pengajuan')->nullable()->after('jenis_izin');
        });
    }

    public function down(): void
    {
        Schema::table('surat_izin', function (Blueprint $table) {
            $table->dropColumn('jam_pengajuan');
        });
    }
};
