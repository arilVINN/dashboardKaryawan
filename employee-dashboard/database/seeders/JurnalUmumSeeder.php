<?php

namespace Database\Seeders;

use App\Models\CoaAkun;
use App\Models\JurnalDivisi;
use App\Models\JurnalEntry;
use App\Models\KPemasukan;
use App\Models\KPengeluaran;
use App\Services\Finance\JurnalEntryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Throwable;

class JurnalUmumSeeder extends Seeder
{
    public function run(): void
    {
        $akun = [
            ['kode' => '1101', 'nama' => 'Kas', 'kelompok' => 'Aset'],
            ['kode' => '1102', 'nama' => 'Bank', 'kelompok' => 'Aset'],
            ['kode' => '2101', 'nama' => 'Utang Usaha', 'kelompok' => 'Kewajiban'],
            ['kode' => '3101', 'nama' => 'Modal Pemilik', 'kelompok' => 'Ekuitas'],
            ['kode' => '4101', 'nama' => 'Pendapatan Jasa', 'kelompok' => 'Pendapatan'],
            ['kode' => '4102', 'nama' => 'Pendapatan Lain-lain', 'kelompok' => 'Pendapatan'],
            ['kode' => '5101', 'nama' => 'Beban Gaji', 'kelompok' => 'Beban'],
            ['kode' => '5102', 'nama' => 'Beban Operasional', 'kelompok' => 'Beban'],
            ['kode' => '5103', 'nama' => 'Beban Perlengkapan', 'kelompok' => 'Beban'],
        ];

        foreach ($akun as $row) {
            CoaAkun::firstOrCreate(['kode' => $row['kode']], $row + ['aktif' => true]);
        }

        // Kode divisi di Jurnal Umum = sub-perusahaan, bukan departemen.
        $divisi = [
            ['kode' => 'SLI01', 'nama' => 'Silindo Indonesia'],
            ['kode' => 'SLP02', 'nama' => 'Silindo Publishing'],
            ['kode' => 'SLT03', 'nama' => 'Silindo Trading'],
            ['kode' => 'SLM04', 'nama' => 'Silindo Media'],
        ];

        foreach ($divisi as $row) {
            JurnalDivisi::firstOrCreate(['kode' => $row['kode']], $row);
        }

        // Isi awal: jurnal untuk transaksi yang sudah ada sebelumnya.
        // Dijalankan sekali per sumber; aman diulang.
        $service = app(JurnalEntryService::class);

        DB::transaction(function () use ($service) {
            foreach (KPemasukan::query()->cursor() as $pemasukan) {
                try {
                    $service->createFromPemasukan($pemasukan);
                } catch (Throwable $e) {
                    report($e);
                }
            }

            foreach (KPengeluaran::query()->cursor() as $pengeluaran) {
                try {
                    $service->createFromPengeluaran($pengeluaran);
                } catch (Throwable $e) {
                    report($e);
                }
            }
        });

        if (JurnalEntry::count() === 0) {
            $this->command?->warn('Belum ada jurnal: tidak ditemukan transaksi kas masuk/kas keluar untuk di-backfill.');
        }
    }
}
