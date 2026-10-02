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
        $this->createTask('TGS-0', $this->staff->karyawan_id_karyawan, Tugas::STATUS_BERJALAN);
        $this->createTask('TGS-1', $this->staff->karyawan_id_karyawan, Tugas::STATUS_BARU);
        $this->createTask('TGS-2', $this->staff->karyawan_id_karyawan, Tugas::STATUS_MENUNGGU_ACC);
        // Di-ACC meski deadline lewat: tetap "sudah di-acc", bukan "telat".
        $this->createTask(
            'TGS-3',
            $this->staff->karyawan_id_karyawan,
            Tugas::STATUS_SUDAH_ACC,
            now()->subDays(5)->toDateString()
        );
        // Deadline lewat dan belum di-ACC: masuk bucket "telat".
        $this->createTask(
            'TGS-4',
            $this->staff->karyawan_id_karyawan,
            Tugas::STATUS_BERJALAN,
            now()->subDays(5)->toDateString()
        );

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
            ->assertJsonPath('data.statistik.tugas_berjalan', 1)
            ->assertJsonPath('data.statistik.tugas_baru', 1)
            ->assertJsonPath('data.statistik.tugas_menunggu_acc', 1)
            ->assertJsonPath('data.statistik.tugas_sudah_acc', 1)
            ->assertJsonPath('data.statistik.tugas_telat', 1)
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
        $token = $this->staff->createToken('staff-messages')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/staff/pesan')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id_tugas', 'TGS-OWN')
            ->assertJsonPath('data.0.jumlah_pesan', 2)
            ->assertJsonPath('data.0.pesan_terakhir.id_pesan', 'PSN-TWO');

        $this->withToken($token)
            ->getJson('/api/staff/pesan?tugas_id_tugas=TGS-OWN')
            ->assertOk()
            ->assertJsonPath('data.tugas.id_tugas', 'TGS-OWN')
            ->assertJsonCount(2, 'data.pesan')
            ->assertJsonPath('data.pesan.0.id_pesan', 'PSN-ONE')
            ->assertJsonPath('data.pesan.1.id_pesan', 'PSN-TWO');
    }

    public function test_staff_cannot_create_new_messages(): void
    {
        $task = $this->createTask('TGS-SEND', $this->staff->karyawan_id_karyawan, 'berjalan');

        $token = $this->staff->createToken('staff-send-message')->plainTextToken;
        $this->withToken($token)->postJson('/api/staff/pesan', [
            'tugas_id_tugas' => $task->id_tugas,
            'judul_pesan' => 'Update Dummy',
            'deskripsi' => 'Pekerjaan sudah mencapai tahap pengujian.',
        ])->assertMethodNotAllowed();

        $this->assertDatabaseCount('pesans', 0);
    }

    public function test_login_rejects_non_alphanumeric_username(): void
    {
        $this->postJson('/api/login', [
            'username' => "' OR 1=1 --",
            'password' => 'password',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseHas('users2', [
            'id_user' => $this->staff->id_user,
            'last_login_at' => '2026-09-29 08:00:00',
        ]);
    }

    public function test_staff_can_see_direct_messages_sent_to_them(): void
    {
        Pesan::create([
            'id_pesan' => 'PSN-DIRECT',
            'judul_pesan' => 'Pesan langsung',
            'deskripsi' => 'Pesan tanpa tugas.',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-09-30',
            'pengirim_id_user' => $this->kadiv->id_user,
            'penerima_id_user' => $this->staff->id_user,
        ]);

        Pesan::create([
            'id_pesan' => 'PSN-OTHER',
            'judul_pesan' => 'Pesan untuk orang lain',
            'deskripsi' => 'Pesan privat.',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-09-30',
            'pengirim_id_user' => $this->kadiv->id_user,
            'penerima_id_user' => $this->kadiv->id_user,
        ]);

        $token = $this->staff->createToken('staff-direct-messages')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/staff/pesan')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonCount(1, 'pesan_langsung')
            ->assertJsonPath('pesan_langsung.0.id_pesan', 'PSN-DIRECT')
            ->assertJsonPath('pesan_langsung.0.arah', 'masuk')
            ->assertJsonPath('pesan_langsung.0.pengirim.nama', 'Kadiv Dummy');

            $this->withToken($token)
                ->getJson('/api/staff/pesan/PSN-DIRECT')
                ->assertOk()
                ->assertJsonPath('data.id_pesan', 'PSN-DIRECT')
                ->assertJsonPath('data.penerima.id_user', $this->staff->id_user);

            $this->withToken($token)
                ->getJson('/api/staff/pesan/PSN-OTHER')
                ->assertNotFound();
            }

    public function test_staff_can_reply_to_messages_but_not_letters(): void
    {
        Pesan::create([
            'id_pesan' => 'PSN-REPLY',
            'judul_pesan' => 'Pembaruan pekerjaan',
            'deskripsi' => 'Bagaimana progres tugasnya?',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-09-30',
            'pengirim_id_user' => $this->kadiv->id_user,
            'penerima_id_user' => $this->staff->id_user,
        ]);
        Pesan::create([
            'id_pesan' => 'PSN-LETTER',
            'judul_pesan' => 'Surat resmi',
            'deskripsi' => 'Surat untuk staff.',
            'tipe' => 'surat',
            'tanggal_pesan' => '2026-09-30',
            'pengirim_id_user' => $this->kadiv->id_user,
            'penerima_id_user' => $this->staff->id_user,
        ]);

        $token = $this->staff->createToken('staff-reply')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/staff/pesan/PSN-REPLY')
            ->assertOk()
            ->assertJsonPath('data.can_reply', true);

        $replyResponse = $this->withToken($token)
            ->postJson('/api/staff/pesan/PSN-REPLY/balas', [
                'deskripsi' => 'Progres sudah 80 persen.',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Balasan berhasil dikirim.');

        $this->assertDatabaseHas('pesans', [
            'id_pesan' => $replyResponse->json('data.id_pesan'),
            'balasan_dari_id_pesan' => 'PSN-REPLY',
            'pengirim_id_user' => $this->staff->id_user,
            'penerima_id_user' => $this->kadiv->id_user,
        ]);

        $this->withToken($token)
            ->getJson('/api/staff/pesan/PSN-REPLY')
            ->assertOk()
            ->assertJsonCount(1, 'data.balasan')
            ->assertJsonPath('data.balasan.0.deskripsi', 'Progres sudah 80 persen.');

        $this->withToken($token)
            ->getJson('/api/staff/pesan/PSN-LETTER')
            ->assertOk()
            ->assertJsonPath('data.can_reply', false);

        $this->withToken($token)
            ->postJson('/api/staff/pesan/PSN-LETTER/balas', [
                'deskripsi' => 'Mencoba membalas surat.',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Surat tidak dapat dibalas.');
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

    public function test_staff_cannot_open_another_staff_task_thread(): void
    {
        $otherStaff = $this->createAccount('KRY-OTHER', 'Staff Lain', 'ROLE-STAFF', 'staff.other');
        $otherTask = $this->createTask('TGS-OTHER', $otherStaff->karyawan_id_karyawan, 'berjalan');

        $this->actingAs($this->staff)
            ->getJson('/api/staff/pesan?tugas_id_tugas='.$otherTask->id_tugas)
            ->assertNotFound();

        $this->assertDatabaseCount('pesans', 0);
    }

    public function test_unauthenticated_requests_are_rejected_by_all_staff_endpoints(): void
    {
        $task = $this->createTask('TGS-AUTH', $this->staff->karyawan_id_karyawan, 'berjalan');

        $this->getJson('/staff/dashboard')->assertUnauthorized();
        $this->getJson('/api/staff/pesan')->assertUnauthorized();
        $this->getJson('/api/staff/pesan?tugas_id_tugas='.$task->id_tugas)->assertUnauthorized();
        $this->getJson('/api/staff/pesan/PSN-UNKNOWN')->assertUnauthorized();
        $this->getJson('/staff/notifikasi')->assertUnauthorized();
    }

    public function test_non_staff_user_is_forbidden_from_staff_endpoints(): void
    {
        $this->actingAs($this->kadiv)
            ->getJson('/staff/dashboard')
            ->assertForbidden()
            ->assertJsonPath('message', 'Akses hanya diberikan kepada Staff.');

        $this->actingAs($this->kadiv)
            ->getJson('/api/staff/pesan')
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

    private function createTask(string $id, string $employeeId, string $status, ?string $deadline = null): Tugas
    {
        return Tugas::create([
            'id_tugas' => $id,
            'karyawan_id_karyawan' => $employeeId,
            'judul_tugas' => 'Tugas Dummy '.$id,
            'deskripsi' => 'Data dummy untuk pengujian endpoint.',
            'deadline' => $deadline ?? now()->addDays(30)->toDateString(),
            'progress' => $status === Tugas::STATUS_SUDAH_ACC ? '100' : '50',
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
