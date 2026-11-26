<?php

namespace App\Exports;

use App\Helpers\FormatAkuntansi;
use App\Models\Divisi;
use App\Models\KLabaRugi;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapLabaRugiExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithStyles, WithTitle
{
    protected ?string $periode;

    protected ?string $mingguKe;

    protected ?string $divisiId;

    public function __construct(?string $periode = null, ?string $mingguKe = null, ?string $divisiId = null)
    {
        $this->periode = $periode ?: now()->format('Y-m');
        $this->mingguKe = $mingguKe;
        $this->divisiId = $divisiId;
    }

    public function collection(): Collection
    {
        $divisiQuery = Divisi::query()->unitNonFinance()->aktif()->ordered();
        if ($this->divisiId) {
            $divisiQuery->where('id_divisi', $this->divisiId);
        }
        $divisis = $divisiQuery->get();

        $rows = collect();
        $totalPendapatan = 0;
        $totalBiaya = 0;
        $totalLaba = 0;
        $totalPendOp = 0;
        $totalBebanOp = 0;
        $totalLabaOp = 0;
        $totalPendNonOp = 0;
        $totalBebanNonOp = 0;

        foreach ($divisis as $divisi) {
            $laporanQuery = KLabaRugi::where('divisi_id_divisi', $divisi->id_divisi)
                ->where('periode', $this->periode)
                ->where('status_verifikasi', '!=', 'perlu_revisi');

            if ($this->mingguKe) {
                $laporanQuery->where('minggu_ke', $this->mingguKe);
            }

            $laporans = $laporanQuery->get();

            $pendapatan = (float) $laporans->sum('total_pendapatan');
            $biaya = (float) $laporans->sum('total_pengeluaran');

            $pendOp = (float) $laporans->sum('pendapatan_operasional');
            $pendNonOp = (float) $laporans->sum('pendapatan_non_operasional');
            if ($pendOp == 0 && $pendNonOp == 0 && $pendapatan > 0) {
                $pendOp = $pendapatan;
            }

            $bebanOp = (float) $laporans->sum('beban_operasional');
            $bebanNonOp = (float) $laporans->sum('beban_non_operasional');
            if ($bebanOp == 0 && $bebanNonOp == 0 && $biaya > 0) {
                $bebanOp = $biaya;
            }

            $labaOp = $pendOp - $bebanOp;
            $laba = FormatAkuntansi::hitungLaba($pendapatan, $biaya);
            $margin = FormatAkuntansi::hitungMargin($pendapatan, $biaya);

            $totalPendapatan += $pendapatan;
            $totalBiaya += $biaya;
            $totalLaba += $laba;
            $totalPendOp += $pendOp;
            $totalBebanOp += $bebanOp;
            $totalLabaOp += $labaOp;
            $totalPendNonOp += $pendNonOp;
            $totalBebanNonOp += $bebanNonOp;

            $rows->push([
                'kode' => $divisi->kode_divisi,
                'divisi' => $divisi->nama_divisi,
                'pend_op' => $pendOp,
                'beban_op' => $bebanOp,
                'laba_op' => $labaOp,
                'pend_non_op' => $pendNonOp,
                'beban_non_op' => $bebanNonOp,
                'pendapatan' => $pendapatan,
                'biaya' => $biaya,
                'laba_rugi' => $laba,
                'margin' => $margin / 100, // Desimal agar diformat persen otomatis di Excel
            ]);
        }

        // Baris TOTAL PERUSAHAAN
        $marginTotal = FormatAkuntansi::hitungMargin($totalPendapatan, $totalBiaya);
        $rows->push([
            'kode' => 'TOTAL PERUSAHAAN',
            'divisi' => '',
            'pend_op' => $totalPendOp,
            'beban_op' => $totalBebanOp,
            'laba_op' => $totalLabaOp,
            'pend_non_op' => $totalPendNonOp,
            'beban_non_op' => $totalBebanNonOp,
            'pendapatan' => $totalPendapatan,
            'biaya' => $totalBiaya,
            'laba_rugi' => $totalLaba,
            'margin' => $marginTotal / 100,
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Divisi',
            'Pend. Operasional (Rp)',
            'Beban Operasional / OPEX (Rp)',
            'Laba Operasional (Rp)',
            'Pend. Non-Op (Rp)',
            'Beban Non-Op (Rp)',
            'Total Pendapatan (Rp)',
            'Total Biaya (Rp)',
            'Laba (Rugi) Bersih (Rp)',
            'Margin (%)',
        ];
    }

    public function columnFormats(): array
    {
        // Format Akuntansi Excel: Positif #,##0 | Negatif (#,##0) | Nol '-'
        $accountingFormat = '#,##0;(#,##0);"-"';

        return [
            'C' => $accountingFormat,
            'D' => $accountingFormat,
            'E' => $accountingFormat,
            'F' => $accountingFormat,
            'G' => $accountingFormat,
            'H' => $accountingFormat,
            'I' => $accountingFormat,
            'J' => $accountingFormat,
            'K' => '0.0%;-0.0%;"0.0%"',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        // 1. Style Header (A1:K1) - Background Deep Navy Blue & Font Putih Tebal
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0B3B60'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('C1:K1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // 2. Alignment Kolom Data
        $sheet->getStyle('A2:A'.($lastRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B2:B'.($lastRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('C2:K'.($lastRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // 3. Style Baris TOTAL PERUSAHAAN (Baris Terakhir) - Background Deep Navy Blue & Font Putih Tebal
        $sheet->mergeCells("A{$lastRow}:B{$lastRow}");
        $sheet->getStyle("A{$lastRow}:K{$lastRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0B3B60'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle("A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("C{$lastRow}:K{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }

    public function title(): string
    {
        $labelMinggu = $this->mingguKe ? 'M'.$this->mingguKe : 'Bulanan';

        return 'Laba Rugi '.$this->periode.' '.$labelMinggu;
    }
}
