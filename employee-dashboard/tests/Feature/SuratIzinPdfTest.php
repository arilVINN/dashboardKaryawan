<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\SuratIzin;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuratIzinPdfTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;

    private User2 $otherStaff;

    private User2 $hrd;

    private SuratIzin $suratIzin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
        ]);

        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'status_aktif' => 'Aktif',
        ]);

        Karyawan::create([
            'id_karyawan' => 'KRY-A',
            'nama' => 'Muhammad Rizky Pratama',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1995-01-01',
            'tanggal_masuk' => '2026-01-01',
            'no_telepon' => '0812',
            'jabatan' => 'Engineer',
            'divisi_id_divisi' => 'DIV-IT',
        ]);
        Karyawan::create([
            'id_karyawan' => 'KRY-B',
            'nama' => 'Karyawan Lain',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_lahir' => '1995-01-01',
            'tanggal_masuk' => '2026-01-01',
            'no_telepon' => '0812',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
        ]);
        Karyawan::create([
            'id_karyawan' => 'KRY-HRD',
            'nama' => 'HRD User',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_lahir' => '1990-01-01',
            'tanggal_masuk' => '2020-01-01',
            'no_telepon' => '0812',
            'jabatan' => 'HRD',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        $this->staff = User2::create([
            'id_user' => 'USR-A',
            'username' => 'staff.a',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => 'KRY-A',
        ]);
        $this->otherStaff = User2::create([
            'id_user' => 'USR-B',
            'username' => 'staff.b',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => 'KRY-B',
        ]);
        $this->hrd = User2::create([
            'id_user' => 'USR-HRD',
            'username' => 'hrd.user',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-HRD',
            'karyawan_id_karyawan' => 'KRY-HRD',
        ]);

        $this->suratIzin = SuratIzin::create([
            'id_surat' => 'SRTABC',
            'karyawan_id_karyawan' => 'KRY-A',
            'tanggal_pengajuan' => '2026-11-26',
            'jenis_izin' => 'Lupa Absen',
            'keperluan' => 'Pribadi',
            'kategori_pengajuan' => 'Izin',
            'judul_surat' => 'Pengajuan Izin - Lupa Absen',
            'tanggal_izin' => '2026-11-30',
            'tanggal_mulai' => '2026-11-30',
            'tanggal_selesai' => '2026-11-30',
            'keterangan' => 'Lupa melakukan absensi masuk.',
            'status' => 'Disetujui',
        ]);
    }

    public function test_owner_can_download_the_perizinan_pdf(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('suratIzin.pdf', $this->suratIzin));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
    }

    public function test_hrd_can_download_any_perizinan_pdf(): void
    {
        $this->actingAs($this->hrd)
            ->get(route('suratIzin.pdf', $this->suratIzin))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_other_staff_cannot_download_someone_elses_pdf(): void
    {
        $this->actingAs($this->otherStaff)
            ->get(route('suratIzin.pdf', $this->suratIzin))
            ->assertForbidden();
    }

    public function test_pending_request_softcopy_is_not_available(): void
    {
        $this->suratIzin->update(['status' => 'Pending']);

        $this->actingAs($this->staff)
            ->get(route('suratIzin.pdf', $this->suratIzin))
            ->assertForbidden();
    }

    public function test_rejected_request_softcopy_is_not_available(): void
    {
        $this->suratIzin->update(['status' => 'Ditolak']);

        $this->actingAs($this->staff)
            ->get(route('suratIzin.pdf', $this->suratIzin))
            ->assertForbidden();
    }

    public function test_pdf_view_maps_the_submitted_data_and_selected_options(): void
    {
        $html = view('pdf.surat-izin', [
            'suratIzin' => $this->suratIzin->load('karyawan.divisi'),
            'logoDataUri' => null,
        ])->render();

        $this->assertStringContainsString('PT. Sinergi Ilmiah Indonesia', $html);
        $this->assertStringContainsString('FORM PENGAJUAN PERIZINAN KARYAWAN', $html);
        $this->assertStringContainsString('FR.03.02.1', $html);
        $this->assertStringContainsString('03 September 2026', $html);
        $this->assertStringContainsString('Muhammad Rizky Pratama', $html);
        $this->assertStringContainsString('Teknologi Informasi', $html);
        $this->assertStringContainsString('26 November 2026', $html);
        $this->assertStringContainsString('30 November 2026', $html);
        $this->assertStringContainsString('Lupa Absen', $html);
        $this->assertStringContainsString('Lupa melakukan absensi masuk.', $html);
        $this->assertSame(2, substr_count($html, 'Disetujui Oleh,'));
        $this->assertStringContainsString('Kepala Divisi,', $html);
        $this->assertStringContainsString('HR,', $html);
        $this->assertStringContainsString('Mengetahui,', $html);
        $this->assertStringContainsString('Direktur PT. Silindo', $html);
        $this->assertStringNotContainsString('Pemohon', $html);

        // One checked jenis; the selected keperluan is marked by underline.
        $this->assertSame(1, substr_count($html, 'class="checkbox checkbox-checked"'));
        $this->assertStringContainsString('<b><u>Pribadi</u></b>', $html);
    }

    public function test_pdf_prints_the_jam_for_time_based_jenis(): void
    {
        $this->suratIzin->update([
            'jenis_izin' => 'Datang Terlambat',
            'jam_pengajuan' => '08:15',
        ]);

        $html = view('pdf.surat-izin', [
            'suratIzin' => $this->suratIzin->load('karyawan.divisi'),
            'logoDataUri' => null,
        ])->render();

        $this->assertStringContainsString('Datang Terlambat, jam', $html);
        $this->assertStringContainsString('08:15', $html);
        $this->assertSame(1, substr_count($html, 'class="checkbox checkbox-checked"'));
    }

    public function test_time_based_request_requires_and_stores_the_jam(): void
    {
        $payload = [
            'tanggal_pengajuan' => now()->toDateString(),
            'keperluan' => 'Pribadi',
            'jenis_pengajuan' => 'Lupa Absen',
            'jam_pengajuan' => '07:45',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'keterangan' => 'Lupa absen masuk.',
        ];

        $this->actingAs($this->staff)
            ->post(route('staff.suratIzin.store'), $payload)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('surat_izin', [
            'karyawan_id_karyawan' => 'KRY-A',
            'jenis_izin' => 'Lupa Absen',
            'jam_pengajuan' => '07:45',
        ]);
    }

    public function test_manual_jam_input_is_normalized_before_validation(): void
    {
        $this->actingAs($this->staff)
            ->post(route('staff.suratIzin.store'), [
                'tanggal_pengajuan' => now()->toDateString(),
                'keperluan' => 'Pribadi',
                'jenis_pengajuan' => 'Datang Terlambat',
                'jam_pengajuan' => '8:30',
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => now()->toDateString(),
                'keterangan' => 'Terlambat masuk.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('surat_izin', [
            'jenis_izin' => 'Datang Terlambat',
            'jam_pengajuan' => '08:30',
        ]);
    }

    public function test_time_based_request_without_jam_is_rejected(): void
    {
        $this->actingAs($this->staff)
            ->post(route('staff.suratIzin.store'), [
                'tanggal_pengajuan' => now()->toDateString(),
                'keperluan' => 'Pribadi',
                'jenis_pengajuan' => 'Pulang Lebih Awal',
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => now()->toDateString(),
            ])
            ->assertSessionHasErrors('jam_pengajuan');
    }

    public function test_pdf_view_includes_the_dummy_signature_when_approved(): void
    {
        $html = view('pdf.surat-izin', [
            'suratIzin' => $this->suratIzin->load('karyawan.divisi'),
            'logoDataUri' => null,
            'signatureDataUri' => 'data:image/png;base64,AAAA',
            'showSignature' => true,
        ])->render();

        $this->assertStringContainsString('alt="Tanda tangan Kadiv"', $html);
    }

    public function test_pdf_view_hides_the_signature_when_not_approved(): void
    {
        $html = view('pdf.surat-izin', [
            'suratIzin' => $this->suratIzin->load('karyawan.divisi'),
            'logoDataUri' => null,
            'signatureDataUri' => 'data:image/png;base64,AAAA',
            'showSignature' => false,
        ])->render();

        $this->assertStringNotContainsString('alt="Tanda tangan', $html);
    }

    public function test_pdf_view_prints_the_kadiv_and_hr_names(): void
    {
        $this->suratIzin->update([
            'kadiv_id_karyawan' => 'KRY-B',
            'hrd_id_user' => $this->hrd->id_user,
        ]);

        $html = view('pdf.surat-izin', [
            'suratIzin' => $this->suratIzin->load('karyawan.divisi'),
            'logoDataUri' => null,
        ])->render();

        $this->assertStringContainsString('( Karyawan Lain )', $html);
        $this->assertStringContainsString('( HRD User )', $html);
    }

    public function test_pdf_view_includes_the_hr_signature_when_available(): void
    {
        $html = view('pdf.surat-izin', [
            'suratIzin' => $this->suratIzin->load('karyawan.divisi'),
            'logoDataUri' => null,
            'hrdSignatureDataUri' => 'data:image/png;base64,BBBB',
            'showSignature' => true,
        ])->render();

        $this->assertStringContainsString('alt="Tanda tangan HR"', $html);
    }

    public function test_pdf_view_keeps_one_page_for_long_keterangan(): void
    {
        $this->suratIzin->update([
            'keterangan' => str_repeat('Keterangan panjang untuk pengujian pembungkusan teks. ', 18),
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.surat-izin', [
            'suratIzin' => $this->suratIzin->load('karyawan.divisi'),
            'logoDataUri' => null,
        ])->setPaper('a4', 'portrait');

        $pdf->render();

        $this->assertSame(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
    }

    public function test_soft_file_link_is_available_in_the_surat_izin_status_table(): void
    {
        $this->actingAs($this->staff)
            ->get('/staff/suratIzin')
            ->assertOk()
            ->assertSee('Lampiran')
            ->assertSee('PDF')
            ->assertSee(route('suratIzin.pdf', ['suratIzin' => $this->suratIzin, 'preview' => 1]), false);
    }

    public function test_pending_soft_file_is_not_linked_in_the_status_table(): void
    {
        $this->suratIzin->update(['status' => 'Pending']);

        $this->actingAs($this->staff)
            ->get('/staff/suratIzin')
            ->assertOk()
            ->assertSee('Setelah disetujui')
            ->assertDontSee(route('suratIzin.pdf', ['suratIzin' => $this->suratIzin, 'preview' => 1]), false);
    }

    public function test_rejected_soft_file_is_not_linked_in_the_status_table(): void
    {
        $this->suratIzin->update(['status' => 'Ditolak']);

        $this->actingAs($this->staff)
            ->get('/staff/suratIzin')
            ->assertOk()
            ->assertSee('Tidak tersedia')
            ->assertDontSee(route('suratIzin.pdf', ['suratIzin' => $this->suratIzin, 'preview' => 1]), false);
    }

    public function test_hrd_list_has_a_cetak_pdf_action(): void
    {
        $this->actingAs($this->hrd)
            ->get(route('hrd.suratIzin'))
            ->assertOk()
            ->assertSee('Cetak PDF')
            ->assertSee(route('suratIzin.pdf', ['suratIzin' => $this->suratIzin, 'preview' => 1]), false);
    }

    public function test_hrd_cannot_print_a_pending_request(): void
    {
        $this->suratIzin->update(['status' => 'Pending']);

        $this->actingAs($this->hrd)
            ->get(route('hrd.suratIzin'))
            ->assertOk()
            ->assertDontSee('Cetak PDF')
            ->assertDontSee(route('suratIzin.pdf', ['suratIzin' => $this->suratIzin, 'preview' => 1]), false);
    }
}
