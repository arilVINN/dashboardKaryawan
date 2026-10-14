<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HrdStaffPhotoTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;

    private User2 $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);
        Divisi::insert([
            ['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi', 'status_aktif' => 'aktif'],
            ['id_divisi' => 'DIV-EMPTY', 'kode_divisi' => 'EMP', 'nama_divisi' => 'Kosong', 'status_aktif' => 'aktif'],
        ]);

        $this->hrd = $this->createAccount('EMP-HRD', 'HRD', 'DIV-IT', 'ROLE-HRD', 'hrd');
        $this->staff = $this->createAccount('EMP-STAFF', 'Staff', 'DIV-EMPTY', 'ROLE-STAFF', 'staff');
    }

    private function createAccount(
        string $employeeId,
        string $name,
        string $divisionId,
        string $roleId,
        string $username,
    ): User2 {
        DB::table('karyawans')->insert([
            'id_karyawan' => $employeeId,
            'nama' => $name,
            'jenis_kelamin' => 'Laki-laki',
            'email' => $username.'@example.test',
            'jabatan' => $name,
            'divisi_id_divisi' => $divisionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User2::create([
            'id_user' => 'USR-'.$employeeId,
            'username' => $username,
            'password' => 'password',
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $employeeId,
        ]);
    }

    public function test_hrd_can_upload_staff_photo(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->hrd)
            ->postJson('/hrd/staff/EMP-STAFF/foto', [
                'foto' => UploadedFile::fake()->image('foto.jpg'),
            ]);

        $response->assertOk()->assertJsonStructure(['message', 'foto_url']);

        $path = Karyawan::find('EMP-STAFF')->foto;
        $this->assertNotNull($path);
        $this->assertStringStartsWith('foto-profil/', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertSame('/storage/'.$path, $response->json('foto_url'));
    }

    public function test_rejects_non_image_file(): void
    {
        Storage::fake('public');

        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff/EMP-STAFF/foto', [
                'foto' => UploadedFile::fake()->create('dokumen.txt', 100, 'text/plain'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('foto');

        $this->assertNull(Karyawan::find('EMP-STAFF')->foto);
    }

    public function test_rejects_oversized_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff/EMP-STAFF/foto', [
                'foto' => UploadedFile::fake()->image('besar.jpg')->size(3000),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('foto');

        $this->assertNull(Karyawan::find('EMP-STAFF')->foto);
    }

    public function test_replacing_photo_deletes_previous_file(): void
    {
        Storage::fake('public');

        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff/EMP-STAFF/foto', [
                'foto' => UploadedFile::fake()->image('lama.jpg'),
            ])
            ->assertOk();
        $oldPath = Karyawan::find('EMP-STAFF')->foto;

        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff/EMP-STAFF/foto', [
                'foto' => UploadedFile::fake()->image('baru.jpg'),
            ])
            ->assertOk();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists(Karyawan::find('EMP-STAFF')->foto);
    }

    public function test_returns_404_for_unknown_staff(): void
    {
        Storage::fake('public');

        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff/NOPE/foto', [
                'foto' => UploadedFile::fake()->image('foto.jpg'),
            ])
            ->assertNotFound();
    }

    public function test_non_hrd_is_forbidden(): void
    {
        Storage::fake('public');

        $this->actingAs($this->staff)
            ->postJson('/hrd/staff/EMP-STAFF/foto', [
                'foto' => UploadedFile::fake()->image('foto.jpg'),
            ])
            ->assertForbidden();
    }

    public function test_karyawan_modals_offer_photo_input(): void
    {
        $this->actingAs($this->hrd)
            ->get('/hrd/daftarKaryawan')
            ->assertOk()
            ->assertSee('id="input-foto-karyawan"', false)
            ->assertSee('id="input-foto-edit-karyawan"', false);

        $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->assertSee('id="input-foto-karyawan"', false)
            ->assertSee('id="input-foto-edit-karyawan"', false);
    }
}
