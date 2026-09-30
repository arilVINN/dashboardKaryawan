<?php

namespace Tests\Feature;

use App\Events\PesanDikirim;
use App\Events\TugasDitugaskan;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Notifikasi;
use App\Models\Pesan;
use App\Models\Role;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class StaffApiTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;

    private User2 $kadiv;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([TugasDitugaskan::class, PesanDikirim::class]);

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
        ]);

        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'status_aktif' => 'aktif',
        ]);

        $this->staff = $this->createAccount('KRY-STAFF', 'Staff Dummy', 'ROLE-STAFF', 'staff.dummy');
        $this->kadiv = $this->createAccount('KRY-KADIV', 'Kadiv Dummy', 'ROLE-KADIV', 'kadiv.dummy');
    }

    public function test_staff_dashboard_returns_metrics_recent_messages_and_notifications(): void
    {
        foreach (['berjalan', 'baru', 'jeda', 'kendala', 'selesai'] as $index => $status) {
            $this->createTask('TGS-'.$index, $this->staff->karyawan_id_karyawan, $status);
        }

        $task = Tugas::findOrFail('TGS-0');
        $this->createMessage('PSN-DASH', $task, $this->kadiv, 'Pesan dashboard');
        Notifikasi::create([
            'id_notifikasi' => 'NTF-DASH',
            'judul_notifikasi' => 'Notifikasi Dummy',
            'isi_notif' => 'Isi notifikasi untuk pengujian.',
            'tanggal_notifikasi' => '2026-09-29',
            'user_id_user' => $this->staff->id_user,
        ]);

        $this->actingAs($this->staff)
            ->getJson('/staff/dashboard')
            ->assertOk()
            ->assertJsonPath('data.statistik.tugas_berlangsung', 1)
            ->assertJsonPath('data.statistik.tugas_pending', 2)
            ->assertJsonPath('data.statistik.tugas_kendala', 1)
            ->assertJsonPath('data.statistik.tugas_selesai', 1)
            ->assertJsonPath('data.statistik.total_tugas', 5)
            ->assertJsonPath('data.statistik.completion_percentage', 20)
            ->assertJsonPath('data.pesan_terbaru.0.id_pesan', 'PSN-DASH')
            ->assertJsonPath('data.notifikasi_terbaru.0.id_notifikasi', 'NTF-DASH');
    }

    public function test_staff_can_list_and_open_messages_for_their_tasks(): void
    {
        $task = $this->createTask('TGS-OWN', $this->staff->karyawan_id_karyawan, 'berjalan');
        $this->createMessage('PSN-ONE', $task, $this->kadiv, 'Pesan pertama');
        $this->createMessage('PSN-TWO', $task, $this->staff, 'Balasan staff');

        $this->actingAs($this->staff)
            ->getJson('/staff/pesan')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id_tugas', 'TGS-OWN')
            ->assertJsonPath('data.0.jumlah_pesan', 2)
            ->assertJsonPath('data.0.pesan_terakhir.id_pesan', 'PSN-TWO');

        $this->actingAs($this->staff)
            ->getJson('/staff/pesan/TGS-OWN')
            ->assertOk()
            ->assertJsonPath('data.tugas.id_tugas', 'TGS-OWN')
            ->assertJsonCount(2, 'data.pesan')
            ->assertJsonPath('data.pesan.0.id_pesan', 'PSN-ONE')
            ->assertJsonPath('data.pesan.1.id_pesan', 'PSN-TWO');
    }

    public function test_staff_can_send_message_to_their_task(): void
    {
        $task = $this->createTask('TGS-SEND', $this->staff->karyawan_id_karyawan, 'berjalan');

        $response = $this->actingAs($this->staff)->postJson('/staff/pesan/send', [
            'tugas_id_tugas' => $task->id_tugas,
            'judul_pesan' => 'Update Dummy',
            'deskripsi' => 'Pekerjaan sudah mencapai tahap pengujian.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Pesan berhasil dikirim.')
            ->assertJsonPath('data.tugas_id_tugas', 'TGS-SEND')
            ->assertJsonPath('data.pengirim.id_user', $this->staff->id_user);

        $this->assertDatabaseHas('pesans', [
            'judul_pesan' => 'Update Dummy',
            'tugas_id_tugas' => 'TGS-SEND',
            'pengirim_id_user' => $this->staff->id_user,
        ]);
        Event::assertDispatched(PesanDikirim::class);
    }

    public function test_staff_can_list_only_their_notifications(): void
    {
        Notifikasi::insert([
            [
                'id_notifikasi' => 'NTF-OWN',
                'judul_notifikasi' => 'Milik Staff',
                'isi_notif' => 'Notifikasi milik staff.',
                'tanggal_notifikasi' => '2026-09-29',
                'user_id_user' => $this->staff->id_user,
            ],
            [
                'id_notifikasi' => 'NTF-OTHER',
                'judul_notifikasi' => 'Milik Kadiv',
                'isi_notif' => 'Tidak boleh terlihat oleh staff.',
                'tanggal_notifikasi' => '2026-09-29',
                'user_id_user' => $this->kadiv->id_user,
            ],
        ]);

        $this->actingAs($this->staff)
            ->getJson('/staff/notifikasi')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id_notifikasi', 'NTF-OWN');
    }

    public function test_staff_cannot_open_or_send_message_to_another_staff_task(): void
    {
        $otherStaff = $this->createAccount('KRY-OTHER', 'Staff Lain', 'ROLE-STAFF', 'staff.other');
        $otherTask = $this->createTask('TGS-OTHER', $otherStaff->karyawan_id_karyawan, 'berjalan');

        $this->actingAs($this->staff)
            ->getJson('/staff/pesan/'.$otherTask->id_tugas)
            ->assertNotFound();

        $this->actingAs($this->staff)
            ->postJson('/staff/pesan/send', [
                'tugas_id_tugas' => $otherTask->id_tugas,
                'judul_pesan' => 'Percobaan akses',
                'deskripsi' => 'Pesan ini tidak boleh disimpan.',
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('pesans', 0);
    }

    public function test_unauthenticated_requests_are_rejected_by_all_staff_endpoints(): void
    {
        $task = $this->createTask('TGS-AUTH', $this->staff->karyawan_id_karyawan, 'berjalan');

        $this->getJson('/staff/dashboard')->assertUnauthorized();
        $this->getJson('/staff/pesan')->assertUnauthorized();
        $this->getJson('/staff/pesan/'.$task->id_tugas)->assertUnauthorized();
        $this->postJson('/staff/pesan/send', [])->assertUnauthorized();
        $this->getJson('/staff/notifikasi')->assertUnauthorized();
    }

    public function test_non_staff_user_is_forbidden_from_staff_endpoints(): void
    {
        $this->actingAs($this->kadiv)
            ->getJson('/staff/dashboard')
            ->assertForbidden()
            ->assertJsonPath('message', 'Akses hanya diberikan kepada Staff.');

        $this->actingAs($this->kadiv)
            ->getJson('/staff/pesan')
            ->assertForbidden();

        $this->actingAs($this->kadiv)
            ->getJson('/staff/notifikasi')
            ->assertForbidden();
    }

    private function createAccount(string $employeeId, string $name, string $roleId, string $username): User2
    {
        Karyawan::create([
            'id_karyawan' => $employeeId,
            'nama' => $name,
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '2000-01-01',
            'tanggal_rekrut' => '2026-01-10',
            'no_telepon' => '081234567890',
            'email' => $username.'@example.test',
            'jabatan' => $name,
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        return User2::create([
            'id_user' => 'USR-'.$employeeId,
            'username' => $username,
            'password' => 'password',
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $employeeId,
            'last_login_at' => '2026-09-29 08:00:00',
        ]);
    }

    private function createTask(string $id, string $employeeId, string $status): Tugas
    {
        return Tugas::create([
            'id_tugas' => $id,
            'karyawan_id_karyawan' => $employeeId,
            'judul_tugas' => 'Tugas Dummy '.$id,
            'deskripsi' => 'Data dummy untuk pengujian endpoint.',
            'deadline' => '2026-10-31',
            'progress' => $status === 'selesai' ? '100' : '50',
            'status' => $status,
            'tanggal_dibuat' => '2026-09-20',
            'tanggal_update' => '2026-09-29',
        ]);
    }

    private function createMessage(string $id, Tugas $task, User2 $sender, string $title): Pesan
    {
        return Pesan::create([
            'id_pesan' => $id,
            'judul_pesan' => $title,
            'deskripsi' => 'Isi pesan dummy.',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-09-29',
            'tugas_id_tugas' => $task->id_tugas,
            'tugas_karyawan_id_karyawan' => $task->karyawan_id_karyawan,
            'pengirim_id_user' => $sender->id_user,
            'penerima_id_user' => $this->staff->id_user,
        ]);
    }
}
