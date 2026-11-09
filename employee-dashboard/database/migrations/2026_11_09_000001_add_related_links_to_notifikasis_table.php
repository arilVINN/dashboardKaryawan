<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifikasis', function (Blueprint $table) {
            $table->string('tugas_id_tugas', 20)->nullable()->after('isi_notif');
            $table->string('pesan_id_pesan', 20)->nullable()->after('tugas_id_tugas');

            $table->index('tugas_id_tugas');
            $table->index('pesan_id_pesan');
        });
    }

    public function down(): void
    {
        Schema::table('notifikasis', function (Blueprint $table) {
            $table->dropIndex(['tugas_id_tugas']);
            $table->dropIndex(['pesan_id_pesan']);
            $table->dropColumn(['tugas_id_tugas', 'pesan_id_pesan']);
        });
    }
};
