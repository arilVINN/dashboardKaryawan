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
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapLabaRugiExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithColumnFormatting
{
    protected ?string $periode;
    protected ?string $mingguKe;
    protected ?string $divisiId;

    public function __construct(?string $periode = null, ?string $mingguKe = null, ?string $divisiId = null)
    {
        $this->periode  = $periode ?: now()->format('Y-m');
        $this->mingguKe = $mingguKe;
        $this->divisiId = $divisiId;
    }

    public function collection(): Collection
    {
        $divisiQuery = Divisi::where('status_aktif', 'Aktif')->orderBy('id_divisi');
        if ($this->divisiId) {
            $divisiQuery->where('id_divisi', $this->divisiId);
        }
        $divisis = $divisiQuery->get();

        $rows = collect();
        $totalPendapatan = 0;
        $totalBiaya      = 0;
        $totalLaba       = 0;

        foreach ($divisis as $divisi) {
            $laporanQuery = KLabaRugi::where('divisi_id_divisi', $divisi->id_divisi)
                ->where('periode', $this->periode)
                ->where('status_verifikasi', '!=', 'perlu_revisi');

            if ($this->mingguKe) {
                $laporanQuery->where('minggu_ke', $this->mingguKe);
            }

            $laporans = $laporanQuery->get();

            $pendapatan = (float) $laporans->sum('total_pendapatan');
            $biaya      = (float) $laporans->sum('total_pengeluaran');
            $laba       = FormatAkuntansi::hitungLaba($pendapatan, $biaya);
            $margin     = FormatAkuntansi::hitungMargin($pendapatan, $biaya);

            $totalPendapatan += $pendapatan;
            $totalBiaya      += $biaya;
            $totalLaba       += $laba;

            $rows->push([
                'kode'       => $divisi->kode_divisi,
                'divisi'     => $divisi->nama_divisi,
                'pendapatan' => $pendapatan,
                'biaya'      => $biaya,
                'laba_rugi'  => $laba,
                'margin'     => $margin / 100, // Desimal agar diformat persen otomatis di Excel
            ]);
        }

        // Baris TOTAL PERUSAHAAN (Sesuai Layout Gambar)
        $marginTotal = FormatAkuntansi::hitungMargin($totalPendapatan, $totalBiaya);
        $rows->push([
            'kode'       => 'TOTAL PERUSAHAAN',
            'divisi'     => '',
            'pendapatan' => $totalPendapatan,
            'biaya'      => $totalBiaya,
            'laba_rugi'  => $totalLaba,
            'margin'     => $marginTotal / 100,
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Divisi',
            'Pendapatan (Rp)',
            'Biaya (Rp)',
            'Laba (Rugi) (Rp)',
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
            'F' => '0.0%;-0.0%;"0.0%"',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        // 1. Style Header (A1:F1) - Background Deep Navy Blue & Font Putih Tebal
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0B3B60'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('C1:F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // 2. Alignment Kolom Data
        $sheet->getStyle("A2:A" . ($lastRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B2:B" . ($lastRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("C2:F" . ($lastRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // 3. Style Baris TOTAL PERUSAHAAN (Baris Terakhir) - Background Deep Navy Blue & Font Putih Tebal
        $sheet->mergeCells("A{$lastRow}:B{$lastRow}");
        $sheet->getStyle("A{$lastRow}:F{$lastRow}")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0B3B60'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle("A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("C{$lastRow}:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }

    public function title(): string
    {
        $labelMinggu = $this->mingguKe ? 'M' . $this->mingguKe : 'Bulanan';
        return 'Laba Rugi ' . $this->periode . ' ' . $labelMinggu;
    }
}
