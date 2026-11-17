<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;

    private User2 $kadiv;

    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
        ]);
        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'status_aktif' => 'aktif',
        ]);

        $this->staff = $this->createAccount('KRY-STAFF', 'ROLE-STAFF', 'stafffoto');
        $this->kadiv = $this->createAccount('KRY-KADIV', 'ROLE-KADIV', 'kadivfoto');
        $this->hrd = $this->createAccount('KRY-HRD', 'ROLE-HRD', 'hrdfoto');
    }

    private function createAccount(string $employeeId, string $roleId, string $username): User2
    {
        Karyawan::create([
            'id_karyawan' => $employeeId,
            'nama' => 'Pemilik '.$username,
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        return User2::create([
            'id_user' => 'USR-'.$employeeId,
            'username' => $username,
            'password' => 'password',
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $employeeId,
        ]);
    }

    public function test_staff_can_upload_profile_photo(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->staff)
            ->postJson('/api/staff/profile/foto', [
                'foto' => UploadedFile::fake()->image('foto.jpg'),
            ]);

        $response->assertOk()->assertJsonStructure(['message', 'foto_url']);

        $path = Karyawan::find('KRY-STAFF')->foto;
        $this->assertNotNull($path);
        $this->assertStringStartsWith('foto-profil/', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertSame('/storage/'.$path, $response->json('foto_url'));
    }

    public function test_show_returns_null_foto_url_without_photo(): void
    {
        $this->actingAs($this->staff)
            ->getJson('/api/staff/profile')
            ->assertOk()
            ->assertJsonPath('data.foto_url', null);
    }

    public function test_show_returns_foto_url_after_upload(): void
    {
        Storage::fake('public');

        $this->actingAs($this->staff)
            ->postJson('/api/staff/profile/foto', [
                'foto' => UploadedFile::fake()->image('foto.jpg'),
            ])
            ->assertOk();

        $this->actingAs($this->staff)
            ->getJson('/api/staff/profile')
            ->assertOk()
            ->assertJsonPath('data.foto_url', '/storage/'.Karyawan::find('KRY-STAFF')->foto);
    }

    public function test_staff_profile_update_rejects_an_email_used_by_another_employee(): void
    {
        Karyawan::where('id_karyawan', 'KRY-KADIV')
            ->update(['email' => 'used@example.test']);

        $response = $this->actingAs($this->staff)
            ->putJson('/api/staff/profile', [
                'nama' => 'Nama Baru',
                'email' => 'used@example.test',
                'no_telepon' => '081234567890',
                'alamat' => 'Alamat baru',
            ])
            ->assertUnprocessable();

        $this->assertArrayHasKey('email', $response->json('data'));

        $this->assertDatabaseHas('karyawans', [
            'id_karyawan' => 'KRY-STAFF',
            'nama' => 'Pemilik stafffoto',
        ]);
    }

    public function test_rejects_non_image_file(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->staff)
            ->postJson('/api/staff/profile/foto', [
                'foto' => UploadedFile::fake()->create('dokumen.txt', 100, 'text/plain'),
            ]);

        $response->assertUnprocessable();
        $this->assertArrayHasKey('foto', $response->json('data'));

        $this->assertNull(Karyawan::find('KRY-STAFF')->foto);
    }

    public function test_rejects_oversized_image(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->staff)
            ->postJson('/api/staff/profile/foto', [
                'foto' => UploadedFile::fake()->image('besar.jpg')->size(3000),
            ]);

        $response->assertUnprocessable();
        $this->assertArrayHasKey('foto', $response->json('data'));

        $this->assertNull(Karyawan::find('KRY-STAFF')->foto);
    }

    public function test_replacing_photo_deletes_previous_file(): void
    {
        Storage::fake('public');

        $this->actingAs($this->staff)
            ->postJson('/api/staff/profile/foto', [
                'foto' => UploadedFile::fake()->image('lama.jpg'),
            ])
            ->assertOk();
        $oldPath = Karyawan::find('KRY-STAFF')->foto;

        $this->actingAs($this->staff)
            ->postJson('/api/staff/profile/foto', [
                'foto' => UploadedFile::fake()->image('baru.jpg'),
            ])
            ->assertOk();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists(Karyawan::find('KRY-STAFF')->foto);
    }

    public function test_kadiv_can_upload_profile_photo(): void
    {
        Storage::fake('public');

        $this->actingAs($this->kadiv)
            ->postJson('/api/kadiv/profile/foto', [
                'foto' => UploadedFile::fake()->image('foto.jpg'),
            ])
            ->assertOk()
            ->assertJsonStructure(['message', 'foto_url']);

        $this->assertNotNull(Karyawan::find('KRY-KADIV')->foto);
    }

    public function test_hrd_can_upload_profile_photo(): void
    {
        Storage::fake('public');

        $this->actingAs($this->hrd)
            ->postJson('/api/hrd/profile/foto', [
                'foto' => UploadedFile::fake()->image('foto.jpg'),
            ])
            ->assertOk()
            ->assertJsonStructure(['message', 'foto_url']);

        $this->assertNotNull(Karyawan::find('KRY-HRD')->foto);
    }

    public function test_staff_profile_page_offers_photo_upload(): void
    {
        $this->actingAs($this->staff)
            ->get('/profile')
            ->assertOk()
            ->assertSee('id="input-foto"', false)
            ->assertSee('id="prof-foto"', false)
            ->assertSee('/api/staff/profile/foto', false);
    }

    public function test_kadiv_profile_page_offers_photo_upload(): void
    {
        $this->actingAs($this->kadiv)
            ->get('/kadiv/profile')
            ->assertOk()
            ->assertSee('id="input-foto"', false)
            ->assertSee('id="prof-foto"', false)
            ->assertSee('/api/kadiv/profile/foto', false);
    }

    public function test_hrd_profile_page_offers_photo_upload(): void
    {
        $this->actingAs($this->hrd)
            ->get('/hrd/profile')
            ->assertOk()
            ->assertSee('id="input-foto"', false)
            ->assertSee('id="prof-foto"', false)
            ->assertSee('/api/hrd/profile/foto', false);
    }

    public function test_kadiv_and_hrd_can_edit_their_own_profile_data(): void
    {
        foreach ([
            [$this->kadiv, '/api/kadiv/profile', 'KRY-KADIV'],
            [$this->hrd, '/api/hrd/profile', 'KRY-HRD'],
        ] as [$account, $endpoint, $employeeId]) {
            $this->actingAs($account)
                ->putJson($endpoint, [
                    'nama' => 'Nama Pribadi Baru',
                    'email' => strtolower($employeeId).'@example.test',
                    'no_telepon' => '081234567890',
                    'alamat' => 'Alamat Pribadi Baru',
                    'no_rekening' => 'BCA 123456',
                    'jabatan' => 'Admin',
                    'tanggal_masuk' => '2025-01-01',
                ])
                ->assertOk()
                ->assertJsonPath('message', 'Profil berhasil diperbarui.');

            $this->assertDatabaseHas('karyawans', [
                'id_karyawan' => $employeeId,
                'nama' => 'Nama Pribadi Baru',
                'alamat' => 'Alamat Pribadi Baru',
                'jabatan' => 'Staff',
                'tanggal_masuk' => null,
            ]);
        }

        $this->assertDatabaseHas('karyawans', [
            'id_karyawan' => 'KRY-STAFF',
            'nama' => 'Pemilik stafffoto',
        ]);
    }

    public function test_hrd_can_change_their_own_password(): void
    {
        $this->hrd->update([
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);

        $this->actingAs($this->hrd)
            ->putJson('/api/hrd/profile/password', [
                'current_password' => 'password',
                'new_password' => 'password-baru',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Password berhasil diperbarui.');
    }

    public function test_profile_pages_share_personal_fields_and_edit_controls(): void
    {
        foreach ([
            [$this->staff, '/profile'],
            [$this->kadiv, '/kadiv/profile'],
            [$this->hrd, '/hrd/profile'],
        ] as [$account, $path]) {
            $this->actingAs($account)
                ->get($path)
                ->assertOk()
                ->assertSee('id="prof-id"', false)
                ->assertSee('id="prof-role"', false)
                ->assertSee('id="prof-status-aktif"', false)
                ->assertSee('id="form-edit-profil"', false)
                ->assertSee('id="input-rekening"', false)
                ->assertSee('id="form-password"', false);
        }
    }

    public function test_topbar_shows_profile_photo_when_set(): void
    {
        Karyawan::where('id_karyawan', 'KRY-STAFF')->update(['foto' => 'foto-profil/test.jpg']);

        $this->actingAs($this->staff)
            ->get('/profile')
            ->assertOk()
            ->assertSee('id="topbar-foto"', false)
            ->assertSee('src="/storage/foto-profil/test.jpg"', false)
            ->assertSee('profile.foto_url', false);
    }

    public function test_topbar_falls_back_to_initial_without_photo(): void
    {
        $this->actingAs($this->staff)
            ->get('/profile')
            ->assertOk()
            ->assertSee('id="topbar-inisial"', false)
            ->assertSee('id="topbar-foto"', false)
            ->assertDontSee('src="/storage/', false);
    }

    public function test_kadiv_topbar_shows_profile_photo_when_set(): void
    {
        Karyawan::where('id_karyawan', 'KRY-KADIV')->update(['foto' => 'foto-profil/kadiv.jpg']);

        $this->actingAs($this->kadiv)
            ->get('/kadiv/dashboard')
            ->assertOk()
            ->assertSee('id="topbar-foto"', false)
            ->assertSee('src="/storage/foto-profil/kadiv.jpg"', false)
            ->assertSee('profile.foto_url', false);
    }

    public function test_kadiv_topbar_falls_back_to_initial_without_photo(): void
    {
        $this->actingAs($this->kadiv)
            ->get('/kadiv/dashboard')
            ->assertOk()
            ->assertSee('id="topbar-inisial"', false)
            ->assertDontSee('src="/storage/', false);
    }
}
