<?php

namespace Tests\Feature;

use App\Events\PesanDikirim;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\SubmitTugas;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class KadivApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([PesanDikirim::class]);

        Role::insert([
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
        ]);
        Divisi::insert([
            ['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi', 'status_aktif' => 'aktif'],
            ['id_divisi' => 'DIV-HR', 'kode_divisi' => 'HR', 'nama_divisi' => 'Human Resource', 'status_aktif' => 'aktif'],
        ]);
    }

    public function test_dashboard_returns_division_metrics_and_staff_summary(): void
    {
        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $staff = $this->createAccount('ST-1', 'Staff IT', 'DIV-IT', 'ROLE-STAFF', 'staff.it', '2026-09-29 08:30:00');
        $outsideStaff = $this->createAccount('ST-2', 'Staff HR', 'DIV-HR', 'ROLE-STAFF', 'staff.hr');

        $notSubmitted = $this->createTask('T-1', $staff->karyawan_id_karyawan);
        $waiting = $this->createTask('T-2', $staff->karyawan_id_karyawan);
        $approved = $this->createTask('T-3', $staff->karyawan_id_karyawan);
        $this->createTask('T-OUT', $outsideStaff->karyawan_id_karyawan);

        $this->createSubmission('SUB-1', $waiting, 'menunggu');
        $this->createSubmission('SUB-2', $approved, 'ACC');

        $this->actingAs($kadiv)
            ->getJson('/api/v1/kadiv/dashboard')
            ->assertOk()
            ->assertJsonPath('data.metrics.tugas_belum_dikirim', 1)
            ->assertJsonPath('data.metrics.tugas_belum_di_acc', 1)
            ->assertJsonPath('data.metrics.tugas_sudah_di_acc', 1)
            ->assertJsonPath('data.metrics.persentase_penyelesaian', 33.33)
            ->assertJsonCount(1, 'data.staff')
            ->assertJsonPath('data.staff.0.nama', 'Staff IT')
            ->assertJsonPath('data.staff.0.jumlah_tugas_dikerjakan', 3);
    }

    public function test_kadiv_can_send_and_list_a_letter_to_hrd(): void
    {
        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $hrd = $this->createAccount('HR-1', 'HRD', 'DIV-HR', 'ROLE-HRD', 'hrd');

        $this->actingAs($kadiv)
            ->postJson('/api/v1/kadiv/pesan', [
                'penerima_id_user' => $hrd->id_user,
                'tipe' => 'surat',
                'judul_pesan' => 'Permintaan Rekrutmen',
                'deskripsi' => 'Mohon tindak lanjut.',
                'link_lampiran' => 'https://example.com/dokumen',
            ])
            ->assertCreated()
            ->assertJsonPath('data.tipe', 'surat')
            ->assertJsonPath('data.arah', 'keluar')
            ->assertJsonPath('data.penerima.id_user', $hrd->id_user);

        $this->assertDatabaseHas('pesans', [
            'pengirim_id_user' => $kadiv->id_user,
            'penerima_id_user' => $hrd->id_user,
            'tipe' => 'surat',
        ]);

        $this->actingAs($kadiv)
            ->getJson('/api/v1/kadiv/pesan?tipe=surat&arah=keluar')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_kadiv_cannot_send_to_staff_from_another_division(): void
    {
        $kadiv = $this->createAccount('KD-1', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $outsideStaff = $this->createAccount('ST-2', 'Staff HR', 'DIV-HR', 'ROLE-STAFF', 'staff.hr');

        $this->actingAs($kadiv)
            ->postJson('/api/v1/kadiv/pesan', [
                'penerima_id_user' => $outsideStaff->id_user,
                'tipe' => 'pesan',
                'judul_pesan' => 'Tidak boleh terkirim',
                'deskripsi' => 'Lintas divisi.',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Penerima harus Staff di divisi Anda atau HRD.');

        $this->assertDatabaseCount('pesans', 0);
    }

    public function test_non_kadiv_is_forbidden(): void
    {
        $staff = $this->createAccount('ST-1', 'Staff IT', 'DIV-IT', 'ROLE-STAFF', 'staff.it');

        $this->actingAs($staff)
            ->getJson('/api/v1/kadiv/dashboard')
            ->assertForbidden();
    }

    private function createAccount(
        string $employeeId,
        string $name,
        string $divisionId,
        string $roleId,
        string $username,
        ?string $lastLoginAt = null,
    ): User2 {
        Karyawan::create([
            'id_karyawan' => $employeeId,
            'nama' => $name,
            'jenis_kelamin' => 'Laki-laki',
            'email' => $username.'@example.com',
            'jabatan' => $name,
            'divisi_id_divisi' => $divisionId,
        ]);

        return User2::create([
            'id_user' => 'USR-'.$employeeId,
            'username' => $username,
            'password' => 'password',
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $employeeId,
            'last_login_at' => $lastLoginAt,
        ]);
    }

    private function createTask(string $id, string $employeeId): Tugas
    {
        return Tugas::create([
            'id_tugas' => $id,
            'karyawan_id_karyawan' => $employeeId,
            'judul_tugas' => 'Tugas '.$id,
            'status' => 'berjalan',
            'tanggal_dibuat' => '2026-09-29',
        ]);
    }

    private function createSubmission(string $id, Tugas $task, string $reviewStatus): SubmitTugas
    {
        return SubmitTugas::create([
            'id_submit_tugas' => $id,
            'status_review' => $reviewStatus,
            'tugas_id_tugas' => $task->id_tugas,
            'tugas_karyawan_id_karyawan' => $task->karyawan_id_karyawan,
        ]);
    }
}
