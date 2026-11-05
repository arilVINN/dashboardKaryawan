<?php

namespace Tests\Feature\Livewire\Staff;

use App\Events\PesanDikirim;
use App\Livewire\Staff\ComposeMessage;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ComposeMessageTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;

    private User2 $otherStaff;

    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PesanDikirim::class]);

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
        ]);

        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'status_aktif' => 'Aktif',
        ]);
        Divisi::create([
            'id_divisi' => 'DIV-HR',
            'kode_divisi' => 'HR',
            'nama_divisi' => 'Human Resource',
            'status_aktif' => 'Aktif',
        ]);

        $this->staff = $this->account('KRY-S', 'Staff Satu', 'DIV-IT', 'ROLE-STAFF', 'staff.satu');
        $this->otherStaff = $this->account('KRY-O', 'Staff Dua', 'DIV-IT', 'ROLE-STAFF', 'staff.dua');
        $this->hrd = $this->account('KRY-H', 'HR Satu', 'DIV-HR', 'ROLE-HRD', 'hr.satu');
    }

    private function account(
        string $employeeId,
        string $name,
        string $divisionId,
        string $roleId,
        string $username
    ): User2 {
        Karyawan::create([
            'id_karyawan' => $employeeId,
            'nama' => $name,
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '2000-01-01',
            'tanggal_masuk' => '2026-01-10',
            'no_telepon' => '081234567890',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => $divisionId,
        ]);

        return User2::create([
            'id_user' => 'USR-'.$employeeId,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $employeeId,
        ]);
    }

    public function test_modal_stays_open_when_division_changes(): void
    {
        Livewire::actingAs($this->staff)
            ->test(ComposeMessage::class)
            ->dispatch('open-compose-message')
            ->assertSet('showModal', true)
            ->set('divisiId', 'DIV-IT')
            ->assertSet('showModal', true)
            ->assertSee('Staff Dua')
            ->call('closeModal')
            ->assertSet('showModal', false);
    }

    public function test_recipients_are_filtered_by_selected_division(): void
    {
        Livewire::actingAs($this->staff)
            ->test(ComposeMessage::class)
            ->set('divisiId', 'DIV-IT')
            ->assertSee('Staff Dua')
            ->assertDontSee('HR Satu');
    }

    public function test_staff_can_send_a_message_to_a_user_in_the_selected_division(): void
    {
        Livewire::actingAs($this->staff)
            ->test(ComposeMessage::class)
            ->set('divisiId', 'DIV-IT')
            ->set('recipientUserId', $this->otherStaff->id_user)
            ->set('judul', 'Koordinasi')
            ->set('isi', 'Mohon bantuannya untuk koordinasi.')
            ->call('send')
            ->assertHasNoErrors()
            ->assertRedirect(route('pesan'));

        $this->assertDatabaseHas('pesans', [
            'pengirim_id_user' => $this->staff->id_user,
            'penerima_id_user' => $this->otherStaff->id_user,
            'judul_pesan' => 'Koordinasi',
            'deskripsi' => 'Mohon bantuannya untuk koordinasi.',
            'tipe' => 'pesan',
        ]);

        Event::assertDispatched(PesanDikirim::class);
    }

    public function test_staff_cannot_send_a_message_to_themselves(): void
    {
        Livewire::actingAs($this->staff)
            ->test(ComposeMessage::class)
            ->set('divisiId', 'DIV-IT')
            ->set('recipientUserId', $this->staff->id_user)
            ->set('judul', 'Diri sendiri')
            ->set('isi', 'Tidak boleh.')
            ->call('send')
            ->assertHasErrors('recipientUserId');

        $this->assertDatabaseCount('pesans', 0);
    }

    public function test_staff_cannot_spoof_a_recipient_from_another_division(): void
    {
        Livewire::actingAs($this->staff)
            ->test(ComposeMessage::class)
            ->set('divisiId', 'DIV-IT')
            ->set('recipientUserId', $this->hrd->id_user)
            ->set('judul', 'Lintas divisi')
            ->set('isi', 'Tidak boleh.')
            ->call('send')
            ->assertHasErrors('recipientUserId');

        $this->assertDatabaseCount('pesans', 0);
    }

    public function test_all_fields_are_required(): void
    {
        Livewire::actingAs($this->staff)
            ->test(ComposeMessage::class)
            ->call('send')
            ->assertHasErrors(['divisiId', 'recipientUserId', 'judul', 'isi']);
    }
}
