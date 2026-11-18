<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HrdStaffStoreTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'RL-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'RL-KADIV', 'nama_role' => 'kadiv'],
            ['id_role' => 'RL-HRD', 'nama_role' => 'hrd'],
        ]);

        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'tipe' => Divisi::TIPE_DIVISI,
            'status_aktif' => 'Aktif',
        ]);

        // Data lama/import dengan format ID yang tidak standar. Sebelumnya baris
        // ini membuat ID berikutnya selalu jatuh ke KR001/US001 dan bentrok.
        Karyawan::create([
            'id_karyawan' => 'KRY-CONTRACT-TEST',
            'nama' => 'Karyawan Lama',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
        ]);
        User2::create([
            'id_user' => 'USR-CONTRACT-TEST',
            'username' => 'karyawanlama',
            'password' => Hash::make('rahasia123'),
            'role_id_role' => 'RL-STAFF',
            'karyawan_id_karyawan' => 'KRY-CONTRACT-TEST',
        ]);

        foreach (['KR001', 'KR002', 'KR005'] as $index => $id) {
            Karyawan::create([
                'id_karyawan' => $id,
                'nama' => 'Karyawan '.$id,
                'jenis_kelamin' => 'Laki-laki',
                'jabatan' => 'Staff',
                'divisi_id_divisi' => 'DIV-IT',
            ]);
            User2::create([
                'id_user' => 'US'.str_pad((string) ([1, 2, 5][$index]), 3, '0', STR_PAD_LEFT),
                'username' => 'karyawan'.$id,
                'password' => Hash::make('rahasia123'),
                'role_id_role' => 'RL-STAFF',
                'karyawan_id_karyawan' => $id,
            ]);
        }

        Karyawan::create([
            'id_karyawan' => 'KRY-HRD',
            'nama' => 'HRD User',
            'jenis_kelamin' => 'Perempuan',
            'jabatan' => 'HRD Manager',
            'divisi_id_divisi' => 'DIV-IT',
        ]);
        $this->hrd = User2::create([
            'id_user' => 'USR-HRD',
            'username' => 'hrduser',
            'password' => Hash::make('rahasia123'),
            'role_id_role' => 'RL-HRD',
            'karyawan_id_karyawan' => 'KRY-HRD',
        ]);
    }

    public function test_new_employee_gets_the_next_sequential_ids_despite_legacy_ids(): void
    {
        $response = $this->actingAs($this->hrd)->postJson('/hrd/staff', $this->payload());

        $response->assertCreated()
            ->assertJsonPath('data.karyawan.id_karyawan', 'KR006')
            ->assertJsonPath('data.user.id_user', 'US006');

        $this->assertDatabaseHas('karyawans', [
            'id_karyawan' => 'KR006',
            'nama' => 'Budi Baru',
            'jabatan' => 'Staff',
        ]);
        $this->assertDatabaseHas('users2', [
            'id_user' => 'US006',
            'username' => 'budibaru',
            'role_id_role' => 'RL-STAFF',
            'karyawan_id_karyawan' => 'KR006',
        ]);
    }

    public function test_markom_jabatan_creates_a_staff_account(): void
    {
        $response = $this->actingAs($this->hrd)->postJson('/hrd/staff', $this->payload([
            'nama' => 'Markom Baru',
            'username' => 'markombaru',
            'jabatan' => 'Markom',
        ]));

        $response->assertCreated();

        $this->assertDatabaseHas('users2', [
            'username' => 'markombaru',
            'role_id_role' => 'RL-STAFF',
        ]);
    }

    public function test_layouter_jabatan_creates_a_staff_account(): void
    {
        Divisi::create([
            'id_divisi' => 'DIV-PB',
            'kode_divisi' => 'PB',
            'nama_divisi' => 'Publikasi Buku',
            'tipe' => Divisi::TIPE_DIVISI,
            'status_aktif' => 'Aktif',
        ]);

        $response = $this->actingAs($this->hrd)->postJson('/hrd/staff', $this->payload([
            'nama' => 'Layouter Baru',
            'username' => 'layouterbaru',
            'jabatan' => 'Layouter',
            'divisi_id_divisi' => 'DIV-PB',
        ]));

        $response->assertCreated();

        $this->assertDatabaseHas('karyawans', [
            'nama' => 'Layouter Baru',
            'jabatan' => 'Layouter',
            'divisi_id_divisi' => 'DIV-PB',
        ]);
        $this->assertDatabaseHas('users2', [
            'username' => 'layouterbaru',
            'role_id_role' => 'RL-STAFF',
        ]);
    }

    public function test_duplicate_username_returns_a_clear_message(): void
    {
        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', $this->payload(['username' => 'karyawanlama']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['username'])
            ->assertJsonPath('errors.username.0', 'Username sudah digunakan. Silakan pilih username lain.');
    }

    public function test_duplicate_email_returns_a_clear_message(): void
    {
        Karyawan::whereKey('KR001')->update(['email' => 'dipakai@example.test']);

        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', $this->payload(['email' => 'dipakai@example.test']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'Email sudah digunakan karyawan lain. Gunakan email lain atau kosongkan.');
    }

    public function test_missing_required_fields_return_indonesian_messages(): void
    {
        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nama', 'jenis_kelamin', 'jabatan', 'divisi_id_divisi', 'username', 'password'])
            ->assertJsonPath('errors.nama.0', 'Nama karyawan wajib diisi.')
            ->assertJsonPath('errors.jabatan.0', 'Jabatan wajib dipilih.')
            ->assertJsonPath('errors.divisi_id_divisi.0', 'Divisi wajib dipilih.');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nama' => 'Budi Baru',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
            'username' => 'budibaru',
            'password' => 'rahasia123',
        ], $overrides);
    }
}
