<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\LampiranKaryawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HrdLampiranKaryawanTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);

        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'status_aktif' => 'Aktif',
        ]);

        $karyawanHrd = Karyawan::create([
            'id_karyawan' => 'KRY-HRD',
            'nama' => 'HRD Tester',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_lahir' => '1990-01-01',
            'tanggal_masuk' => '2020-01-01',
            'no_telepon' => '08123456789',
            'jabatan' => 'HRD',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        $this->hrd = User2::create([
            'id_user' => 'USR-HRD',
            'username' => 'hrd_tester',
            'password' => Hash::make('password123'),
            'role_id_role' => 'ROLE-HRD',
            'karyawan_id_karyawan' => $karyawanHrd->id_karyawan,
        ]);
    }

    public function test_can_create_karyawan_with_new_fields_and_lampiran(): void
    {
        $fileKtp = UploadedFile::fake()->create('ktp_saya.pdf', 500, 'application/pdf');
        $fileIjazah = UploadedFile::fake()->create('ijazah_saya.jpg', 600, 'image/jpeg');

        $payload = [
            'nama' => 'Budi Setiawan',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1996-03-12',
            'tanggal_masuk' => '2026-03-01',
            'tanggal_akhir_kontrak' => '2027-03-01',
            'no_rekening' => 'BCA 987654321',
            'no_telepon' => '081299998888',
            'email' => 'budi.setiawan@example.test',
            'alamat' => 'Jl. Merdeka No. 10',
            'jabatan' => 'Staff',
            'status_aktif' => 'Aktif',
            'divisi_id_divisi' => 'DIV-IT',
            'username' => 'budisetiawan',
            'password' => 'password123',
            'lampiran' => [
                'ktp' => $fileKtp,
                'ijazah' => $fileIjazah,
            ],
        ];

        $response = $this->actingAs($this->hrd)->post('/hrd/staff', $payload);
        $response->assertCreated();

        $this->assertDatabaseHas('karyawans', [
            'nama' => 'Budi Setiawan',
            'tanggal_masuk' => '2026-03-01',
            'tanggal_akhir_kontrak' => '2027-03-01',
            'no_rekening' => 'BCA 987654321',
        ]);

        $karyawan = Karyawan::where('email', 'budi.setiawan@example.test')->firstOrFail();
        $this->assertCount(2, $karyawan->lampiran);

        $ktpRecord = $karyawan->lampiran()->where('jenis_lampiran', 'ktp')->first();
        $this->assertNotNull($ktpRecord);
        $this->assertSame('ktp_saya.pdf', $ktpRecord->nama_file);
        Storage::disk('public')->assertExists($ktpRecord->path_file);

        $ijazahRecord = $karyawan->lampiran()->where('jenis_lampiran', 'ijazah')->first();
        $this->assertNotNull($ijazahRecord);
        $this->assertSame('ijazah_saya.jpg', $ijazahRecord->nama_file);
        Storage::disk('public')->assertExists($ijazahRecord->path_file);
    }

    public function test_detail_karyawan_page_renders_new_info_and_lampiran(): void
    {
        $karyawan = Karyawan::create([
            'id_karyawan' => 'KRY-TEST1',
            'nama' => 'Siti Rahma',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_lahir' => '1998-07-20',
            'tanggal_masuk' => '2025-05-01',
            'tanggal_akhir_kontrak' => '2026-05-01',
            'no_rekening' => 'Mandiri 1122334455',
            'no_telepon' => '0812333444',
            'email' => 'siti@example.test',
            'alamat' => 'Semarang',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        LampiranKaryawan::create([
            'id_lampiran' => 'LMP-001',
            'karyawan_id_karyawan' => $karyawan->id_karyawan,
            'jenis_lampiran' => 'ktp',
            'nama_file' => 'ktp_siti.pdf',
            'path_file' => 'lampiran_karyawan/ktp_siti.pdf',
        ]);

        $response = $this->actingAs($this->hrd)->get('/hrd/detailKaryawan/' . $karyawan->id_karyawan);
        $response->assertOk();

        // Cek data diri baru
        $response->assertSee('No. Rekening');
        $response->assertSee('Mandiri 1122334455');
        $response->assertSee('Tanggal Masuk');
        $response->assertSee('Tanggal Akhir Kontrak');

        // Cek lampiran
        $response->assertSee('Lampiran Dokumen');
        $response->assertSee('ktp_siti.pdf');
        $response->assertSee(route('hrd.lampiran.download', 'LMP-001'));
    }

    public function test_download_lampiran_endpoint(): void
    {
        $karyawan = Karyawan::create([
            'id_karyawan' => 'KRY-DL',
            'nama' => 'Doni Kusuma',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_masuk' => '2026-01-01',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        $filePath = 'lampiran_karyawan/test_transkrip.pdf';
        Storage::disk('public')->put($filePath, 'fake content');

        $lampiran = LampiranKaryawan::create([
            'id_lampiran' => 'LMP-DL',
            'karyawan_id_karyawan' => $karyawan->id_karyawan,
            'jenis_lampiran' => 'transkrip',
            'nama_file' => 'transkrip_nilai.pdf',
            'path_file' => $filePath,
        ]);

        $response = $this->actingAs($this->hrd)->get(route('hrd.lampiran.download', $lampiran->id_lampiran));
        $response->assertOk();
        $response->assertDownload('transkrip_nilai.pdf');
    }

    public function test_deleting_karyawan_cascades_to_lampiran(): void
    {
        $karyawan = Karyawan::create([
            'id_karyawan' => 'KRY-DEL',
            'nama' => 'Delete Me',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_masuk' => '2026-01-01',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        LampiranKaryawan::create([
            'id_lampiran' => 'LMP-DEL',
            'karyawan_id_karyawan' => $karyawan->id_karyawan,
            'jenis_lampiran' => 'lamaran',
            'nama_file' => 'surat_lamaran.pdf',
            'path_file' => 'lampiran_karyawan/lamaran.pdf',
        ]);

        $this->assertDatabaseHas('lampiran_karyawan', ['id_lampiran' => 'LMP-DEL']);

        $karyawan->delete();

        $this->assertDatabaseMissing('lampiran_karyawan', ['id_lampiran' => 'LMP-DEL']);
    }
}

