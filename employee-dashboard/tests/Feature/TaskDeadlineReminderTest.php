<?php

namespace Tests\Feature;

use App\Mail\TugasDeadlineMail;
use App\Models\Karyawan;
use App\Models\Tugas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TaskDeadlineReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        // Seeder membuat tugas dengan deadline "besok", yang bisa masuk jendela
        // 24 jam tergantung jam saat test berjalan. Tandai semuanya sudah
        // diingatkan agar hanya tugas yang dibuat test ini yang dikirim.
        Tugas::query()->update(['deadline_reminder_sent_at' => now()]);
    }

    public function test_it_emails_tasks_due_within_24_hours(): void
    {
        Mail::fake();

        $task = $this->makeTask('TG901', [
            'deadline' => now()->addHours(20),
            'status' => Tugas::STATUS_BERJALAN,
        ]);

        $this->artisan('tugas:remind-deadline')->assertExitCode(0);

        Mail::assertSent(
            TugasDeadlineMail::class,
            fn (TugasDeadlineMail $mail) => $mail->hasTo('staff@silindo.co.id')
                && $mail->tugas->id_tugas === 'TG901'
        );

        $this->assertNotNull($task->fresh()->deadline_reminder_sent_at);
    }

    public function test_it_does_not_email_tasks_beyond_24_hours(): void
    {
        Mail::fake();

        $task = $this->makeTask('TG902', [
            'deadline' => now()->addHours(30),
            'status' => Tugas::STATUS_BERJALAN,
        ]);

        $this->artisan('tugas:remind-deadline')->assertExitCode(0);

        Mail::assertNothingOutgoing();
        $this->assertNull($task->fresh()->deadline_reminder_sent_at);
    }

    public function test_it_does_not_email_completed_tasks(): void
    {
        Mail::fake();

        $this->makeTask('TG903', [
            'deadline' => now()->addHours(10),
            'status' => Tugas::STATUS_SUDAH_ACC,
        ]);

        $this->artisan('tugas:remind-deadline')->assertExitCode(0);

        Mail::assertNothingOutgoing();
    }

    public function test_it_does_not_email_tasks_waiting_for_approval(): void
    {
        Mail::fake();

        $this->makeTask('TG904', [
            'deadline' => now()->addHours(10),
            'status' => Tugas::STATUS_MENUNGGU_ACC,
        ]);

        $this->artisan('tugas:remind-deadline')->assertExitCode(0);

        Mail::assertNothingOutgoing();
    }

    public function test_it_does_not_send_the_same_reminder_twice(): void
    {
        Mail::fake();

        $this->makeTask('TG905', [
            'deadline' => now()->addHours(20),
            'status' => Tugas::STATUS_BERJALAN,
        ]);

        $this->artisan('tugas:remind-deadline')->assertExitCode(0);
        $this->artisan('tugas:remind-deadline')->assertExitCode(0);

        Mail::assertSent(TugasDeadlineMail::class, 1);
    }

    public function test_to_override_sends_the_email_without_marking_the_task(): void
    {
        Mail::fake();

        $task = $this->makeTask('TG906', [
            'deadline' => now()->addHours(20),
            'status' => Tugas::STATUS_BERJALAN,
        ]);

        $this->artisan('tugas:remind-deadline', [
            '--to' => 'penerima.uji@example.com',
        ])->assertExitCode(0);

        Mail::assertSent(
            TugasDeadlineMail::class,
            fn (TugasDeadlineMail $mail) => $mail->hasTo('penerima.uji@example.com')
        );

        $this->assertNull($task->fresh()->deadline_reminder_sent_at);
    }

    public function test_dry_run_does_not_send_anything(): void
    {
        Mail::fake();

        $task = $this->makeTask('TG907', [
            'deadline' => now()->addHours(20),
            'status' => Tugas::STATUS_BERJALAN,
        ]);

        $this->artisan('tugas:remind-deadline', ['--dry-run' => true])
            ->expectsOutputToContain('dry-run')
            ->assertExitCode(0);

        Mail::assertNothingOutgoing();
        $this->assertNull($task->fresh()->deadline_reminder_sent_at);
    }

    public function test_it_skips_employees_without_an_email(): void
    {
        Mail::fake();

        Karyawan::where('id_karyawan', 'KR001')->update(['email' => null]);

        $this->makeTask('TG908', [
            'deadline' => now()->addHours(20),
            'status' => Tugas::STATUS_BERJALAN,
        ]);

        $this->artisan('tugas:remind-deadline')
            ->expectsOutputToContain('tidak memiliki email')
            ->assertExitCode(0);

        Mail::assertNothingOutgoing();
    }

    public function test_id_option_can_force_a_specific_task(): void
    {
        Mail::fake();

        $this->makeTask('TG909', [
            'deadline' => now()->addDays(5),
            'status' => Tugas::STATUS_BERJALAN,
        ]);

        $this->artisan('tugas:remind-deadline', ['--id' => 'TG909'])
            ->assertExitCode(0);

        Mail::assertSent(
            TugasDeadlineMail::class,
            fn (TugasDeadlineMail $mail) => $mail->tugas->id_tugas === 'TG909'
        );
    }

    public function test_updating_the_deadline_resets_the_reminder_marker(): void
    {
        $task = $this->makeTask('TG910', [
            'deadline' => now()->addHours(20),
            'status' => Tugas::STATUS_BERJALAN,
            'deadline_reminder_sent_at' => now(),
        ]);

        $this->assertNotNull($task->fresh()->deadline_reminder_sent_at);

        $task->update(['deadline' => now()->addDays(2)]);

        $this->assertNull($task->fresh()->deadline_reminder_sent_at);
    }

    public function test_the_reminder_email_renders_the_deadline_and_task_link(): void
    {
        $staff = Karyawan::where('id_karyawan', 'KR001')->firstOrFail();

        $task = $this->makeTask('TG911', [
            'deadline' => Carbon::parse('2026-11-03 17:00:00'),
            'status' => Tugas::STATUS_BERJALAN,
        ]);

        $mail = new TugasDeadlineMail($task);

        $this->assertSame(
            'Pengingat Deadline: '.$task->judul_tugas,
            $mail->envelope()->subject
        );

        $html = $mail->render();

        $this->assertStringContainsString($task->judul_tugas, $html);
        $this->assertStringContainsString($staff->nama, $html);
        $this->assertStringContainsString(url('/tugas'), $html);
        $this->assertStringContainsString('kurang dari 24 jam', $html);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeTask(string $id, array $overrides = []): Tugas
    {
        $staff = Karyawan::where('id_karyawan', 'KR001')->firstOrFail();

        return Tugas::withoutEvents(fn () => Tugas::create(array_merge([
            'id_tugas' => $id,
            'karyawan_id_karyawan' => $staff->id_karyawan,
            'judul_tugas' => 'Tugas Pengingat '.$id,
            'deskripsi' => 'Tugas untuk menguji pengingat deadline.',
            'deadline' => now()->addHours(20),
            'progress' => '0',
            'status' => Tugas::STATUS_BERJALAN,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ], $overrides)));
    }
}
