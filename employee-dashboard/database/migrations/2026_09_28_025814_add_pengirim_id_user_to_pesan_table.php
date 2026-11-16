<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesans', function (Blueprint $table) {
            $table->string('pengirim_id_user', 20);

            $table->foreign('pengirim_id_user')
                ->references('id_user')
                ->on('users2')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pesans', function (Blueprint $table) {
            $table->dropForeign(['pengirim_id_user']);
            $table->dropColumn('pengirim_id_user');
        });
    }
};
