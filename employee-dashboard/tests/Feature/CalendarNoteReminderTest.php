<?php

namespace Tests\Feature;

use App\Models\CalendarNote;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CalendarNoteReminderTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);

        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'status_aktif' => 'aktif',
        ]);

        Karyawan::create([
            'id_karyawan' => 'KRY-STAFF',
            'nama' => 'Staff Dummy',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '2000-01-01',
            'tanggal_masuk' => '2026-01-10',
            'no_telepon' => '081234567890',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        $this->staff = User2::create([
            'id_user' => 'USR-STAFF',
            'username' => 'staff.dummy',
            'password' => 'password',
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => 'KRY-STAFF',
            'last_login_at' => '2026-11-04 08:00:00',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_note_stores_custom_reminder_days_before(): void
    {
        $this->actingAs($this->staff)
            ->postJson('/kalender/notes', [
                'tanggal' => now()->addDays(5)->toDateString(),
                'judul' => 'Evaluasi mingguan',
                'detail' => 'Siapkan laporan.',
                'warna' => 'blue',
                'reminder_days_before' => 3,
            ])
            ->assertCreated()
            ->assertJsonPath('event.extendedProps.reminder_days_before', 3)
            ->assertJsonPath('event.extendedProps.reminder_label', '3 hari sebelum');

        $this->assertDatabaseHas('calendar_notes', [
            'user_id_user' => $this->staff->id_user,
            'judul' => 'Evaluasi mingguan',
            'reminder_days_before' => 3,
        ]);
    }

    public function test_reminder_days_before_defaults_to_one_day(): void
    {
        $this->actingAs($this->staff)
            ->postJson('/kalender/notes', [
                'tanggal' => now()->addDays(2)->toDateString(),
                'judul' => 'Catatan tanpa pengingat kustom',
            ])
            ->assertCreated()
            ->assertJsonPath('event.extendedProps.reminder_days_before', CalendarNote::REMINDER_DEFAULT_DAYS);
    }

    public function test_reminder_can_be_disabled_with_null(): void
    {
        $this->actingAs($this->staff)
            ->postJson('/kalender/notes', [
                'tanggal' => now()->addDays(2)->toDateString(),
                'judul' => 'Catatan tanpa pengingat',
                'reminder_days_before' => null,
            ])
            ->assertCreated()
            ->assertJsonPath('event.extendedProps.reminder_days_before', null)
            ->assertJsonPath('event.extendedProps.reminder_label', null);
    }

    public function test_reminder_days_before_is_validated(): void
    {
        $this->actingAs($this->staff)
            ->postJson('/kalender/notes', [
                'tanggal' => now()->addDays(2)->toDateString(),
                'judul' => 'Pengingat negatif',
                'reminder_days_before' => -1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reminder_days_before');

        $this->actingAs($this->staff)
            ->postJson('/kalender/notes', [
                'tanggal' => now()->addDays(2)->toDateString(),
                'judul' => 'Pengingat terlalu jauh',
                'reminder_days_before' => CalendarNote::REMINDER_MAX_DAYS + 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reminder_days_before');
    }

    public function test_reminder_accepts_the_maximum_weekly_offset_in_days(): void
    {
        // 60 minggu = 420 hari (batas maksimum pilihan UI).
        $this->actingAs($this->staff)
            ->postJson('/kalender/notes', [
                'tanggal' => now()->addDays(500)->toDateString(),
                'judul' => 'Pengingat maksimum',
                'reminder_days_before' => 420,
            ])
            ->assertCreated()
            ->assertJsonPath('event.extendedProps.reminder_days_before', 420);
    }

    public function test_reminder_date_in_the_past_is_rejected(): void
    {
        $this->actingAs($this->staff)
            ->postJson('/kalender/notes', [
                'tanggal' => now()->toDateString(),
                'judul' => 'Pengingat sudah lewat',
                'reminder_days_before' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reminder_days_before');

        $this->assertDatabaseCount('calendar_notes', 0);
    }

    public function test_note_index_returns_reminder_metadata(): void
    {
        $note = $this->createNote('Catatan rapat', now()->addDays(4), 2);

        $this->actingAs($this->staff)
            ->getJson('/kalender/notes')
            ->assertOk()
            ->assertJsonPath('data.0.extendedProps.note_id', $note->id)
            ->assertJsonPath('data.0.extendedProps.reminder_days_before', 2)
            ->assertJsonPath('data.0.extendedProps.reminder_label', '2 hari sebelum')
            ->assertJsonPath('data.0.extendedProps.reminder_date', now()->addDays(2)->toDateString());
    }

    public function test_calendar_date_filters_reject_invalid_or_reversed_ranges(): void
    {
        $this->actingAs($this->staff)
            ->getJson('/kalender/notes?start=invalid-date')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('start');

        $start = urlencode(now()->startOfMonth()->toIso8601String());
        $end = urlencode(now()->endOfMonth()->toIso8601String());
        $this->actingAs($this->staff)
            ->getJson("/kalender/data?start={$start}&end={$end}")
            ->assertOk();

        $this->actingAs($this->staff)
            ->getJson('/kalender/data?start=2026-11-10&end=2026-11-09')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('end');
    }

    public function test_notifications_include_notes_on_their_chosen_reminder_day(): void
    {
        Carbon::setTestNow('2026-11-04 08:00:00');

        $remindTodayThreeDays = $this->createNote('Rapat tiga hari lagi', now()->addDays(3), 3);
        $remindTodayOnTheDay = $this->createNote('Acara hari ini', now(), 0);
        $this->createNote('Belum waktunya', now()->addDays(1), 3);
        $this->createNote('Terlalu awal', now()->addDays(5), 1);
        $this->createNote('Tanpa pengingat', now()->addDays(2), null);

        $response = $this->actingAs($this->staff)
            ->getJson('/staff/notifikasi')
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id_notifikasi');

        $this->assertContains('note-reminder-' . $remindTodayThreeDays->id, $ids);
        $this->assertContains('note-reminder-' . $remindTodayOnTheDay->id, $ids);
        $this->assertCount(2, $ids);

        $threeDays = collect($response->json('data'))
            ->firstWhere('id_notifikasi', 'note-reminder-' . $remindTodayThreeDays->id);
        $this->assertStringContainsString('3 hari lagi', $threeDays['isi_notif']);

        $onTheDay = collect($response->json('data'))
            ->firstWhere('id_notifikasi', 'note-reminder-' . $remindTodayOnTheDay->id);
        $this->assertStringContainsString('hari ini', $onTheDay['isi_notif']);
    }

    private function createNote(string $judul, Carbon $tanggal, ?int $reminderDaysBefore): CalendarNote
    {
        return CalendarNote::create([
            'user_id_user' => $this->staff->id_user,
            'tanggal' => $tanggal->toDateString(),
            'judul' => $judul,
            'detail' => null,
            'warna' => 'slate',
            'reminder_days_before' => $reminderDaysBefore,
        ]);
    }
}
