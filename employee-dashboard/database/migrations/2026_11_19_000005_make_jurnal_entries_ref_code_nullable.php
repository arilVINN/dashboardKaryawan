<?php

use App\Models\JurnalEntry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jurnal manual dan jurnal balik tidak memakai ref_code; hanya transaksi
     * otomatis (kas masuk/kas keluar/pencairan) yang memilikinya.
     */
    public function up(): void
    {
        Schema::table('jurnal_entries', function (Blueprint $table) {
            $table->string('ref_code', 30)->nullable()->change();
        });

        DB::table('jurnal_entries')
            ->whereNull('source_type')
            ->orWhere('source_type', JurnalEntry::class)
            ->update(['ref_code' => null]);
    }

    public function down(): void
    {
        DB::table('jurnal_entries')
            ->whereNull('ref_code')
            ->orderBy('id')
            ->get()
            ->each(function ($row) {
                DB::table('jurnal_entries')
                    ->where('id', $row->id)
                    ->update(['ref_code' => 'LEG'.str_pad((string) $row->id, 6, '0', STR_PAD_LEFT)]);
            });

        Schema::table('jurnal_entries', function (Blueprint $table) {
            $table->string('ref_code', 30)->nullable(false)->change();
        });
    }
};
