<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Services\NationalHolidayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class NationalHolidayServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        config([
            'services.holiday.auto_sync' => true,
            'services.holiday.include_joint_leave' => false,
            'services.holiday.timeout' => 2,
        ]);
    }

    public function test_it_reads_national_holidays_from_the_primary_api(): void
    {
        Http::fake([
            '*data-libur*' => Http::response([
                'year' => 2026,
                'libur_nasional' => [
                    ['date' => '2026-01-01', 'name' => 'Tahun Baru 2026 Masehi'],
                    ['date' => '2026-08-17', 'name' => 'Hari Proklamasi Kemerdekaan RI'],
                ],
                'cuti_bersama' => [
                    ['date' => '2026-12-24', 'name' => 'Cuti Bersama Kelahiran Yesus Kristus'],
                ],
            ]),
        ]);

        $rows = app(NationalHolidayService::class)->fetch(2026);

        $this->assertSame(['2026-01-01', '2026-08-17'], array_column($rows, 'date'));
        Http::assertSent(fn ($request) => str_contains($request->url(), 'data-libur'));
    }

    public function test_it_falls_back_to_the_github_dataset_for_missing_years(): void
    {
        Http::fake([
            '*data-libur*' => Http::response(['error' => 'Belum ada data'], 404),
            '*guangrei*' => Http::response([
                '2027-01-01' => ['summary' => 'Hari Tahun Baru'],
                '2027-03-09' => ['summary' => 'Cuti Bersama Idul Fitri'],
                '2027-03-10' => ['summary' => 'Hari Idul Fitri (belum pasti)'],
                '2028-01-01' => ['summary' => 'Tahun Baru 2028'],
            ]),
        ]);

        $rows = app(NationalHolidayService::class)->fetch(2027);

        $this->assertSame(['2027-01-01', '2027-03-10'], array_column($rows, 'date'));
    }

    public function test_joint_leave_can_be_included(): void
    {
        config(['services.holiday.include_joint_leave' => true]);

        Http::fake([
            '*data-libur*' => Http::response([
                'libur_nasional' => [
                    ['date' => '2026-01-01', 'name' => 'Tahun Baru 2026 Masehi'],
                ],
                'cuti_bersama' => [
                    ['date' => '2026-12-24', 'name' => 'Cuti Bersama Kelahiran Yesus Kristus'],
                ],
            ]),
        ]);

        $rows = app(NationalHolidayService::class)->fetch(2026);

        $this->assertCount(2, $rows);
        $this->assertSame(Holiday::TYPE_NATIONAL, $rows[0]['type']);
        $this->assertSame(Holiday::TYPE_JOINT_LEAVE, $rows[1]['type']);
    }

    public function test_sync_replaces_only_the_requested_year(): void
    {
        Holiday::create(['date' => '2025-12-25', 'name' => 'Natal 2025', 'source' => 'api']);
        Holiday::create(['date' => '2026-01-02', 'name' => 'Data lama', 'source' => 'manual']);

        Http::fake([
            '*data-libur*' => Http::response([
                'libur_nasional' => [
                    ['date' => '2026-01-01', 'name' => 'Tahun Baru 2026 Masehi'],
                    ['date' => '2026-08-17', 'name' => 'Hari Proklamasi Kemerdekaan RI'],
                ],
            ]),
        ]);

        $count = app(NationalHolidayService::class)->syncYear(2026);

        $this->assertSame(2, $count);
        $this->assertSame(['Natal 2025'], Holiday::whereYear('date', 2025)->pluck('name')->all());
        $this->assertEqualsCanonicalizing(
            ['Tahun Baru 2026 Masehi', 'Hari Proklamasi Kemerdekaan RI'],
            Holiday::whereYear('date', 2026)->pluck('name')->all()
        );
        $this->assertSame(2, Holiday::whereYear('date', 2026)->where('source', 'api')->count());
    }

    public function test_a_failed_sync_keeps_the_existing_rows(): void
    {
        Holiday::create(['date' => '2026-01-01', 'name' => 'Hari Lama', 'source' => 'api']);

        Http::fake(['*' => Http::response('', 500)]);

        $this->expectException(\RuntimeException::class);

        try {
            app(NationalHolidayService::class)->syncYear(2026);
        } finally {
            $this->assertDatabaseHas('holidays', ['name' => 'Hari Lama']);
        }
    }

    public function test_ensure_year_is_quiet_when_the_api_fails(): void
    {
        Log::spy();
        Http::fake(['*' => Http::response('', 500)]);

        $service = app(NationalHolidayService::class);
        $service->ensureYear(2026);

        $this->assertSame(0, Holiday::count());
        Log::shouldHaveReceived('warning')->once();

        // The failure is cached briefly so the next render does not retry.
        $requestsAfterFailure = Http::recorded()->count();
        $service->ensureYear(2026);

        $this->assertSame($requestsAfterFailure, Http::recorded()->count());
    }

    public function test_sync_stores_the_holiday_type(): void
    {
        Http::fake([
            '*data-libur*' => Http::response([
                'libur_nasional' => [
                    ['date' => '2026-01-01', 'name' => 'Tahun Baru 2026 Masehi'],
                ],
            ]),
        ]);

        app(NationalHolidayService::class)->syncYear(2026);

        $this->assertDatabaseHas('holidays', [
            'name' => 'Tahun Baru 2026 Masehi',
            'type' => Holiday::TYPE_NATIONAL,
            'source' => 'api',
        ]);
    }

    public function test_ensure_year_removes_obsolete_row_types(): void
    {
        Holiday::create([
            'date' => '2026-01-01',
            'name' => 'Tahun Baru 2026 Masehi',
            'type' => Holiday::TYPE_NATIONAL,
            'source' => 'api',
        ]);
        Holiday::create([
            'date' => '2026-04-21',
            'name' => 'Hari Kartini',
            'type' => 'hari_besar_nasional',
            'source' => 'api',
        ]);

        Http::fake([
            '*data-libur*' => Http::response([
                'libur_nasional' => [
                    ['date' => '2026-01-01', 'name' => 'Tahun Baru 2026 Masehi'],
                ],
            ]),
        ]);

        app(NationalHolidayService::class)->ensureYear(2026);

        $this->assertDatabaseMissing('holidays', ['name' => 'Hari Kartini']);
        $this->assertDatabaseHas('holidays', ['name' => 'Tahun Baru 2026 Masehi']);
    }
}
