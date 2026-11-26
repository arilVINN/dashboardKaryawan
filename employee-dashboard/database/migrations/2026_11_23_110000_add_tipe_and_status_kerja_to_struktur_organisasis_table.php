<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('struktur_organisasis', function (Blueprint $table) {
            if (! Schema::hasColumn('struktur_organisasis', 'tipe_node')) {
                $table->string('tipe_node', 30)->default('orang')->after('jabatan');
            }
            if (! Schema::hasColumn('struktur_organisasis', 'status_kerja')) {
                $table->string('status_kerja', 30)->default('tetap')->after('tipe_node');
            }
            if (! Schema::hasColumn('struktur_organisasis', 'garis_putus')) {
                $table->boolean('garis_putus')->default(false)->after('status_kerja');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('struktur_organisasis', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('struktur_organisasis', 'tipe_node')) {
                $cols[] = 'tipe_node';
            }
            if (Schema::hasColumn('struktur_organisasis', 'status_kerja')) {
                $cols[] = 'status_kerja';
            }
            if (Schema::hasColumn('struktur_organisasis', 'garis_putus')) {
                $cols[] = 'garis_putus';
            }
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
