<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tugas', 'file_pendukung_name')) {
            Schema::table('tugas', function (Blueprint $table) {
                $table->string('file_pendukung_name')->nullable()->after('file_pendukung');
            });
        }

        if (! Schema::hasColumn('submit_tugas', 'file_hasil_name')) {
            Schema::table('submit_tugas', function (Blueprint $table) {
                $table->string('file_hasil_name')->nullable()->after('file_hasil');
            });
        }

        if (! Schema::hasColumn('submit_tugas', 'file_revisi_name')) {
            Schema::table('submit_tugas', function (Blueprint $table) {
                $table->string('file_revisi_name')->nullable()->after('file_revisi');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('submit_tugas', 'file_revisi_name')) {
            Schema::table('submit_tugas', function (Blueprint $table) {
                $table->dropColumn('file_revisi_name');
            });
        }

        if (Schema::hasColumn('submit_tugas', 'file_hasil_name')) {
            Schema::table('submit_tugas', function (Blueprint $table) {
                $table->dropColumn('file_hasil_name');
            });
        }

        if (Schema::hasColumn('tugas', 'file_pendukung_name')) {
            Schema::table('tugas', function (Blueprint $table) {
                $table->dropColumn('file_pendukung_name');
            });
        }
    }
};
