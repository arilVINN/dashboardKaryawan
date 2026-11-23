<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoFallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([['id_role' => 'RL-ST', 'nama_role' => 'Staff']]);
        Divisi::create([
            'id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT',
            'nama_divisi' => 'IT', 'status_aktif' => 'Aktif',
        ]);
        Karyawan::create([
            'id_karyawan' => 'K-1', 'nama' => 'User', 'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff', 'divisi_id_divisi' => 'DIV-IT',
        ]);
        $user = User2::create([
            'id_user' => 'U-1', 'username' => 'user', 'password' => Hash::make('x'),
            'role_id_role' => 'RL-ST', 'karyawan_id_karyawan' => 'K-1',
        ]);
        $this->actingAs($user);
    }

    public function test_legacy_private_photo_is_served_at_the_public_storage_url(): void
    {
        Storage::disk('private')->put('foto-profil/legacy.jpg', 'legacy-image-bytes');

        $response = $this->get('/storage/foto-profil/legacy.jpg');

        $response->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');
        $this->assertSame('legacy-image-bytes', $response->streamedContent());
    }

    public function test_public_photo_is_still_served(): void
    {
        Storage::disk('public')->put('foto-profil/current.png', 'current-image-bytes');

        $response = $this->get('/storage/foto-profil/current.png');

        $response->assertOk()
            ->assertHeader('Content-Type', 'image/png');
        $this->assertSame('current-image-bytes', $response->streamedContent());
    }

    public function test_missing_photo_returns_not_found(): void
    {
        $this->get('/storage/foto-profil/missing.jpg')->assertNotFound();
    }

    public function test_path_traversal_is_rejected(): void
    {
        $response = $this->get('/storage/foto-profil/..%2Fsecret.jpg');
        $this->assertContains($response->getStatusCode(), [403, 404]);
    }
}
