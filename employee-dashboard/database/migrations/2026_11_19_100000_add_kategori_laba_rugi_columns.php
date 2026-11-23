<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom detail kategori Laba/Rugi:
     *
     * PENDAPATAN:
     *   - pendapatan_operasional   (penjualan buku, jasa, dsb.)
     *   - pendapatan_non_operasional (bunga bank, penjualan aset, dsb.)
     *
     * BEBAN:
     *   - beban_operasional        (gaji, sewa, utilitas, ATK, dsb.)
     *   - beban_non_operasional    (bunga pinjaman, denda, kerugian selisih kurs, dsb.)
     *
     * Kolom total_pendapatan dan total_pengeluaran tetap dipertahankan
     * sebagai computed sum agar backward-compatible.
     */
    public function up(): void
    {
        Schema::table('klaba_rugi', function (Blueprint $table) {
            // Pendapatan detail
            $table->decimal('pendapatan_operasional', 15, 2)->default(0)->after('total_pengeluaran');
            $table->decimal('pendapatan_non_operasional', 15, 2)->default(0)->after('pendapatan_operasional');

            // Beban detail
            $table->decimal('beban_operasional', 15, 2)->default(0)->after('pendapatan_non_operasional');
            $table->decimal('beban_non_operasional', 15, 2)->default(0)->after('beban_operasional');

            // Laba operasional (pendapatan_op - beban_op) untuk tampilan terpisah
            $table->decimal('laba_operasional', 15, 2)->default(0)->after('beban_non_operasional');
        });
    }

    public function down(): void
    {
        Schema::table('klaba_rugi', function (Blueprint $table) {
            $table->dropColumn([
                'pendapatan_operasional',
                'pendapatan_non_operasional',
                'beban_operasional',
                'beban_non_operasional',
                'laba_operasional',
            ]);
        });
    }
};
