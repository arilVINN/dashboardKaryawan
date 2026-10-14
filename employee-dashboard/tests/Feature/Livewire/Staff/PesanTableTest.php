<?php

namespace Tests\Feature\Livewire\Staff;

use App\Livewire\Staff\PesanTable;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Pesan;
use App\Models\Role;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PesanTableTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);

        $this->staff = $this->account('KRY-S', 'Staff Satu', 'ROLE-STAFF', 'staffsatu');
        $sender = $this->account('KRY-A', 'Abby Sender', 'ROLE-KADIV', 'abby');
        $this->account('KRY-H', 'Hrd Satu', 'ROLE-HRD', 'hrdsatu');

        Tugas::create([
            'id_tugas' => 'T-1',
            'karyawan_id_karyawan' => 'KRY-S',
            'judul_tugas' => 'Desain Logo',
            'deskripsi' => 'Desain Logo desc',
            'deadline' => now()->addDays(10)->toDateString(),
            'progress' => '0',
            'status' => Tugas::STATUS_BARU,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
        Pesan::create([
            'id_pesan' => 'P-T1',
            'judul_pesan' => 'Versi Revisi',
            'deskripsi' => 'Versi Revisi body',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-10-01',
            'tugas_id_tugas' => 'T-1',
            'tugas_karyawan_id_karyawan' => 'KRY-S',
            'pengirim_id_user' => $sender->id_user,
            'penerima_id_user' => $this->staff->id_user,
        ]);
        Pesan::create([
            'id_pesan' => 'P-D1',
            'judul_pesan' => 'Rapat Mingguan',
            'deskripsi' => 'Rapat Mingguan body',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-10-02',
            'tugas_id_tugas' => null,
            'tugas_karyawan_id_karyawan' => null,
            'pengirim_id_user' => $sender->id_user,
            'penerima_id_user' => $this->staff->id_user,
        ]);
        Tugas::create([
            'id_tugas' => 'T-3',
            'karyawan_id_karyawan' => 'KRY-S',
            'judul_tugas' => 'Arsip Lama',
            'deskripsi' => 'Arsip Lama desc',
            'deadline' => now()->addDays(10)->toDateString(),
            'progress' => '0',
            'status' => Tugas::STATUS_BARU,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
        Pesan::create([
            'id_pesan' => 'P-T3',
            'judul_pesan' => 'Balasan Staff',
            'deskripsi' => 'Balasan Staff body',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-09-15',
            'tugas_id_tugas' => 'T-3',
            'tugas_karyawan_id_karyawan' => 'KRY-S',
            'pengirim_id_user' => $this->staff->id_user,
            'penerima_id_user' => $sender->id_user,
        ]);
    }

    private function account(string $id, string $nama, string $role, string $username): User2
    {
        Karyawan::create(['id_karyawan' => $id, 'nama' => $nama, 'jenis_kelamin' => 'Laki-laki', 'jabatan' => $nama, 'divisi_id_divisi' => 'DIV-IT']);

        return User2::create([
            'id_user' => 'USR-'.$id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $role,
            'karyawan_id_karyawan' => $id,
        ]);
    }

    public function test_renders_threads_and_direct_messages(): void
    {
        Livewire::actingAs($this->staff)->test(PesanTable::class)
            ->assertSee('Tugas: Desain Logo')
            ->assertSee('Rapat Mingguan');
    }

    public function test_filters_by_arah(): void
    {
        Livewire::actingAs($this->staff)->test(PesanTable::class)
            ->set('arah', 'keluar')
            ->assertSee('Arsip Lama')
            ->assertDontSee('Desain Logo')
            ->assertDontSee('Rapat Mingguan');

        Livewire::actingAs($this->staff)->test(PesanTable::class)
            ->set('arah', 'masuk')
            ->assertSee('Desain Logo')
            ->assertSee('Rapat Mingguan')
            ->assertDontSee('Arsip Lama');
    }

    public function test_searches_by_judul_or_pengirim(): void
    {
        Livewire::actingAs($this->staff)->test(PesanTable::class)
            ->set('search', 'rapat')
            ->assertSee('Rapat Mingguan')
            ->assertDontSee('Desain Logo');
    }

    public function test_non_staff_is_forbidden(): void
    {
        $hrd = User2::where('username', 'hrdsatu')->first();

        Livewire::actingAs($hrd)->test(PesanTable::class)->assertForbidden();
    }

    public function test_shows_unread_badge_and_marks_message_read(): void
    {
        Livewire::actingAs($this->staff)->test(PesanTable::class)
            ->assertSee('belum dibaca');

        Livewire::actingAs($this->staff)->test(PesanTable::class)
            ->call('baca', 'langsung', 'P-D1')
            ->assertRedirect(url('/pesan/detail/P-D1'));

        $this->assertNotNull(Pesan::whereKey('P-D1')->first()->dibaca_at);
    }

    public function test_can_send_message_to_kadiv(): void
    {
        $kadiv = User2::where('username', 'abby')->first();

        Livewire::actingAs($this->staff)->test(PesanTable::class)
            ->set('recipientUserId', $kadiv->id_user)
            ->set('isi', 'Halo Kadiv')
            ->call('sendMessage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pesans', [
            'pengirim_id_user' => $this->staff->id_user,
            'penerima_id_user' => $kadiv->id_user,
            'deskripsi' => 'Halo Kadiv',
        ]);
    }

    public function test_rejects_recipient_outside_division(): void
    {
        Livewire::actingAs($this->staff)->test(PesanTable::class)
            ->set('recipientUserId', $this->staff->id_user)
            ->set('isi', 'Halo saya sendiri')
            ->call('sendMessage')
            ->assertHasErrors('recipientUserId');

        $this->assertDatabaseMissing('pesans', ['deskripsi' => 'Halo saya sendiri']);
    }

    public function test_can_read_outgoing_thread_without_marking_read(): void
    {
        // T-3's latest message was sent by the staff (keluar): opening it
        // must redirect to its detail instead of silently doing nothing,
        // and must not stamp dibaca_at on someone else's inbox copy.
        Livewire::actingAs($this->staff)->test(PesanTable::class)
            ->call('baca', 'tugas', 'T-3')
            ->assertRedirect(url('/pesan/detail/P-T3'));

        $this->assertNull(Pesan::whereKey('P-T3')->first()->dibaca_at);
    }

    public function test_can_read_thread_with_legacy_null_task_owner_column(): void
    {
        // Legacy/demo rows may link a task without filling the denormalized
        // tugas_karyawan_id_karyawan column; ownership via the task itself
        // must still let the staff open the thread.
        Tugas::create([
            'id_tugas' => 'T-9',
            'karyawan_id_karyawan' => 'KRY-S',
            'judul_tugas' => 'Tugas Lama',
            'deskripsi' => 'Tugas Lama desc',
            'deadline' => now()->addDays(10)->toDateString(),
            'progress' => '0',
            'status' => Tugas::STATUS_BARU,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
        Pesan::create([
            'id_pesan' => 'P-LEGACY',
            'judul_pesan' => 'Balasan Lama',
            'deskripsi' => 'Balasan Lama body',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-09-01',
            'tugas_id_tugas' => 'T-9',
            'tugas_karyawan_id_karyawan' => null,
            'pengirim_id_user' => $this->staff->id_user,
            'penerima_id_user' => User2::where('username', 'abby')->first()->id_user,
        ]);

        Livewire::actingAs($this->staff)->test(PesanTable::class)
            ->call('baca', 'tugas', 'T-9')
            ->assertRedirect(url('/pesan/detail/P-LEGACY'));
    }

    public function test_sent_direct_message_is_listed_and_readable(): void
    {        $kadiv = User2::where('username', 'abby')->first();

        Pesan::create([
            'id_pesan' => 'P-SENT',
            'judul_pesan' => 'Laporan Selesai',
            'deskripsi' => 'Sudah saya kirim.',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-10-03',
            'pengirim_id_user' => $this->staff->id_user,
            'penerima_id_user' => $kadiv->id_user,
        ]);

        Livewire::actingAs($this->staff)->test(PesanTable::class)
            ->assertSee('Laporan Selesai')
            ->call('baca', 'langsung', 'P-SENT')
            ->assertRedirect(url('/pesan/detail/P-SENT'));

        $this->assertNull(Pesan::whereKey('P-SENT')->first()->dibaca_at);
    }

    public function test_per_page_defaults_to_ten(): void
    {
        Livewire::actingAs($this->staff)->test(PesanTable::class)
            ->assertSet('perPage', 10)
            ->set('perPage', 25)
            ->assertSet('perPage', 25);
    }
}
