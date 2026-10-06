<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * App uses string user IDs (e.g. users2.id_user = "US001").
     * Laravel's default sessions.user_id is bigint, so any web
     * (session) login fails on pgsql with SQLSTATE[22P02].
     * Sessions data is disposable, so rebuild the column as string.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE VARCHAR(255) USING user_id::text');
            return;
        }

        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
        Schema::table('sessions', function (Blueprint $table) {
            $table->string('user_id', 255)->nullable()->index();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE BIGINT USING CASE WHEN user_id ~ \'^[0-9]+$\' THEN user_id::bigint ELSE NULL END');
            return;
        }

        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
        Schema::table('sessions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->index();
        });
    }
};
