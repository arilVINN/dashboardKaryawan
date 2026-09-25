<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Models\User2;
use App\Models\Tugas;

class TugasApiTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = \database\Seeders\DatabaseSeeder::class;

    #[Test]
    public function staff_bisa_login_menggunakan_username()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'budist',
            'password' => 'pass123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token', 'role']);
    }

    #[Test]
    public function staff_bisa_melihat_daftar_tugas()
    {
        $user = User2::where('username', 'budist')->first();

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/staff/tugas');

        $response->assertStatus(200)
                 ->assertJsonFragment(['judul_tugas' => 'Bikin API']);
    }

    #[Test]
    public function staff_bisa_submit_tugas()
    {
        $user = User2::where('username', 'budist')->first();
        $tugas = Tugas::where('karyawan_id_karyawan', $user->karyawan_id_karyawan)->first();

        Storage::fake('public');
        $file = UploadedFile::fake()->create('dokumen.pdf', 10);

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson("/api/staff/tugas/{$tugas->id_tugas}/submit", [
                             'file_hasil' => $file,
                             'catatan_karyawan' => 'Selesai',
                             'progress' => 100
                         ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('submit_tugas', [
            'tugas_id_tugas' => $tugas->id_tugas,
            'catatan_revisi' => '-', 
            'link_submit' => '-',
            'status_review' => 'submitted'
        ]);
    }
        #[Test]
    public function staff_hanya_bisa_melihat_tugas_sendiri()
    {
        // 1. Ambil Staff A (Budi) yang sudah punya tugas "Bikin API" dari Seeder
        $staffA = User2::where('username', 'budist')->first();
        $tugasA = Tugas::where('karyawan_id_karyawan', $staffA->karyawan_id_karyawan)->first();

        // 2. Kita buat Staff B yang SATU DIVISI dengan Budi
        $karyawanB = \App\Models\Karyawan::create([
            'id_karyawan' => 'KR002',
            'nama' => 'Joko (Teman Divisi)',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1996-01-01',
            'tanggal_rekrut' => '2023-01-10',
            'no_telepon' => '08111',
            'email' => 'joko@mail.com',
            'jabatan' => 'Frontend',
            'divisi_id_divisi' => $staffA->karyawan->divisi_id_divisi, // Satu divisi!
        ]);
        $staffB = User2::create([
            'id_user' => 'US002',
            'username' => 'jokost',
            'password' => 'pass123',
            'role_id_role' => $staffA->role_id_role,
            'karyawan_id_karyawan' => $karyawanB->id_karyawan,
        ]);

        // ============================================
        // PENGUJIAN: Joko mencoba melihat daftar tugas
        // ============================================
        $responseList = $this->actingAs($staffB, 'sanctum')->getJson('/api/staff/tugas');
        
        // Joko harus mendapat array KOSONG, karena dia tidak punya tugas (Tugas A tidak ikut terbaca)
        $responseList->assertStatus(200)->assertExactJson([
            'message' => 'Daftar tugas Anda',
            'data' => []
        ]);

        // ============================================
        // PENGUJIAN: Joko mencoba mengintip detail tugas si Budi via ID
        // ============================================
        $responseDetail = $this->actingAs($staffB, 'sanctum')->getJson("/api/staff/tugas/{$tugasA->id_tugas}");
        
        // Sistem harus memblokir Joko dengan status 404 (Not Found)
        $responseDetail->assertStatus(404)->assertJson(['message' => 'Tugas tidak ditemukan']);
    }
}