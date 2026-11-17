<?php

namespace App\Helpers;

use Carbon\Carbon;

class FormatTanggal
{
    private const BULAN = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Agu',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des',
    ];

    /**
     * Format a date range in the finance tables:
     * - 01 - 07 Nov 26 (same month)
     * - 28 Nov - 05 Des 26 (different months)
     * - 28 Des 26 - 04 Jan 27 (different years)
     */
    public static function rentang(mixed $start, mixed $end): string
    {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        if ($startDate->isSameMonth($endDate) && $startDate->isSameYear($endDate)) {
            return $startDate->format('d').' - '.$endDate->format('d').' '
                .self::BULAN[$startDate->month].' '.$endDate->format('y');
        }

        if ($startDate->isSameYear($endDate)) {
            return $startDate->format('d').' '.self::BULAN[$startDate->month].' - '
                .$endDate->format('d').' '.self::BULAN[$endDate->month].' '.$endDate->format('y');
        }

        return $startDate->format('d').' '.self::BULAN[$startDate->month].' '.$startDate->format('y').' - '
            .$endDate->format('d').' '.self::BULAN[$endDate->month].' '.$endDate->format('y');
    }
}
