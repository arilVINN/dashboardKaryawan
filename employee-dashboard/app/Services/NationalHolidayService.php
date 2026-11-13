<?php

namespace App\Services;

use App\Models\Holiday;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class NationalHolidayService
{
    /**
     * Structured SKB 3 Menteri data (national holidays + cuti bersama).
     */
    private const DATA_LIBUR_URL = 'https://data-libur-nasional-indonesia.vercel.app/api/libur?year=%d';

    /**
     * Community-maintained fallback dataset (also covers years the primary API
     * does not publish yet, e.g. 2027).
     */
    private const GUANGREI_URL = 'https://raw.githubusercontent.com/guangrei/APIHariLibur_V2/main/holidays.json';

    /**
     * Additional fallback with structured national holiday data.
     */
    private const WERUKA_URL = 'https://cal.weruka.dev/api/holidays?year=%d';

    /**
     * Bump when the stored shape changes so previously synced years are
     * refreshed on the next calendar render.
     */
    private const SYNC_VERSION = 'v3';

    /**
     * @return array<int, array{date: string, name: string, type: string}>
     */
    public function fetch(int $year): array
    {
        return Cache::remember(
            'national-holidays:public:'.self::SYNC_VERSION.":{$year}",
            now()->addDay(),
            function () use ($year) {
                $errors = [];

                foreach ($this->sources() as $source) {
                    try {
                        $rows = $this->normalize(
                            $source['driver'],
                            $this->request($source['url'], $year),
                            $year
                        );

                        if ($rows !== []) {
                            return $rows;
                        }

                        $errors[] = "{$source['name']}: data kosong";
                    } catch (\Throwable $exception) {
                        $errors[] = "{$source['name']}: {$exception->getMessage()}";
                    }
                }

                throw new RuntimeException(
                    'Gagal mengambil hari libur dari semua sumber ('.implode('; ', $errors).').'
                );
            }
        );
    }

    /**
     * Replace the stored holidays for one year with the API result.
     */
    public function syncYear(int $year): int
    {
        $holidays = $this->fetch($year);

        if ($holidays === []) {
            throw new RuntimeException("Data hari libur {$year} kosong.");
        }

        return DB::transaction(function () use ($year, $holidays) {
            Holiday::query()->whereYear('date', $year)->delete();

            $now = now();

            foreach (array_chunk($holidays, 200) as $chunk) {
                Holiday::query()->insert(array_map(
                    fn (array $holiday) => [
                        'date' => $holiday['date'],
                        'name' => mb_substr($holiday['name'], 0, 150),
                        'type' => $holiday['type'],
                        'source' => 'api',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    $chunk
                ));
            }

            Cache::forget("national-holidays:sync-failed:{$year}");
            Cache::put($this->syncMarker($year), true, now()->addDay());

            return count($holidays);
        });
    }

    /**
     * Fetch a year once and silently fall back to the stored data when the API
     * is unreachable, so rendering the calendar never fails because of it.
     */
    public function ensureYear(int $year): void
    {
        if (! config('services.holiday.auto_sync')) {
            return;
        }

        $marker = $this->syncMarker($year);

        if (Cache::has($marker)) {
            return;
        }

        $hasHolidays = Holiday::query()
            ->whereYear('date', $year)
            ->whereIn('type', Holiday::DAY_OFF_TYPES)
            ->where('source', 'api')
            ->exists();

        // Resync when obsolete row types (e.g. old observances) are present.
        $hasObsoleteRows = Holiday::query()
            ->whereYear('date', $year)
            ->whereNotIn('type', Holiday::DAY_OFF_TYPES)
            ->exists();

        if ($hasHolidays && ! $hasObsoleteRows) {
            Cache::put($marker, true, now()->addDay());

            return;
        }

        if (Cache::has("national-holidays:sync-failed:{$year}")) {
            return;
        }

        try {
            $this->syncYear($year);
        } catch (\Throwable $exception) {
            Cache::put("national-holidays:sync-failed:{$year}", true, now()->addHour());

            Log::warning('Sinkronisasi hari libur gagal.', [
                'year' => $year,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<int, int>  $years
     */
    public function ensureYears(array $years): void
    {
        foreach (array_unique($years) as $year) {
            $this->ensureYear((int) $year);
        }
    }

    private function syncMarker(int $year): string
    {
        return 'national-holidays:synced:'.self::SYNC_VERSION.":{$year}";
    }

    /**
     * @return array<int, array{name: string, driver: string, url: string}>
     */
    private function sources(): array
    {
        return [
            [
                'name' => 'data-libur',
                'driver' => 'data-libur',
                'url' => self::DATA_LIBUR_URL,
            ],
            [
                'name' => 'guangrei',
                'driver' => 'guangrei',
                'url' => self::GUANGREI_URL,
            ],
            [
                'name' => 'weruka',
                'driver' => 'weruka',
                'url' => self::WERUKA_URL,
            ],
        ];
    }

    private function request(string $url, int $year): mixed
    {
        $url = str_contains($url, '%d') ? sprintf($url, $year) : $url;

        $response = Http::timeout((int) config('services.holiday.timeout', 5))
            ->connectTimeout(3)
            ->acceptJson()
            ->get($url);

        $response->throw();

        return $response->json();
    }

    /**
     * @return array<int, array{date: string, name: string, type: string}>
     */
    private function normalize(string $driver, mixed $payload, int $year): array
    {
        if (! is_array($payload)) {
            throw new RuntimeException('Respons API tidak valid.');
        }

        $rows = match ($driver) {
            'data-libur' => $this->normalizeDataLibur($payload),
            'guangrei' => $this->normalizeGuangrei($payload, $year),
            'weruka' => $this->normalizeWeruka($payload, $year),
            default => throw new RuntimeException('Sumber data tidak dikenal.'),
        };

        return $this->clean($rows, $year);
    }

    /**
     * @return array<int, array{date: string, name: string, type: string}>
     */
    private function normalizeDataLibur(array $payload): array
    {
        $rows = [];

        foreach ($payload['libur_nasional'] ?? [] as $holiday) {
            $date = $holiday['date'] ?? null;
            $name = trim((string) ($holiday['name'] ?? ''));

            if ($date && $name !== '') {
                $rows[] = [
                    'date' => $date,
                    'name' => $name,
                    'type' => Holiday::TYPE_NATIONAL,
                ];
            }
        }

        if (config('services.holiday.include_joint_leave')) {
            foreach ($payload['cuti_bersama'] ?? [] as $holiday) {
                $date = $holiday['date'] ?? null;
                $name = trim((string) ($holiday['name'] ?? ''));

                if ($date && $name !== '') {
                    $rows[] = [
                        'date' => $date,
                        'name' => $name,
                        'type' => Holiday::TYPE_JOINT_LEAVE,
                    ];
                }
            }
        }

        return $rows;
    }

    /**
     * @return array<int, array{date: string, name: string, type: string}>
     */
    private function normalizeGuangrei(array $payload, int $year): array
    {
        $rows = [];

        foreach ($payload as $date => $holiday) {
            if (! is_string($date) || ! str_starts_with($date, $year.'-')) {
                continue;
            }

            $name = trim((string) ($holiday['summary'] ?? ''));

            if ($name === '') {
                continue;
            }

            $isJointLeave = str_starts_with(mb_strtolower($name), 'cuti bersama')
                || str_starts_with(mb_strtolower($name), 'joint holiday');

            if ($isJointLeave && ! config('services.holiday.include_joint_leave')) {
                continue;
            }

            $rows[] = [
                'date' => $date,
                'name' => $name,
                'type' => $isJointLeave ? Holiday::TYPE_JOINT_LEAVE : Holiday::TYPE_NATIONAL,
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array{date: string, name: string, type: string}>
     */
    private function normalizeWeruka(array $payload, int $year): array
    {
        $rows = [];

        foreach ($payload as $holiday) {
            if (! is_array($holiday)) {
                continue;
            }

            $type = (string) ($holiday['type'] ?? '');

            if (! in_array($type, Holiday::DAY_OFF_TYPES, true)) {
                continue;
            }

            if ($type === Holiday::TYPE_JOINT_LEAVE && ! config('services.holiday.include_joint_leave')) {
                continue;
            }

            $date = $holiday['date'] ?? null;
            $name = trim((string) ($holiday['name'] ?? ''));

            if (! is_string($date) || ! str_starts_with($date, $year.'-') || $name === '') {
                continue;
            }

            $rows[] = [
                'date' => $date,
                'name' => $name,
                'type' => $type,
            ];
        }

        return $rows;
    }

    /**
     * @param  array<int, array{date: string, name: string, type: string}>  $rows
     * @return array<int, array{date: string, name: string, type: string}>
     */
    private function clean(array $rows, int $year): array
    {
        return collect($rows)
            ->filter(function (array $row) use ($year) {
                if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $row['date'], $parts)) {
                    return false;
                }

                return (int) $parts[1] === $year
                    && checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])
                    && $row['name'] !== '';
            })
            ->unique(fn (array $row) => $row['date'].'|'.mb_strtolower($row['name']))
            ->sortBy(fn (array $row) => $row['date'].'|'.$row['type'].'|'.mb_strtolower($row['name']))
            ->values()
            ->all();
    }
}
