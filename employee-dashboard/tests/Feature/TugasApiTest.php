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

    protected function setUp(): void
    {
        parent::setUp();

        // Seed explicitly: the $seeder property only runs during migrate:fresh,
        // which happens at most once per test process (other test classes may
        // migrate first), so depending on it makes this test order-dependent.
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

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

        // Submit 100% → tugas berpindah ke status "menunggu di-acc"
        $this->assertDatabaseHas('tugas', [
            'id_tugas' => $tugas->id_tugas,
            'status' => Tugas::STATUS_MENUNGGU_ACC,
        ]);
    }

    #[Test]
    public function kadiv_bisa_acc_tugas_dan_tidak_bisa_acc_dua_kali()
    {
        $staff = User2::where('username', 'budist')->first();
        $kadiv = User2::where('username', 'tonokd')->first();
        $tugas = Tugas::where('karyawan_id_karyawan', $staff->karyawan_id_karyawan)->first();

        // Staff submit dulu agar ada yang direview
        $this->actingAs($staff, 'sanctum')
             ->postJson("/api/staff/tugas/{$tugas->id_tugas}/submit", [
                 'catatan_karyawan' => 'Siap direview',
                 'progress' => 100,
             ])
             ->assertStatus(200);

        // ACC pertama → status "sudah di-acc"
        $this->actingAs($kadiv, 'sanctum')
             ->postJson("/api/kadiv/tugas/{$tugas->id_tugas}/review", [
                 'status_review' => 'acc',
             ])
             ->assertStatus(200);

        $this->assertDatabaseHas('tugas', [
            'id_tugas' => $tugas->id_tugas,
            'status' => Tugas::STATUS_SUDAH_ACC,
        ]);

        // ACC kedua → ditolak (guard dobel-ACC)
        $this->actingAs($kadiv, 'sanctum')
             ->postJson("/api/kadiv/tugas/{$tugas->id_tugas}/review", [
                 'status_review' => 'acc',
             ])
             ->assertStatus(403);
    }

    #[Test]
    public function kadiv_revisi_mengembalikan_tugas_ke_berjalan()
    {
        $staff = User2::where('username', 'budist')->first();
        $kadiv = User2::where('username', 'tonokd')->first();
        $tugas = Tugas::where('karyawan_id_karyawan', $staff->karyawan_id_karyawan)->first();

        $this->actingAs($staff, 'sanctum')
             ->postJson("/api/staff/tugas/{$tugas->id_tugas}/submit", [
                 'catatan_karyawan' => 'Revisi dulu',
                 'progress' => 100,
             ])
             ->assertStatus(200);

        // Revisi → tugas kembali "berjalan"
        $this->actingAs($kadiv, 'sanctum')
             ->postJson("/api/kadiv/tugas/{$tugas->id_tugas}/review", [
                 'status_review' => 'revisi',
                 'catatan_revisi' => 'Perbaiki bagian ini',
                 'deadline' => now()->addDays(5)->toDateString(),
             ])
             ->assertStatus(200);

        $this->assertDatabaseHas('tugas', [
            'id_tugas' => $tugas->id_tugas,
            'status' => Tugas::STATUS_BERJALAN,
        ]);
    }

    #[Test]
    public function staff_bisa_submit_tugas_tanpa_mengirim_progress()
    {
        $user = User2::where('username', 'budist')->first();
        $tugas = Tugas::where('karyawan_id_karyawan', $user->karyawan_id_karyawan)->first();
        $progressAwal = $tugas->progress;

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson("/api/staff/tugas/{$tugas->id_tugas}/submit", [
                             'catatan_karyawan' => 'Sedang dikerjakan',
                         ]);

        $response->assertStatus(200);
        $this->assertEquals($progressAwal, $tugas->fresh()->progress);
    }

        #[Test]
    public function staff_hanya_bisa_melihat_tugas_sendiri()
    {
        // 1. Ambil Staff A (Budi) yang sudah punya tugas "Bikin API" dari Seeder
        $staffA = User2::where('username', 'budist')->first();
        $tugasA = Tugas::where('karyawan_id_karyawan', $staffA->karyawan_id_karyawan)->first();

        // 2. Kita buat Staff B yang SATU DIVISI dengan Budi
        $karyawanB = \App\Models\Karyawan::create([
            'id_karyawan' => 'KR902',
            'nama' => 'Joko (Teman Divisi)',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1996-01-01',
            'tanggal_rekrut' => '2023-01-10',
            'no_telepon' => '08111',
            'jabatan' => 'Frontend',
            'divisi_id_divisi' => $staffA->karyawan->divisi_id_divisi, // Satu divisi!
        ]);
        $staffB = User2::create([
            'id_user' => 'US902',
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