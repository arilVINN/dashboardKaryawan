<?php

namespace Tests\Feature;

use App\Livewire\Kadiv\PesanTable;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Notifikasi;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class HrIncomingNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;

    private User2 $kadiv;

    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->staff = $this->account('STF', 'Staff Satu', 'Staff', 'ROLE-STAFF', 'staff.one');
        $this->kadiv = $this->account('KAD', 'Kadiv Satu', 'Kadiv IT', 'ROLE-KADIV', 'kadiv.one');
        $this->hrd = $this->account('HRD', 'HRD Satu', 'HRD Manager', 'ROLE-HRD', 'hrd.one');
    }

    public function test_booking_submission_notifies_hrd(): void
    {
        $this->actingAs($this->staff)
            ->post('/booking', [
                'kategori' => 'Ruangan',
                'jenis' => 'Ruang Meeting',
                'tanggal' => now()->addDay()->toDateString(),
                'jam_mulai' => '10:00',
                'jam_selesai' => '11:00',
                'keperluan' => 'Rapat koordinasi',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $notification = Notifikasi::where('user_id_user', $this->hrd->id_user)->firstOrFail();
        $this->assertSame('Booking Masuk', $notification->judul_notifikasi);
        $this->assertStringContainsString('Ruangan', $notification->isi_notif);
        $this->assertStringContainsString('Ruang Meeting', $notification->isi_notif);
        $this->assertStringContainsString('Staff Satu', $notification->isi_notif);
        $this->assertSame(0, Notifikasi::where('user_id_user', $this->staff->id_user)->count());
    }

    public function test_surat_izin_submission_notifies_hrd(): void
    {
        $this->actingAs($this->staff)
            ->post(route('staff.suratIzin.store'), [
                'jenis_izin' => 'Izin Sakit',
                'judul_surat' => 'Izin Sakit Hari Ini',
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => now()->toDateString(),
                'keterangan' => 'Demam',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $notification = Notifikasi::where('user_id_user', $this->hrd->id_user)->firstOrFail();
        $this->assertSame('Surat Izin Masuk', $notification->judul_notifikasi);
        $this->assertStringContainsString('Izin Sakit Hari Ini', $notification->isi_notif);
        $this->assertStringContainsString('Izin Sakit', $notification->isi_notif);
        $this->assertStringContainsString('Staff Satu', $notification->isi_notif);
    }

    public function test_kadiv_surat_izin_submission_notifies_hrd(): void
    {
        Livewire::actingAs($this->kadiv)
            ->test(PesanTable::class)
            ->set('jenisIzin', 'Izin Keluarga')
            ->set('judulSurat', 'Izin Acara Keluarga')
            ->set('tanggalMulai', now()->toDateString())
            ->set('tanggalSelesai', now()->toDateString())
            ->call('kirimSuratIzin')
            ->assertHasNoErrors();

        $notification = Notifikasi::where('user_id_user', $this->hrd->id_user)->firstOrFail();
        $this->assertSame('Surat Izin Masuk', $notification->judul_notifikasi);
        $this->assertStringContainsString('Izin Acara Keluarga', $notification->isi_notif);
        $this->assertStringContainsString('Kadiv Satu', $notification->isi_notif);
    }

    public function test_hrd_dashboard_renders_incoming_and_birthday_notifications(): void
    {
        $this->withoutVite();

        Notifikasi::create([
            'id_notifikasi' => Notifikasi::generateId(),
            'judul_notifikasi' => 'Booking Masuk',
            'isi_notif' => 'Booking Ruangan (Ruang Meeting) dari Staff Satu menunggu persetujuan HRD.',
            'tanggal_notifikasi' => now()->toDateString(),
            'user_id_user' => $this->hrd->id_user,
        ]);
        Notifikasi::create([
            'id_notifikasi' => Notifikasi::generateId(),
            'judul_notifikasi' => 'Surat Izin Masuk',
            'isi_notif' => 'Surat izin "Izin Sakit Hari Ini" dari Staff Satu menunggu persetujuan HRD.',
            'tanggal_notifikasi' => now()->toDateString(),
            'user_id_user' => $this->hrd->id_user,
        ]);
        Notifikasi::create([
            'id_notifikasi' => Notifikasi::generateId(),
            'judul_notifikasi' => 'Selamat Ulang Tahun!',
            'isi_notif' => 'Selamat ulang tahun, Staff Satu!',
            'tanggal_notifikasi' => now()->toDateString(),
            'user_id_user' => $this->hrd->id_user,
        ]);
        Notifikasi::create([
            'id_notifikasi' => Notifikasi::generateId(),
            'judul_notifikasi' => 'Notifikasi orang lain',
            'isi_notif' => 'Tidak boleh tampil.',
            'tanggal_notifikasi' => now()->toDateString(),
            'user_id_user' => $this->staff->id_user,
        ]);

        $this->actingAs($this->hrd)
            ->get('/hrd/dashboard')
            ->assertOk()
            ->assertSee('Booking masuk')
            ->assertSee('Surat izin masuk')
            ->assertSee('Ulang tahun karyawan')
            ->assertSee('menunggu persetujuan HRD.')
            ->assertDontSee('Notifikasi orang lain');
    }

    public function test_hrd_notification_panel_is_limited_to_five_items(): void
    {
        $notifikasi = collect(range(1, 8))->map(fn (int $i) => (object) [
            'id_notifikasi' => 'NT-PANEL-'.$i,
            'judul_notifikasi' => 'Booking Masuk '.$i,
            'isi_notif' => 'Notifikasi ke-'.$i,
            'created_at' => now(),
        ]);
        $pesan = collect(range(1, 5))->map(fn (int $i) => (object) [
            'id_pesan' => 'PSN-PANEL-'.$i,
            'judul_pesan' => 'Pesan '.$i,
            'deskripsi' => 'Isi pesan '.$i,
            'created_at' => now(),
        ]);

        $contractReminders = collect(range(1, 2))->map(fn (int $i) => [
            'id_notifikasi' => 'NT-KONTRAK-'.$i,
            'isi_notif' => 'Kontrak karyawan '.$i.' segera berakhir.',
            'created_at' => now()->toDateTimeString(),
        ]);

        $html = view('component_hrd.notifikasi', compact(
            'notifikasi',
            'pesan',
            'contractReminders'
        ))->render();

        $this->assertSame(5, substr_count($html, 'data-notification-id="'));
        $this->assertStringContainsString('Kontrak kerja segera berakhir', $html);
        $this->assertStringContainsString('Kontrak karyawan 1 segera berakhir.', $html);
        $this->assertStringContainsString('Notifikasi ke-1', $html);
        $this->assertStringNotContainsString('Notifikasi ke-4', $html);
        $this->assertStringNotContainsString('Pesan 1', $html);
    }

    private function account(
        string $suffix,
        string $nama,
        string $jabatan,
        string $roleId,
        string $username
    ): User2 {
        Karyawan::create([
            'id_karyawan' => 'KRY-'.$suffix,
            'nama' => $nama,
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => $jabatan,
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        return User2::create([
            'id_user' => 'USR-'.$suffix,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => 'KRY-'.$suffix,
        ]);
    }
}
