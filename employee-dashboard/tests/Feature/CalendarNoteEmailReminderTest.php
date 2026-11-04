<?php

namespace Tests\Feature;

use App\Mail\CalendarNoteReminderMail;
use App\Models\CalendarNote;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CalendarNoteEmailReminderTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;

    private Karyawan $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        config(['mail.queue_notifications' => false]);

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'kadiv'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'hrd'],
        ]);

        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'status_aktif' => 'aktif',
        ]);

        $this->karyawan = Karyawan::create([
            'id_karyawan' => 'KRY-STAFF',
            'nama' => 'Staff Dummy',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '2000-01-01',
            'tanggal_masuk' => '2026-01-10',
            'no_telepon' => '081234567890',
            'email' => 'staff@example.test',
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

    public function test_it_emails_a_note_on_its_reminder_date(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-11-04 08:00:00');

        $note = $this->createNote('Rapat bulanan', now()->addDays(3), 3);

        $this->artisan('catatan:remind-email')->assertSuccessful();

        Mail::assertSent(CalendarNoteReminderMail::class, function (CalendarNoteReminderMail $mail) use ($note): bool {
            return $mail->hasTo('staff@example.test') && $mail->note->is($note);
        });

        $this->assertNotNull($note->fresh()->reminder_email_sent_at);
    }

    public function test_it_does_not_email_before_the_reminder_date(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-11-04 08:00:00');

        $this->createNote('Belum waktunya', now()->addDays(5), 1);

        $this->artisan('catatan:remind-email')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_it_skips_notes_without_a_reminder(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-11-04 08:00:00');

        $this->createNote('Tanpa pengingat', now()->addDays(2), null);

        $this->artisan('catatan:remind-email')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_it_does_not_send_the_same_reminder_twice(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-11-04 08:00:00');

        $this->createNote('Rapat bulanan', now()->addDays(3), 3);

        $this->artisan('catatan:remind-email')->assertSuccessful();
        $this->artisan('catatan:remind-email')->assertSuccessful();

        Mail::assertSent(CalendarNoteReminderMail::class, 1);
    }

    public function test_it_skips_notes_whose_employee_has_no_email(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-11-04 08:00:00');

        $this->karyawan->update(['email' => null]);

        $note = $this->createNote('Tanpa email', now()->addDays(3), 3);

        $this->artisan('catatan:remind-email')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($note->fresh()->reminder_email_sent_at);
    }

    public function test_dry_run_sends_nothing_and_marks_nothing(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-11-04 08:00:00');

        $note = $this->createNote('Rapat bulanan', now()->addDays(3), 3);

        $this->artisan('catatan:remind-email', ['--dry-run' => true])->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($note->fresh()->reminder_email_sent_at);
    }

    public function test_to_override_sends_without_marking_the_note(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-11-04 08:00:00');

        $note = $this->createNote('Rapat bulanan', now()->addDays(3), 3);

        $this->artisan('catatan:remind-email', ['--to' => 'qa@example.test'])->assertSuccessful();

        Mail::assertSent(CalendarNoteReminderMail::class, fn (CalendarNoteReminderMail $mail): bool => $mail->hasTo('qa@example.test'));
        $this->assertNull($note->fresh()->reminder_email_sent_at);
    }

    public function test_force_resends_an_already_sent_reminder(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-11-04 08:00:00');

        $note = $this->createNote('Rapat bulanan', now()->addDays(3), 3);
        $note->forceFill(['reminder_email_sent_at' => now()])->saveQuietly();

        $this->artisan('catatan:remind-email')->assertSuccessful();
        Mail::assertNothingSent();

        $this->artisan('catatan:remind-email', ['--force' => true])->assertSuccessful();
        Mail::assertSent(CalendarNoteReminderMail::class, 1);
    }

    public function test_id_option_sends_a_note_regardless_of_its_reminder_date(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-11-04 08:00:00');

        $note = $this->createNote('Catatan khusus', now()->addDays(30), 3);

        $this->artisan('catatan:remind-email', ['--id' => $note->id])->assertSuccessful();

        Mail::assertSent(CalendarNoteReminderMail::class, 1);
        $this->assertNotNull($note->fresh()->reminder_email_sent_at);
    }

    public function test_date_option_uses_an_alternate_reference_date(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-11-01 08:00:00');

        $note = $this->createNote('Rapat akhir bulan', now()->addDays(30), 3);

        $this->artisan('catatan:remind-email', ['--date' => '2026-11-28'])->assertSuccessful();

        Mail::assertSent(CalendarNoteReminderMail::class, 1);
        $this->assertNotNull($note->fresh()->reminder_email_sent_at);
    }

    public function test_changing_the_note_date_or_reminder_resets_the_sent_marker(): void
    {
        Carbon::setTestNow('2026-11-04 08:00:00');

        $note = $this->createNote('Rapat bulanan', now()->addDays(3), 3);
        $note->forceFill(['reminder_email_sent_at' => now()])->saveQuietly();

        $note->update(['tanggal' => now()->addDays(10)->toDateString()]);
        $this->assertNull($note->fresh()->reminder_email_sent_at);

        $note->forceFill(['reminder_email_sent_at' => now()])->saveQuietly();
        $note->update(['reminder_days_before' => 5]);
        $this->assertNull($note->fresh()->reminder_email_sent_at);
    }

    public function test_reminder_email_renders_the_note_details_and_calendar_link(): void
    {
        Carbon::setTestNow('2026-11-04 08:00:00');

        $note = $this->createNote(
            'Rapat bulanan',
            now()->addDays(3),
            3,
            'Siapkan laporan mingguan.'
        );

        $rendered = (new CalendarNoteReminderMail($note))->render();

        $this->assertStringContainsString('Rapat bulanan', $rendered);
        $this->assertStringContainsString('Siapkan laporan mingguan.', $rendered);
        $this->assertStringContainsString(url('/kalender'), $rendered);
    }

    public function test_reminder_email_uses_the_role_specific_calendar_link(): void
    {
        Carbon::setTestNow('2026-11-04 08:00:00');

        $this->staff->update(['role_id_role' => 'ROLE-KADIV']);

        $note = CalendarNote::create([
            'user_id_user' => $this->staff->id_user,
            'tanggal' => now()->addDays(3)->toDateString(),
            'judul' => 'Rapat divisi',
            'detail' => null,
            'warna' => 'blue',
            'reminder_days_before' => 3,
        ]);

        $rendered = (new CalendarNoteReminderMail($note))->render();

        $this->assertStringContainsString(url('/kadiv/kalender'), $rendered);
    }

    public function test_it_sends_immediately_when_a_same_day_note_is_created_after_08_00(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-11-04 09:00:00');

        $this->actingAs($this->staff)
            ->postJson('/kalender/notes', [
                'tanggal' => now()->toDateString(),
                'judul' => 'Agenda hari ini',
                'detail' => 'Jangan lupa hadir.',
                'warna' => 'blue',
                'reminder_days_before' => 0,
            ])
            ->assertCreated();

        Mail::assertSent(CalendarNoteReminderMail::class, function (CalendarNoteReminderMail $mail): bool {
            return $mail->hasTo('staff@example.test')
                && $mail->note->judul === 'Agenda hari ini';
        });

        $note = CalendarNote::where('judul', 'Agenda hari ini')->firstOrFail();
        $this->assertNotNull($note->reminder_email_sent_at);
    }

    public function test_it_does_not_send_immediately_before_08_00(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-11-04 07:00:00');

        $this->actingAs($this->staff)
            ->postJson('/kalender/notes', [
                'tanggal' => now()->toDateString(),
                'judul' => 'Agenda pagi',
                'warna' => 'slate',
                'reminder_days_before' => 0,
            ])
            ->assertCreated();

        Mail::assertNothingSent();

        $note = CalendarNote::where('judul', 'Agenda pagi')->firstOrFail();
        $this->assertNull($note->reminder_email_sent_at);
    }

    private function createNote(
        string $judul,
        Carbon $tanggal,
        ?int $reminderDaysBefore,
        ?string $detail = null
    ): CalendarNote {
        return CalendarNote::create([
            'user_id_user' => $this->staff->id_user,
            'tanggal' => $tanggal->toDateString(),
            'judul' => $judul,
            'detail' => $detail,
            'warna' => 'slate',
            'reminder_days_before' => $reminderDaysBefore,
        ]);
    }
}
