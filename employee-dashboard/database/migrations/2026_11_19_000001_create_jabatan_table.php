<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jabatan titles now belong to a division. The same name may exist in
     * different divisions, so uniqueness is scoped to the division and the
     * normalised (lower-cased, trimmed) name.
     */
    public function up(): void
    {
        Schema::create('jabatan', function (Blueprint $table): void {
            $table->id();
            $table->string('division_id', 20);
            $table->string('nama', 80);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->foreign('division_id')
                ->references('id_divisi')
                ->on('divisis')
                ->onDelete('cascade');
        });

        DB::statement('CREATE UNIQUE INDEX jabatan_division_nama_unique ON jabatan (division_id, lower(trim(nama)))');
    }

    public function down(): void
    {
        Schema::dropIfExists('jabatan');
    }
};
