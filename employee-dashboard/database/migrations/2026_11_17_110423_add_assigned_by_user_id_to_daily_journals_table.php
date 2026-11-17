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
        Schema::table('daily_journals', function (Blueprint $table) {
            $table->string('assigned_by_user_id', 50)->nullable()->after('karyawan_id_karyawan');
            $table->index('assigned_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_journals', function (Blueprint $table) {
            $table->dropIndex(['assigned_by_user_id']);
            $table->dropColumn('assigned_by_user_id');
        });
    }
};
