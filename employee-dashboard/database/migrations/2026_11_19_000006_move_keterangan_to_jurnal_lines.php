<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Keterangan kini menjadi milik tiap baris (menggambarkan COA yang dipilih),
     * bukan catatan untuk seluruh transaksi.
     */
    public function up(): void
    {
        Schema::table('jurnal_lines', function (Blueprint $table) {
            $table->text('keterangan')->nullable()->after('account_id');
        });

        DB::table('jurnal_entries')
            ->whereNotNull('keterangan')
            ->orderBy('id')
            ->get()
            ->each(function ($entry) {
                DB::table('jurnal_lines')
                    ->where('jurnal_entry_id', $entry->id)
                    ->update(['keterangan' => $entry->keterangan]);
            });

        Schema::table('jurnal_entries', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_entries', function (Blueprint $table) {
            $table->text('keterangan')->nullable()->after('jurnal_divisi_id');
        });

        DB::table('jurnal_entries')
            ->orderBy('id')
            ->get()
            ->each(function ($entry) {
                $firstLine = DB::table('jurnal_lines')
                    ->where('jurnal_entry_id', $entry->id)
                    ->orderBy('sort_order')
                    ->first();

                if ($firstLine) {
                    DB::table('jurnal_entries')
                        ->where('id', $entry->id)
                        ->update(['keterangan' => $firstLine->keterangan]);
                }
            });

        Schema::table('jurnal_lines', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};
