<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    private function createHrd(string $username = 'hrduser'): User2
    {
        $karyawan = Karyawan::create([
            'id_karyawan' => 'KRY-HRD',
            'nama' => 'HRD Dummy',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1990-01-01',
            'tanggal_rekrut' => '2023-01-01',
            'no_telepon' => '081234567890',
            'jabatan' => 'HRD',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        return User2::create([
            'id_user' => 'USR-HRD',
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-HRD',
            'karyawan_id_karyawan' => $karyawan->id_karyawan,
        ]);
    }

    public function test_hrd_rejects_duplicate_divisi_kode(): void
    {
        $hrd = $this->createHrd();

        $this->actingAs($hrd)
            ->postJson('/api/hrd/divisi', [
                'kode_divisi' => 'IT',
                'nama_divisi' => 'Divisi Baru',
            ])
            ->assertUnprocessable()
            ->assertJsonStructure(['data' => ['kode_divisi']]);
    }

    public function test_hrd_rejects_duplicate_divisi_nama(): void
    {
        $hrd = $this->createHrd();

        $this->actingAs($hrd)
            ->postJson('/api/hrd/divisi', [
                'kode_divisi' => 'IT2',
                'nama_divisi' => 'Teknologi Informasi',
            ])
            ->assertUnprocessable()
            ->assertJsonStructure(['data' => ['nama_divisi']]);
    }

    public function test_hrd_creates_staff_without_email(): void
    {
        $hrd = $this->createHrd();

        $this->actingAs($hrd)
            ->postJson('/api/hrd/staff', [
                'nama' => 'John Doe',
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_lahir' => '1995-05-05',
                'no_telepon' => '081234567890',
                'jabatan' => 'Backend Developer',
                'divisi_id_divisi' => 'DIV-IT',
                'username' => 'johndoe',
                'password' => 'rahasia123',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('users2', ['username' => 'johndoe']);
    }

    public function test_hrd_rejects_duplicate_username(): void
    {
        $hrd = $this->createHrd();

        $payload = [
            'nama' => 'John Doe',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1995-05-05',
            'no_telepon' => '081234567890',
            'jabatan' => 'Backend Developer',
            'divisi_id_divisi' => 'DIV-IT',
            'username' => 'johndoe',
            'password' => 'rahasia123',
        ];

        $this->actingAs($hrd)->postJson('/api/hrd/staff', $payload)->assertCreated();

        $this->actingAs($hrd)
            ->postJson('/api/hrd/staff', $payload)
            ->assertUnprocessable()
            ->assertJsonStructure(['data' => ['username']]);
    }

    public function test_hrd_rejects_php_upload(): void
    {
        $hrd = $this->createHrd();
        $staff = $this->createStaff('KRY-S1', 'staffsatu');

        $this->actingAs($hrd)
            ->postJson('/api/hrd/pesan', [
                'penerima_id_user' => $staff->id_user,
                'judul_pesan' => 'Lampiran',
                'deskripsi' => 'Lihat lampiran.',
                'tipe' => 'pesan',
                'file_lampiran' => UploadedFile::fake()->create('evil.php', 10, 'application/x-php'),
            ])
            ->assertUnprocessable()
            ->assertJsonStructure(['data' => ['file_lampiran']]);
    }

    public function test_hrd_accepts_pdf_upload(): void
    {
        $hrd = $this->createHrd();
        $staff = $this->createStaff('KRY-S1', 'staffsatu');

        $response = $this->actingAs($hrd)
            ->post('/api/hrd/pesan', [
                'penerima_id_user' => $staff->id_user,
                'judul_pesan' => 'Lampiran',
                'deskripsi' => 'Lihat lampiran.',
                'tipe' => 'pesan',
                'file_lampiran' => UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'),
            ])
            ->assertCreated();

        // A 201 alone would also be returned when the file is ignored
        // (file_lampiran is nullable); prove the upload was actually stored.
        $this->assertNotEmpty(
            $response->json('data.file_lampiran'),
            'Accepted upload should be persisted and returned.'
        );
    }

    public function test_hrd_rejects_javascript_link(): void
    {
        $hrd = $this->createHrd();
        $staff = $this->createStaff('KRY-S1', 'staffsatu');

        $this->actingAs($hrd)
            ->postJson('/api/hrd/pesan', [
                'penerima_id_user' => $staff->id_user,
                'judul_pesan' => 'Link',
                'deskripsi' => 'Klik tautan.',
                'tipe' => 'pesan',
                'link_lampiran' => 'javascript:alert(1)',
            ])
            ->assertUnprocessable()
            ->assertJsonStructure(['data' => ['link_lampiran']]);
    }

    public function test_staff_submit_rejects_javascript_link(): void
    {
        $staff = $this->createStaff('KRY-S1', 'staffsatu');
        $tugas = Tugas::create([
            'id_tugas' => 'TG001',
            'karyawan_id_karyawan' => $staff->karyawan_id_karyawan,
            'judul_tugas' => 'Bikin API',
            'deskripsi' => 'Buat backend.',
            'deadline' => now()->addDays(3)->toDateString(),
            'progress' => '0',
            'status' => Tugas::STATUS_BARU,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);

        $this->actingAs($staff)
            ->postJson("/api/staff/tugas/{$tugas->id_tugas}/submit", [
                'link_submit' => 'javascript:alert(1)',
                'progress' => 50,
            ])
            ->assertUnprocessable()
            ->assertJsonStructure(['data' => ['link_submit']]);
    }

    public function test_login_rejects_wrong_password_without_leaking_user(): void
    {
        $this->createStaff('KRY-S1', 'staffsatu');

        $this->postJson('/api/login', [
            'username' => 'staffsatu',
            'password' => 'salah',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Username atau password salah');
    }

    public function test_login_rejects_unknown_username_without_leaking_user(): void
    {
        $this->createStaff('KRY-S1', 'staffsatu');

        // Same status and body as a wrong password for an existing user:
        // otherwise the response tells an attacker which usernames exist.
        $this->postJson('/api/login', [
            'username' => 'ghostuser',
            'password' => 'pass123',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Username atau password salah');
    }

    public function test_login_is_throttled_after_repeated_failures(): void
    {
        $this->createStaff('KRY-S1', 'staffsatu');

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'username' => 'staffsatu',
                'password' => 'salah',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/login', [
            'username' => 'staffsatu',
            'password' => 'salah',
        ])->assertStatus(429);
    }

    public function test_successful_logins_do_not_consume_throttle_budget(): void
    {
        $this->createStaff('KRY-S1', 'staffsatu');

        // Six consecutive successful logins must all succeed: success clears
        // the attempt budget instead of consuming it.
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/login', [
                'username' => 'staffsatu',
                'password' => 'pass123',
            ])->assertOk();
        }
    }

    public function test_login_throttle_is_scoped_per_username(): void
    {
        $this->createStaff('KRY-S1', 'staffsatu');
        $this->createStaff('KRY-S2', 'staffdua');

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'username' => 'staffsatu',
                'password' => 'salah',
            ])->assertUnauthorized();
        }

        // Another username on the same IP still gets its own budget.
        $this->postJson('/api/login', [
            'username' => 'staffdua',
            'password' => 'salah',
        ])->assertUnauthorized();
    }

    public function test_login_rejects_plaintext_stored_password(): void
    {
        $this->createStaff('KRY-S1', 'staffsatu');
        DB::table('users2')
            ->where('username', 'staffsatu')
            ->update(['password' => 'pass123']);

        $this->postJson('/api/login', [
            'username' => 'staffsatu',
            'password' => 'pass123',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_rejects_dotted_username_format(): void
    {
        // Contract: login usernames are dotless (`alpha_num:ascii`). Even a
        // dotted account that already exists must not be able to log in.
        DB::table('karyawans')->insert([
            'id_karyawan' => 'KRY-DOT',
            'nama' => 'Staff Dot',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '2000-01-01',
            'tanggal_rekrut' => '2026-01-10',
            'no_telepon' => '081234567890',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users2')->insert([
            'id_user' => 'USR-DOT',
            'username' => 'john.doe',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => 'KRY-DOT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/login', [
            'username' => 'john.doe',
            'password' => 'pass123',
        ])
            ->assertUnprocessable()
            ->assertJsonStructure(['data' => ['username']]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    private function createStaff(string $employeeId, string $username): User2
    {
        Karyawan::create([
            'id_karyawan' => $employeeId,
            'nama' => 'Staff Dummy',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '2000-01-01',
            'tanggal_rekrut' => '2026-01-10',
            'no_telepon' => '081234567890',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        return User2::create([
            'id_user' => 'USR-'.$employeeId,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => $employeeId,
        ]);
    }
}
