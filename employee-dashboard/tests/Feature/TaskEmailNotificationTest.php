<?php

namespace Tests\Feature;

use App\Mail\TugasMail;
use App\Models\Karyawan;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class TaskEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_creating_a_task_sends_an_email_to_the_assigned_employee(): void
    {
        Mail::fake();

        $kadiv = User2::where('username', 'tonokd')->firstOrFail();
        $staff = Karyawan::where('email', 'staff@silindo.co.id')->firstOrFail();

        $this->actingAs($kadiv, 'sanctum')
            ->postJson('/api/kadiv/tugas', [
                'karyawan_id_karyawan' => $staff->id_karyawan,
                'judul_tugas' => 'Tugas Email',
                'deskripsi' => 'Mohon dikerjakan sebelum tenggat.',
                'deadline' => now()->addDay()->format('Y-m-d\TH:i'),
            ])
            ->assertCreated();

        Mail::assertSent(
            TugasMail::class,
            fn (TugasMail $mail) => $mail->hasTo($staff->email)
        );
    }

    public function test_email_is_queued_when_queue_notifications_are_enabled(): void
    {
        config(['mail.queue_notifications' => true]);
        Mail::fake();

        $kadiv = User2::where('username', 'tonokd')->firstOrFail();
        $staff = Karyawan::where('email', 'staff@silindo.co.id')->firstOrFail();

        $this->actingAs($kadiv, 'sanctum')
            ->postJson('/api/kadiv/tugas', [
                'karyawan_id_karyawan' => $staff->id_karyawan,
                'judul_tugas' => 'Tugas Queue',
                'deskripsi' => 'Harus masuk queue.',
                'deadline' => now()->addDay()->format('Y-m-d\TH:i'),
            ])
            ->assertCreated();

        Mail::assertQueued(
            TugasMail::class,
            fn (TugasMail $mail) => $mail->hasTo($staff->email)
        );
    }

    public function test_the_database_notification_is_still_created(): void
    {
        Mail::fake();

        $kadiv = User2::where('username', 'tonokd')->firstOrFail();
        $staff = Karyawan::where('email', 'staff@silindo.co.id')->firstOrFail();
        $staffUser = User2::where('karyawan_id_karyawan', $staff->id_karyawan)->firstOrFail();

        $this->actingAs($kadiv, 'sanctum')
            ->postJson('/api/kadiv/tugas', [
                'karyawan_id_karyawan' => $staff->id_karyawan,
                'judul_tugas' => 'Tugas Notifikasi',
                'deskripsi' => 'Cek notifikasi.',
                'deadline' => now()->addDay()->format('Y-m-d\TH:i'),
            ])
            ->assertCreated();

        $this->assertDatabaseHas('notifikasis', [
            'user_id_user' => $staffUser->id_user,
            'judul_notifikasi' => 'Tugas Baru',
        ]);
    }

    public function test_no_email_is_sent_when_the_employee_has_no_address(): void
    {
        Mail::fake();

        Karyawan::where('id_karyawan', 'KR001')->update(['email' => null]);

        $kadiv = User2::where('username', 'tonokd')->firstOrFail();

        $this->actingAs($kadiv, 'sanctum')
            ->postJson('/api/kadiv/tugas', [
                'karyawan_id_karyawan' => 'KR001',
                'judul_tugas' => 'Tanpa Email',
                'deskripsi' => 'Karyawan ini tidak punya email.',
                'deadline' => now()->addDay()->format('Y-m-d\TH:i'),
            ])
            ->assertCreated();

        Mail::assertNothingOutgoing();
    }

    public function test_livewire_task_creation_uses_the_safe_id_generator_and_sends_email(): void
    {
        Mail::fake();

        $kadiv = User2::where('username', 'tonokd')->firstOrFail();
        $staff = Karyawan::where('email', 'staff@silindo.co.id')->firstOrFail();

        Livewire::actingAs($kadiv)
            ->test(\App\Livewire\Kadiv\TugasTable::class)
            ->set('judul', 'Tugas Livewire')
            ->set('deskripsi', 'Dibuat dari modal.')
            ->set('tenggat', now()->addDay()->format('Y-m-d H:i:s'))
            ->set('recipient', $staff->id_karyawan)
            ->call('createTask')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tugas', [
            'id_tugas' => 'TG005',
            'judul_tugas' => 'Tugas Livewire',
        ]);

        Mail::assertSent(
            TugasMail::class,
            fn (TugasMail $mail) => $mail->hasTo($staff->email)
        );
    }

    public function test_task_creation_works_when_legacy_tgs_ids_exist(): void
    {
        // The seeder ships opaque "TGS-*" task IDs. The old generator sorted
        // IDs as strings, picked TGS-*, and produced TG001 again, which
        // collided with the existing primary key and returned a 500.
        $this->assertGreaterThan(
            0,
            Tugas::where('id_tugas', 'like', 'TGS-%')->count()
        );

        Mail::fake();

        $kadiv = User2::where('username', 'tonokd')->firstOrFail();

        $this->actingAs($kadiv, 'sanctum')
            ->postJson('/api/kadiv/tugas', [
                'karyawan_id_karyawan' => 'KR001',
                'judul_tugas' => 'Setelah ID Legacy',
                'deskripsi' => 'Harus tetap bisa dibuat.',
                'deadline' => now()->addDay()->format('Y-m-d\TH:i'),
            ])
            ->assertCreated()
            ->assertJsonPath('data.id_tugas', 'TG005');

        $this->assertDatabaseHas('tugas', ['id_tugas' => 'TG005']);
    }

    public function test_the_email_renders_the_task_details_and_task_link(): void
    {
        $staff = Karyawan::where('id_karyawan', 'KR001')->firstOrFail();

        $tugas = Tugas::withoutEvents(fn () => Tugas::create([
            'id_tugas' => 'TG900',
            'karyawan_id_karyawan' => $staff->id_karyawan,
            'judul_tugas' => 'Tugas Render Email',
            'deskripsi' => 'Detail tugas untuk email.',
            'deadline' => now()->addDay(),
            'progress' => '0',
            'status' => Tugas::STATUS_BARU,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]));

        $mail = new TugasMail($tugas);

        $this->assertSame(
            'Notifikasi Tugas Baru: Tugas Render Email',
            $mail->envelope()->subject
        );

        $html = $mail->render();

        $this->assertStringContainsString('Tugas Render Email', $html);
        $this->assertStringContainsString($staff->nama, $html);
        $this->assertStringContainsString(url('/tugas'), $html);
    }
}
