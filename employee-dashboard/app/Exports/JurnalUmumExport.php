<?php

namespace App\Exports;

use App\Models\JurnalEntry;
use App\Models\JurnalLine;
use Carbon\Carbon;
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

class JurnalUmumExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private readonly string $periode,
        private readonly ?int $divisiId = null,
    ) {}

    public function collection(): Collection
    {
        [$tahun, $bulan] = explode('-', $this->periode);
        $tanggalMulai = Carbon::createFromDate((int) $tahun, (int) $bulan, 1)->startOfMonth();
        $tanggalAkhir = $tanggalMulai->copy()->endOfMonth();

        $query = JurnalEntry::query()
            ->with(['lines.account', 'divisi'])
            ->whereBetween('entry_date', [$tanggalMulai->toDateString(), $tanggalAkhir->toDateString()]);

        if ($this->divisiId !== null) {
            $query->where('jurnal_divisi_id', $this->divisiId);
        }

        $rows = collect();
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($query->orderByDesc('entry_date')->orderByDesc('id')->get() as $entry) {
            foreach ($entry->lines->sortByDesc(fn (JurnalLine $line) => $line->debit > 0) as $line) {
                $namaAkun = trim((string) ($line->account?->nama ?? ''));
                $kodeAkun = trim((string) ($line->account?->kode ?? ''));
                $ketLine = trim((string) ($line->keterangan ?? ''));

                if ($namaAkun !== '' && $ketLine !== '' && strcasecmp($namaAkun, $ketLine) !== 0) {
                    $keterangan = "{$namaAkun} - {$ketLine}";
                } elseif ($namaAkun !== '') {
                    $keterangan = $namaAkun;
                } else {
                    $keterangan = $ketLine !== '' ? $ketLine : '-';
                }

                $referensi = $kodeAkun !== '' ? $kodeAkun : ($entry->ref_code ?? '-');

                $rows->push([
                    $entry->entry_date->format('Y-m-d'),
                    $keterangan,
                    $referensi,
                    $entry->divisi?->kode ?? '-',
                    (int) $line->debit,
                    (int) $line->credit,
                ]);

                $totalDebit += (int) $line->debit;
                $totalCredit += (int) $line->credit;
            }
        }

        $rows->push(['TOTAL', '', '', '', $totalDebit, $totalCredit]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Keterangan',
            'Referensi',
            'Kode Divisi',
            'Debit (Rp)',
            'Kredit (Rp)',
        ];
    }

    public function columnFormats(): array
    {
        $accountingFormat = '#,##0;(#,##0);"-"';

        return [
            'E' => $accountingFormat,
            'F' => $accountingFormat,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();
        $totalRow = $lastRow;

        $filterEndRow = max(1, $totalRow - 1);
        $sheet->setAutoFilter("A1:F{$filterEndRow}");

        $sheet->getStyle('A1:F1')->applyFromArray([
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

        if ($totalRow > 2) {
            $sheet->getStyle('E2:F'.($totalRow - 1))
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $sheet->mergeCells("A{$totalRow}:D{$totalRow}");
        $sheet->getStyle("A{$totalRow}:F{$totalRow}")->applyFromArray([
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
        $sheet->getStyle("E{$totalRow}:F{$totalRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }

    public function title(): string
    {
        return 'Jurnal Umum '.$this->periode;
    }
}
