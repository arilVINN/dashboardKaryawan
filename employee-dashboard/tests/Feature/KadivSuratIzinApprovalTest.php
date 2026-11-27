<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\SuratIzin;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KadivSuratIzinApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;

    private User2 $kadiv;

    private User2 $otherKadiv;

    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'kadiv'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'hrd'],
        ]);

        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'status_aktif' => 'Aktif',
        ]);
        Divisi::create([
            'id_divisi' => 'DIV-FIN',
            'kode_divisi' => 'FIN',
            'nama_divisi' => 'Finance',
            'status_aktif' => 'Aktif',
        ]);

        $this->staff = $this->account('STF', 'Staff Satu', 'Staff IT', 'DIV-IT', 'ROLE-STAFF', 'staff.one');
        $this->kadiv = $this->account('KAD', 'Kadiv IT', 'Kepala Divisi IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $this->otherKadiv = $this->account('KAF', 'Kadiv Finance', 'Kepala Divisi Finance', 'DIV-FIN', 'ROLE-KADIV', 'kadiv.fin');
        $this->hrd = $this->account('HRD', 'HRD Satu', 'HRD Manager', 'DIV-IT', 'ROLE-HRD', 'hrd.one');
    }

    public function test_staff_submission_goes_to_kadiv_before_hrd(): void
    {
        $this->submitStaffRequest();

        $surat = SuratIzin::firstOrFail();

        $this->assertSame('Pending', $surat->status);
        $this->assertSame('kadiv', $surat->tahap);
        $this->assertDatabaseHas('notifikasis', [
            'user_id_user' => $this->kadiv->id_user,
            'judul_notifikasi' => 'Surat Izin Menunggu Persetujuan',
        ]);
        $this->assertDatabaseMissing('notifikasis', [
            'user_id_user' => $this->hrd->id_user,
            'judul_notifikasi' => 'Surat Izin Masuk',
        ]);
    }

    public function test_hrd_cannot_approve_before_kadiv_approval(): void
    {
        $this->submitStaffRequest();
        $surat = SuratIzin::firstOrFail();

        $this->actingAs($this->hrd)
            ->post(route('hrd.suratIzin.acc', $surat))
            ->assertStatus(409);
    }

    public function test_kadiv_must_upload_a_png_signature_before_approving(): void
    {
        $this->submitStaffRequest();
        $surat = SuratIzin::firstOrFail();

        $this->actingAs($this->kadiv)
            ->post(route('kadiv.suratIzin.acc', $surat))
            ->assertStatus(409);
    }

    public function test_kadiv_signature_approval_sends_request_to_hrd_and_generates_pdf_after_hrd_approval(): void
    {
        $this->submitStaffRequest();
        $surat = SuratIzin::firstOrFail();

        $this->actingAs($this->kadiv)
            ->post(route('kadiv.signature.upload'), [
                'tanda_tangan' => UploadedFile::fake()->image('ttd-kadiv.png', 600, 200),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->kadiv->refresh();
        $kadivEmployee = $this->kadiv->karyawan()->firstOrFail();
        $this->assertNotNull($kadivEmployee->tanda_tangan);
        Storage::disk('private')->assertExists($kadivEmployee->tanda_tangan);

        $this->actingAs($this->kadiv)
            ->get(route('kadiv.signature.show'))
            ->assertOk()
            ->assertHeader('content-type', 'image/png');

        $this->actingAs($this->kadiv)
            ->get(route('kadiv.suratIzin'))
            ->assertOk()
            ->assertSee('Pengajuan Izin - Sakit');

        $this->actingAs($this->kadiv)
            ->post(route('kadiv.suratIzin.acc', $surat))
            ->assertRedirect()
            ->assertSessionHas('success');

        $surat->refresh();

        $this->assertSame('Pending', $surat->status);
        $this->assertSame('hrd', $surat->tahap);
        $this->assertSame($this->kadiv->karyawan_id_karyawan, $surat->kadiv_id_karyawan);
        $this->assertNotNull($surat->kadiv_signature_path);
        Storage::disk('private')->assertExists($surat->kadiv_signature_path);

        $this->assertDatabaseHas('notifikasis', [
            'user_id_user' => $this->hrd->id_user,
            'judul_notifikasi' => 'Surat Izin Menunggu Persetujuan HRD',
        ]);

        $this->actingAs($this->hrd)
            ->get(route('hrd.suratIzin'))
            ->assertOk()
            ->assertSee('Tanda Tangan')
            ->assertSee('modalHrdSignature', false);

        $this->actingAs($this->hrd)
            ->post(route('hrd.signature.upload'), [
                'tanda_tangan' => UploadedFile::fake()->image('ttd-hr.png', 600, 200),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->hrd->refresh();
        $hrdEmployee = $this->hrd->karyawan()->firstOrFail();
        $this->assertNotNull($hrdEmployee->tanda_tangan);
        Storage::disk('private')->assertExists($hrdEmployee->tanda_tangan);

        $this->actingAs($this->hrd)
            ->post(route('hrd.suratIzin.acc', $surat))
            ->assertRedirect()
            ->assertSessionHas('success');

        $surat->refresh();

        $this->assertSame('Disetujui', $surat->status);
        $this->assertSame('selesai', $surat->tahap);
        $this->assertSame($this->hrd->id_user, $surat->hrd_id_user);
        $this->assertNotNull($surat->hrd_signature_path);
        Storage::disk('private')->assertExists($surat->hrd_signature_path);

        $this->actingAs($this->staff)
            ->get(route('suratIzin.pdf', $surat))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_kadiv_can_save_a_drawn_signature_data_uri(): void
    {
        $this->submitStaffRequest();

        $dataUri = 'data:image/png;base64,'.base64_encode(
            (string) file_get_contents(public_path('gambar/dummy-signature.png'))
        );

        $this->actingAs($this->kadiv)
            ->post(route('kadiv.signature.upload'), [
                'tanda_tangan_data' => $dataUri,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $employee = $this->kadiv->karyawan()->firstOrFail();
        $this->assertNotNull($employee->tanda_tangan);
        Storage::disk('private')->assertExists($employee->tanda_tangan);

        $processed = imagecreatefromstring(
            Storage::disk('private')->get($employee->tanda_tangan)
        );

        $this->assertNotFalse($processed);
        $this->assertSame(900, imagesx($processed));
        $this->assertSame(300, imagesy($processed));

        imagedestroy($processed);
    }

    public function test_kadiv_cannot_approve_a_request_from_another_division(): void
    {
        $this->submitStaffRequest();
        $surat = SuratIzin::firstOrFail();

        $this->actingAs($this->otherKadiv)
            ->post(route('kadiv.suratIzin.acc', $surat))
            ->assertStatus(403);

        $this->assertSame('kadiv', $surat->fresh()->tahap);
    }

    private function submitStaffRequest(): void
    {
        $this->actingAs($this->staff)
            ->post(route('staff.suratIzin.store'), [
                'tanggal_pengajuan' => now()->toDateString(),
                'keperluan' => 'Pribadi',
                'jenis_pengajuan' => 'Sakit',
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => now()->toDateString(),
                'keterangan' => 'Demam',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    private function account(
        string $prefix,
        string $nama,
        string $jabatan,
        string $divisiId,
        string $roleId,
        string $username
    ): User2 {
        $karyawan = Karyawan::create([
            'id_karyawan' => 'KRY-'.$prefix,
            'nama' => $nama,
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1995-01-01',
            'tanggal_masuk' => '2026-01-01',
            'no_telepon' => '0812',
            'jabatan' => $jabatan,
            'divisi_id_divisi' => $divisiId,
            'status_aktif' => 'Aktif',
        ]);

        return User2::create([
            'id_user' => 'USR-'.$prefix,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $karyawan->id_karyawan,
        ]);
    }
}
