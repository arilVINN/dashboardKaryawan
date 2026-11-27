<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_izin', function (Blueprint $table) {
            $table->string('hrd_signature_path')->nullable()->after('hrd_id_user');
        });
    }

    public function down(): void
    {
        Schema::table('surat_izin', function (Blueprint $table) {
            $table->dropColumn('hrd_signature_path');
        });
    }
};
