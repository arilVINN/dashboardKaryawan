<?php

namespace Tests\Feature\Livewire\Hrd;

use App\Livewire\Hrd\ComposeMessage;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Pesan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ComposeMessageTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;
    private User2 $staff;
    private User2 $inactive;
    private User2 $otherHrd;

    protected function setUp(): void
    {
        parent::setUp();

        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'status_aktif' => 'Aktif',
        ]);

        Role::insert([
            ['id_role' => 'RL-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'RL-ST', 'nama_role' => 'Staff'],
            ['id_role' => 'RL-KD', 'nama_role' => 'Kadiv'],
        ]);

        $this->hrd = $this->makeUser('U-HRD', 'hrd.user', 'RL-HRD', 'K-HRD', 'HRD User');
        $this->staff = $this->makeUser('U-ST', 'staff.user', 'RL-ST', 'K-ST', 'Staff Satu');
        $this->inactive = $this->makeUser('U-INA', 'inactive.user', 'RL-ST', 'K-INA', 'Staff Nonaktif', 'Tidak Aktif');
        $this->otherHrd = $this->makeUser('U-HRD2', 'other.hrd', 'RL-HRD', 'K-HRD2', 'HRD Dua');
    }

    public function test_recipient_results_follow_the_existing_messaging_rule(): void
    {
        Livewire::actingAs($this->hrd)
            ->test(ComposeMessage::class)
            ->call('open')
            ->set('search', 'staff')
            ->assertSee('Staff Satu')
            ->assertDontSee('Staff Nonaktif')
            ->assertDontSee('HRD User')
            ->assertDontSee('HRD Dua');
    }

    public function test_policy_allows_only_eligible_recipients(): void
    {
        $this->assertTrue(Gate::forUser($this->hrd)->allows('create', [Pesan::class, $this->staff]));
        $this->assertFalse(Gate::forUser($this->hrd)->allows('create', [Pesan::class, $this->hrd]));
        $this->assertFalse(Gate::forUser($this->hrd)->allows('create', [Pesan::class, $this->inactive]));
        $this->assertFalse(Gate::forUser($this->hrd)->allows('create', [Pesan::class, $this->otherHrd]));
    }

    public function test_sending_creates_a_message_notifies_the_recipient_and_refreshes_the_list(): void
    {
        Livewire::actingAs($this->hrd)
            ->test(ComposeMessage::class)
            ->call('open')
            ->call('selectRecipient', $this->staff->id_user)
            ->set('isi', "Halo Staff,\nMohon konfirmasi jadwal terbit.")
            ->call('send')
            ->assertDispatched('app-toast', message: 'Pesan terkirim')
            ->assertDispatched('pesan-dikirim')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('pesans', [
            'pengirim_id_user' => $this->hrd->id_user,
            'penerima_id_user' => $this->staff->id_user,
            'deskripsi' => "Halo Staff,\nMohon konfirmasi jadwal terbit.",
            'tipe' => 'pesan',
        ]);
        $this->assertDatabaseHas('notifikasis', ['user_id_user' => $this->staff->id_user]);
    }

    public function test_message_body_is_limited_to_2000_characters(): void
    {
        Livewire::actingAs($this->hrd)
            ->test(ComposeMessage::class)
            ->call('open')
            ->call('selectRecipient', $this->staff->id_user)
            ->set('isi', str_repeat('a', 2001))
            ->call('send')
            ->assertHasErrors('isi');

        $this->assertDatabaseCount('pesans', 0);
    }

    private function makeUser(
        string $id,
        string $username,
        string $role,
        string $karyawanId,
        string $nama,
        string $status = 'Aktif'
    ): User2 {
        Karyawan::create([
            'id_karyawan' => $karyawanId,
            'nama' => $nama,
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
            'status_aktif' => $status,
        ]);

        return User2::create([
            'id_user' => $id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $role,
            'karyawan_id_karyawan' => $karyawanId,
        ]);
    }
}
