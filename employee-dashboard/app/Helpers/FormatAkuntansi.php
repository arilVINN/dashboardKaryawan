<?php

namespace App\Helpers;

class FormatAkuntansi
{
    /**
     * Format nominal rupiah dengan notasi akuntansi standar.
     * Nilai negatif / defisit ditampilkan dalam tanda kurung: (Rp 10.000.000)
     * Bukan menggunakan tanda minus.
     */
    public static function formatRupiah(float|int|null $nominal, bool $withPrefix = true): string
    {
        $nominal = (float) ($nominal ?? 0);
        $prefix  = $withPrefix ? 'Rp ' : '';

        if ($nominal < 0) {
            return '(' . $prefix . number_format(abs($nominal), 0, ',', '.') . ')';
        }

        return $prefix . number_format($nominal, 0, ',', '.');
    }

    /**
     * Format angka tabel laporan keuangan:
     * - Nilai 0 ditampilkan sebagai '-'
     * - Nilai negatif ditampilkan dalam kurung: (45.039.830)
     * - Nilai positif: 600.000
     */
    public static function formatAngkaLaporan(float|int|null $nominal): string
    {
        $nominal = (float) ($nominal ?? 0);

        if ($nominal == 0.0) {
            return '-';
        }

        if ($nominal < 0) {
            return '(' . number_format(abs($nominal), 0, ',', '.') . ')';
        }

        return number_format($nominal, 0, ',', '.');
    }

    /**
     * Format persentase margin dengan notasi akuntansi.
     * Nilai negatif ditampilkan dalam tanda kurung: (50,0%)
     */
    public static function formatMargin(float|int|null $margin): string
    {
        $margin = (float) ($margin ?? 0);

        if ($margin < 0) {
            return '(' . number_format(abs($margin), 1, ',', '.') . '%)';
        }

        return number_format($margin, 1, ',', '.') . '%';
    }

    /**
     * Hitung margin laba sesuai aturan khusus:
     * - 0% jika salah satu komponen (pendapatan ATAU pengeluaran) adalah 0 / tidak ada
     * - Hitung normal hanya jika KEDUA komponen > 0
     */
    public static function hitungMargin(float $pendapatan, float $pengeluaran): float
    {
        if ($pendapatan <= 0 || $pengeluaran <= 0) {
            return 0.0;
        }

        $laba = $pendapatan - $pengeluaran;

        return round(($laba / $pendapatan) * 100, 2);
    }

    /**
     * Hitung laba bersih.
     */
    public static function hitungLaba(float $pendapatan, float $pengeluaran): float
    {
        return $pendapatan - $pengeluaran;
    }

    /**
     * Generate ID dengan prefix dan padding, berdasarkan jumlah existing records.
     * Contoh: 'KAD' + count + 1 -> 'KAD00001'
     */
    public static function generateId(string $prefix, int $count): string
    {
        return $prefix . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    }
}
