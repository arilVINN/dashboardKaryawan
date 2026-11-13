<?php

namespace App\Console\Commands;

use App\Services\NationalHolidayService;
use Illuminate\Console\Command;

class SyncNationalHolidays extends Command
{
    protected $signature = 'holidays:sync
                            {year? : Tahun tertentu (YYYY)}
                            {--years=2 : Jumlah tahun yang disinkronkan bila tahun tidak diisi}';

    protected $description = 'Sinkronkan hari libur nasional dari API ke kalender';

    public function handle(NationalHolidayService $holidays): int
    {
        $year = $this->argument('year');

        if ($year !== null) {
            if (! ctype_digit((string) $year) || (int) $year < 2000 || (int) $year > 2100) {
                $this->error('Tahun tidak valid. Gunakan format YYYY.');

                return self::FAILURE;
            }

            $years = [(int) $year];
        } else {
            $years = range(
                now()->year,
                now()->year + max(1, (int) $this->option('years')) - 1
            );
        }

        $failed = false;

        foreach ($years as $target) {
            try {
                $count = $holidays->syncYear($target);
                $this->info("Hari libur {$target}: {$count} entri disinkronkan.");
            } catch (\Throwable $exception) {
                $failed = true;
                $this->error("Hari libur {$target}: {$exception->getMessage()}");
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
