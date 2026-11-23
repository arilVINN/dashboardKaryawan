<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table): void {
            $table->foreignId('jabatan_id')
                ->nullable()
                ->after('divisi_id_divisi')
                ->constrained('jabatan')
                ->nullOnDelete();
        });

        Schema::table('users2', function (Blueprint $table): void {
            $table->boolean('must_change_password')->default(false)->after('password');
            // Bumped whenever a password is reset so existing sessions can be
            // invalidated without touching the shared sessions table.
            $table->string('session_version', 36)->default('1')->after('must_change_password');
        });
    }

    public function down(): void
    {
        Schema::table('users2', function (Blueprint $table): void {
            $table->dropColumn(['must_change_password', 'session_version']);
        });

        Schema::table('karyawans', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('jabatan_id');
        });
    }
};
