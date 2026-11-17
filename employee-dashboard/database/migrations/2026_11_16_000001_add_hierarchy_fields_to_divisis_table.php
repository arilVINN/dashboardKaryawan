<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Divisions are no longer a flat list. Each row is now one node in the
     * company hierarchy:
     *
     *   level 1 - Direktur Utama (single person)
     *   level 2 - Manajemen (grouping node, no direct members)
     *   level 3 - HR, Finance & Accounting, and the operational divisions
     *
     * `kapasitas` keeps the "single person" rule in the data itself:
     *   null = unlimited, 1 = exactly one member, 0 = grouping (no members).
     */
    public function up(): void
    {
        Schema::table('divisis', function (Blueprint $table) {
            $table->string('parent_id', 20)->nullable()->after('id_divisi');
            $table->string('tipe', 20)->default('divisi')->after('nama_divisi');
            $table->unsignedTinyInteger('level')->default(3)->after('tipe');
            $table->unsignedSmallInteger('kapasitas')->nullable()->after('level');
            $table->unsignedInteger('urutan')->default(0)->after('kapasitas');

            $table->index('parent_id');
            $table->foreign('parent_id')
                ->references('id_divisi')
                ->on('divisis')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('divisis', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id']);
            $table->dropColumn(['parent_id', 'tipe', 'level', 'kapasitas', 'urutan']);
        });
    }
};
