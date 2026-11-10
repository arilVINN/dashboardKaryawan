<?php

namespace Database\Seeders;

use App\Services\NationalHolidayService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class HolidaySeeder extends Seeder
{
    /**
     * Pull national holidays from the API instead of keeping a manual list.
     * A failed sync only logs a warning so seeding still completes offline.
     */
    public function run(NationalHolidayService $holidays): void
    {
        if (! config('services.holiday.auto_sync')) {
            $this->command?->warn('Sinkronisasi hari libur dilewati (HOLIDAY_AUTO_SYNC=false).');

            return;
        }

        foreach ([now()->year, now()->year + 1] as $year) {
            try {
                $count = $holidays->syncYear($year);
                $this->command?->info("Hari libur {$year}: {$count} entri disinkronkan.");
            } catch (\Throwable $exception) {
                Log::warning('Seeder hari libur gagal.', [
                    'year' => $year,
                    'message' => $exception->getMessage(),
                ]);

                $this->command?->warn("Hari libur {$year} gagal disinkronkan: {$exception->getMessage()}");
            }
        }
    }
}
