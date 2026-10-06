<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('submit_tugas', 'file_revisi')) {
            Schema::table('submit_tugas', function (Blueprint $table): void {
                $table->string('file_revisi')->nullable();
            });
        }

        if (! Schema::hasColumn('submit_tugas', 'deadline_revisi')) {
            Schema::table('submit_tugas', function (Blueprint $table): void {
                $table->dateTime('deadline_revisi')->nullable();
            });
        }
    }

    public function down(): void
    {
        $columns = [];
        if (Schema::hasColumn('submit_tugas', 'file_revisi')) {
            $columns[] = 'file_revisi';
        }
        if (Schema::hasColumn('submit_tugas', 'deadline_revisi')) {
            $columns[] = 'deadline_revisi';
        }

        if ($columns !== []) {
            Schema::table('submit_tugas', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
