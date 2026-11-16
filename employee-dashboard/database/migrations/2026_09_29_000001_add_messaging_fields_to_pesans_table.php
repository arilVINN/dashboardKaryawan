<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesans', function (Blueprint $table): void {
            $table->string('penerima_id_user', 20)->nullable()->after('pengirim_id_user');
            $table->string('tipe', 20)->default('pesan')->after('deskripsi');
            $table->text('link_lampiran')->nullable()->after('tipe');
            $table->string('file_lampiran')->nullable()->after('link_lampiran');

            $table->foreign('penerima_id_user')
                ->references('id_user')
                ->on('users2')
                ->restrictOnDelete();
        });

        Schema::table('pesans', function (Blueprint $table): void {
            $table->string('tugas_id_tugas', 20)->nullable()->change();
            $table->string('tugas_karyawan_id_karyawan', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pesans', function (Blueprint $table): void {
            $table->dropForeign(['penerima_id_user']);
            $table->dropColumn(['penerima_id_user', 'tipe', 'link_lampiran', 'file_lampiran']);
        });

        Schema::table('pesans', function (Blueprint $table): void {
            $table->string('tugas_id_tugas', 20)->nullable(false)->change();
            $table->string('tugas_karyawan_id_karyawan', 20)->nullable(false)->change();
        });
    }
};
