<?php

namespace Tests\Feature;

use App\Livewire\DailyJournalTable;
use App\Models\DailyJournal;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class DailyJournalAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private User2 $kadiv;

    private User2 $hrd;

    private User2 $direktur;

    private User2 $staff;

    private User2 $otherKadiv;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'kadiv'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'hrd'],
            ['id_role' => 'ROLE-DIR', 'nama_role' => 'direktur'],
        ]);

        Divisi::insert([
            ['id_divisi' => 'DIV-A', 'kode_divisi' => 'A', 'nama_divisi' => 'Divisi A', 'status_aktif' => 'Aktif'],
            ['id_divisi' => 'DIV-B', 'kode_divisi' => 'B', 'nama_divisi' => 'Divisi B', 'status_aktif' => 'Aktif'],
        ]);

        $this->createEmployee('EMP-KADIV-A', 'Kadiv A', 'DIV-A', 'Kepala Divisi');
        $this->createEmployee('EMP-STAFF-A', 'Staff A', 'DIV-A', 'Staff');
        $this->createEmployee('EMP-KADIV-B', 'Kadiv B', 'DIV-B', 'Kepala Divisi');
        $this->createEmployee('EMP-HRD', 'HRD User', 'DIV-A', 'HRD');
        $this->createEmployee('EMP-DIR', 'Direktur User', 'DIV-A', 'Direktur Utama');

        $this->kadiv = $this->createUser('USR-KADIV-A', 'kadiva', 'ROLE-KADIV', 'EMP-KADIV-A');
        $this->staff = $this->createUser('USR-STAFF-A', 'staffa', 'ROLE-STAFF', 'EMP-STAFF-A');
        $this->otherKadiv = $this->createUser('USR-KADIV-B', 'kadivb', 'ROLE-KADIV', 'EMP-KADIV-B');
        $this->hrd = $this->createUser('USR-HRD', 'hrduser', 'ROLE-HRD', 'EMP-HRD');
        $this->direktur = $this->createUser('USR-DIR', 'direktur', 'ROLE-DIR', 'EMP-DIR');
    }

    public function test_kadiv_can_assign_to_an_employee_in_their_division_only(): void
    {
        Livewire::actingAs($this->kadiv)
            ->test(DailyJournalTable::class)
            ->set('targetKaryawanId', 'EMP-STAFF-A')
            ->set('aktivitas', 'Menyiapkan laporan divisi')
            ->call('saveJournal')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('daily_journals', [
            'user_id_user' => 'USR-STAFF-A',
            'karyawan_id_karyawan' => 'EMP-STAFF-A',
            'assigned_by_user_id' => 'USR-KADIV-A',
            'aktivitas' => 'Menyiapkan laporan divisi',
        ]);

        Livewire::actingAs($this->kadiv)
            ->test(DailyJournalTable::class)
            ->set('targetKaryawanId', 'EMP-KADIV-B')
            ->set('aktivitas', 'Tugas lintas divisi')
            ->call('saveJournal')
            ->assertSee('Anda tidak memiliki akses untuk menugaskan To Do kepada karyawan tersebut.');

        $this->assertDatabaseMissing('daily_journals', [
            'aktivitas' => 'Tugas lintas divisi',
        ]);
    }

    public function test_hrd_and_director_can_assign_to_kadiv_and_staff(): void
    {
        Livewire::actingAs($this->hrd)
            ->test(DailyJournalTable::class)
            ->call('setViewMode', 'all')
            ->set('targetKaryawanId', 'EMP-KADIV-B')
            ->set('aktivitas', 'Review dari HRD')
            ->call('saveJournal')
            ->assertHasNoErrors();

        Livewire::actingAs($this->direktur)
            ->test(DailyJournalTable::class)
            ->call('setViewMode', 'all')
            ->set('targetKaryawanId', 'EMP-STAFF-A')
            ->set('aktivitas', 'Prioritas dari Direktur')
            ->call('saveJournal')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('daily_journals', [
            'user_id_user' => 'USR-KADIV-B',
            'assigned_by_user_id' => 'USR-HRD',
            'aktivitas' => 'Review dari HRD',
        ]);
        $this->assertDatabaseHas('daily_journals', [
            'user_id_user' => 'USR-STAFF-A',
            'assigned_by_user_id' => 'USR-DIR',
            'aktivitas' => 'Prioritas dari Direktur',
        ]);
    }

    public function test_director_can_open_assignment_controls_from_the_todo_page(): void
    {
        $this->actingAs($this->direktur)
            ->get('/jurnal')
            ->assertOk()
            ->assertSee('Monitoring Karyawan');

        Livewire::actingAs($this->direktur)
            ->test(DailyJournalTable::class)
            ->call('setViewMode', 'all')
            ->set('filterKaryawanId', 'EMP-STAFF-A')
            ->call('openCreateModal')
            ->assertSet('targetKaryawanId', 'EMP-STAFF-A')
            ->assertSee('Penerima To Do')
            ->assertSee('Staff A — STAFF');
    }

    public function test_recipient_sees_who_assigned_the_todo(): void
    {
        $journal = DailyJournal::create([
            'user_id_user' => $this->staff->id_user,
            'karyawan_id_karyawan' => $this->staff->karyawan_id_karyawan,
            'assigned_by_user_id' => $this->kadiv->id_user,
            'tanggal' => now()->toDateString(),
            'aktivitas' => 'Tugas dari kepala divisi',
            'prioritas' => 'sedang',
            'status' => 'pending',
        ]);

        Livewire::actingAs($this->staff)
            ->test(DailyJournalTable::class)
            ->assertSee('Dari: Kadiv A (KADIV)');

        Livewire::actingAs($this->staff)
            ->test(DailyJournalTable::class)
            ->call('deleteJournal', $journal->id)
            ->assertSee('To Do ini ditugaskan oleh atasan dan hanya dapat dihapus oleh pemberi tugas.');

        $this->assertDatabaseHas('daily_journals', ['id' => $journal->id]);
    }

    public function test_staff_cannot_switch_to_team_monitoring(): void
    {
        Livewire::actingAs($this->staff)
            ->test(DailyJournalTable::class)
            ->call('setViewMode', 'all')
            ->assertSet('viewMode', 'my')
            ->assertSee('Anda tidak memiliki akses ke mode To Do tersebut.');
    }

    private function createEmployee(string $id, string $name, string $divisionId, string $position): void
    {
        Karyawan::create([
            'id_karyawan' => $id,
            'nama' => $name,
            'jenis_kelamin' => 'Laki-laki',
            'email' => strtolower(str_replace('-', '.', $id)).'@example.test',
            'jabatan' => $position,
            'divisi_id_divisi' => $divisionId,
        ]);
    }

    private function createUser(string $id, string $username, string $roleId, string $employeeId): User2
    {
        return User2::create([
            'id_user' => $id,
            'username' => $username,
            'password' => Hash::make('secret123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $employeeId,
        ]);
    }
}
