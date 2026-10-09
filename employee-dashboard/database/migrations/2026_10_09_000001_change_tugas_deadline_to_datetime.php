<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tugas', function (Blueprint $table): void {
            $table->dateTime('deadline')->nullable()->change();
        });

        DB::table('tugas')
            ->whereNotNull('deadline')
            ->orderBy('id_tugas')
            ->chunk(500, function ($tasks): void {
                foreach ($tasks as $task) {
                    DB::table('tugas')
                        ->where('id_tugas', $task->id_tugas)
                        ->update([
                            'deadline' => Carbon::parse($task->deadline)
                                ->endOfDay()
                                ->format('Y-m-d H:i:s'),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table): void {
            $table->date('deadline')->nullable()->change();
        });
    }
};
