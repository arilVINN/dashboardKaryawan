<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Notifikasi;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinanceNotificationVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private User2 $finance;
    private User2 $itKadiv;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);

        Divisi::create(['id_divisi' => 'DIV-FIN', 'kode_divisi' => 'FIN', 'nama_divisi' => 'Finance & Accounting', 'status_aktif' => 'Aktif']);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Information Technology', 'status_aktif' => 'Aktif']);

        Karyawan::create(['id_karyawan' => 'KRY-FIN', 'nama' => 'Finance Lead', 'jenis_kelamin' => 'Perempuan', 'jabatan' => 'Kadiv Finance', 'divisi_id_divisi' => 'DIV-FIN']);
        Karyawan::create(['id_karyawan' => 'KRY-IT', 'nama' => 'IT Lead', 'jenis_kelamin' => 'Laki-laki', 'jabatan' => 'Kadiv IT', 'divisi_id_divisi' => 'DIV-IT']);

        $this->finance = User2::create([
            'id_user' => 'USR-FIN',
            'username' => 'finance.lead',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-KADIV',
            'karyawan_id_karyawan' => 'KRY-FIN',
        ]);
        $this->itKadiv = User2::create([
            'id_user' => 'USR-IT',
            'username' => 'it.lead',
            'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-KADIV',
            'karyawan_id_karyawan' => 'KRY-IT',
        ]);
    }

    public function test_pengajuan_dana_submit_notifies_finance_users(): void
    {
        $this->actingAs($this->itKadiv)
            ->post(route('kadiv.pengajuanDana.store'), [
                'judul_pengajuan' => 'Pengadaan server',
                'keperluan' => 'Upgrade infrastruktur',
                'nominal_diajukan' => 15000000,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $notification = Notifikasi::where('user_id_user', $this->finance->id_user)->firstOrFail();
        $this->assertSame('Pengajuan Dana Baru', $notification->judul_notifikasi);
        $this->assertStringContainsString('Pengadaan server', $notification->isi_notif);
        $this->assertStringContainsString('IT Lead', $notification->isi_notif);
        $this->assertSame(0, Notifikasi::where('user_id_user', $this->itKadiv->id_user)->count());
    }

    public function test_laporan_laba_rugi_submit_notifies_finance_users(): void
    {
        Storage::fake('public');

        $this->actingAs($this->itKadiv)
            ->post(route('kadiv.laporanLabaRugi.store'), [
                'periode' => '2026-11',
                'minggu_ke' => 3,
                'tanggal_mulai' => '2026-11-03',
                'tanggal_selesai' => '2026-11-04',
                'total_pendapatan' => 60000000,
                'total_pengeluaran' => 10000000,
                'bukti_laporan' => UploadedFile::fake()->create('bukti.pdf', 20, 'application/pdf'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $notification = Notifikasi::where('user_id_user', $this->finance->id_user)->firstOrFail();
        $this->assertSame('Laporan L/R Baru', $notification->judul_notifikasi);
        $this->assertStringContainsString('2026-11', $notification->isi_notif);
        $this->assertStringContainsString('IT Lead', $notification->isi_notif);
    }

}
