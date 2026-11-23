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
        $this->call(CoaSeeder::class);

        // Kode divisi di Jurnal Umum = sub-perusahaan, bukan departemen.
        $divisi = [
            ['kode' => 'D01', 'nama' => 'PT.SILINDO'],
            ['kode' => 'D02', 'nama' => 'Buku'],
            ['kode' => 'D03', 'nama' => 'Jurnal'],
            ['kode' => 'D04', 'nama' => 'MARKOM'],
            ['kode' => 'D05', 'nama' => 'Academic Events'],
            ['kode' => 'D06', 'nama' => 'SMK'],
            ['kode' => 'D07', 'nama' => 'KOS'],
            ['kode' => 'D08', 'nama' => 'Business Development'],
            ['kode' => 'D09', 'nama' => 'Kelas Kerjasama'],
            ['kode' => 'D10', 'nama' => 'Meira English Institute'],
            ['kode' => 'D11', 'nama' => 'Citra Komunika'],
            ['kode' => 'D12', 'nama' => 'Public Relations'],
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