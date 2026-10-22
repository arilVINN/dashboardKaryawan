<?php

namespace Tests\Feature\Livewire\Kadiv;

use App\Livewire\Kadiv\TugasTable;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class TugasTableTest extends TestCase
{
    use RefreshDatabase;

    private User2 $kadiv;
    private User2 $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);

        $this->kadiv = $this->account('KD-1', 'Kadiv IT', 'ROLE-KADIV', 'kadiv.it');
        $this->account('ST-1', 'Staff One', 'ROLE-STAFF', 'staff.one');
        $this->account('ST-2', 'Staff Two', 'ROLE-STAFF', 'staff.two');
        $this->staff = User2::where('username', 'staff.one')->first();

        $this->task('T-1', 'ST-1', 'Alpha');
        $this->task('T-2', 'ST-1', 'Gamma');
    }

    private function account(string $id, string $nama, string $role, string $username, string $divisionId = 'DIV-IT'): User2
    {
        Karyawan::create(['id_karyawan' => $id, 'nama' => $nama, 'jenis_kelamin' => 'Laki-laki', 'jabatan' => $nama, 'divisi_id_divisi' => $divisionId]);

        return User2::create([
            'id_user' => 'USR-' . $id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $role,
            'karyawan_id_karyawan' => $id,
        ]);
    }

    private function task(string $id, string $karyawanId, string $judul): void
    {
        Tugas::create([
            'id_tugas' => $id,
            'karyawan_id_karyawan' => $karyawanId,
            'judul_tugas' => $judul,
            'deadline' => now()->addDays(5)->toDateString(),
            'status' => Tugas::STATUS_BARU,
            'progress' => '0',
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
    }

    public function test_searches_and_creates_task(): void
    {
        Livewire::actingAs($this->kadiv)->test(TugasTable::class)
            ->set('search', 'alpha')
            ->assertSee('Alpha')
            ->assertDontSee('Gamma');

        Livewire::actingAs($this->kadiv)->test(TugasTable::class)
            ->set('judul', 'Tugas X')
            ->set('deskripsi', 'd')
            ->set('tenggat', now()->addWeek()->toDateTimeString())
            ->set('recipient', 'ST-1')
            ->call('createTask')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tugas', ['karyawan_id_karyawan' => 'ST-1', 'judul_tugas' => 'Tugas X']);
    }

    public function test_create_for_semua_assigns_to_every_division_staff(): void
    {
        Livewire::actingAs($this->kadiv)->test(TugasTable::class)
            ->set('judul', 'Bulk')
            ->set('deskripsi', 'd')
            ->set('tenggat', now()->addWeek()->toDateTimeString())
            ->set('recipient', 'semua')
            ->call('createTask')
            ->assertHasNoErrors();

        $this->assertSame(2, Tugas::where('judul_tugas', 'Bulk')->count());
    }

    public function test_non_kadiv_is_forbidden(): void
    {
        Livewire::actingAs($this->staff)->test(TugasTable::class)->assertForbidden();
    }

    public function test_create_for_semua_with_empty_division_fails(): void
    {
        Divisi::create(['id_divisi' => 'DIV-EMPTY', 'kode_divisi' => 'EMP', 'nama_divisi' => 'Kosong', 'status_aktif' => 'Aktif']);
        Karyawan::create(['id_karyawan' => 'KD-2', 'nama' => 'Kadiv Kosong', 'jenis_kelamin' => 'Laki-laki', 'jabatan' => 'Kadiv', 'divisi_id_divisi' => 'DIV-EMPTY']);
        $kadiv2 = User2::create([
            'id_user' => 'USR-KD-2', 'username' => 'kadiv.kosong', 'password' => Hash::make('pass123'),
            'role_id_role' => 'ROLE-KADIV', 'karyawan_id_karyawan' => 'KD-2',
        ]);

        Livewire::actingAs($kadiv2)->test(TugasTable::class)
            ->set('judul', 'Bulk')
            ->set('deskripsi', 'd')
            ->set('tenggat', now()->addWeek()->toDateTimeString())
            ->set('recipient', 'semua')
            ->call('createTask')
            ->assertHasErrors('recipient');

        $this->assertDatabaseMissing('tugas', ['judul_tugas' => 'Bulk']);
    }

    public function test_project_can_assign_cross_division_staff_but_not_hrd_or_kadiv(): void
    {
        Divisi::create(['id_divisi' => 'DIV-MKT', 'kode_divisi' => 'MKT', 'nama_divisi' => 'Marketing', 'status_aktif' => 'Aktif']);
        $staffMarketing = $this->account('ST-MKT', 'Staff Marketing', 'ROLE-STAFF', 'staff.marketing', 'DIV-MKT');
        $this->account('HRD-1', 'HRD Marketing', 'ROLE-HRD', 'hrd.marketing', 'DIV-MKT');
        $this->account('KD-MKT', 'Kadiv Marketing', 'ROLE-KADIV', 'kadiv.marketing', 'DIV-MKT');
        Storage::fake('public');

        Livewire::actingAs($this->kadiv)->test(TugasTable::class)
            ->set('activeTab', 'project')
            ->assertSee('Staff Marketing (Marketing)')
            ->assertDontSee('HRD Marketing')
            ->assertDontSee('Kadiv Marketing')
            ->set('namaProject', 'Project Lintas Divisi')
            ->set('deskripsiProject', 'Deskripsi project untuk tim Marketing.')
            ->set('projectFile', UploadedFile::fake()->create('brief.pdf', 10, 'application/pdf'))
            ->set('taskLabels', ['Riset'])
            ->set('taskKaryawan', ['ST-MKT'])
            ->call('createProject')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('project_tasks', [
            'label' => 'Riset',
            'karyawan_id_karyawan' => 'ST-MKT',
        ]);

        $project = \App\Models\ProjectModel::where('nama_project', 'Project Lintas Divisi')->firstOrFail();
        $this->assertSame('Deskripsi project untuk tim Marketing.', $project->deskripsi);
        $this->assertSame('brief.pdf', $project->file_name);
        Storage::disk('public')->assertExists($project->file_path);

        Auth::logout();
        $this->withToken($staffMarketing->createToken('test')->plainTextToken)
            ->getJson('/api/staff/projects')
            ->assertOk()
            ->assertJsonPath('data.0.deskripsi', 'Deskripsi project untuk tim Marketing.')
            ->assertJsonPath('data.0.file_name', 'brief.pdf')
            ->assertJsonPath('data.0.id_project', $project->id_project);

        $this->withToken($staffMarketing->createToken('project-detail')->plainTextToken)
            ->getJson('/api/staff/projects/' . $project->id_project)
            ->assertOk()
            ->assertJsonPath('data.nama_project', 'Project Lintas Divisi')
            ->assertJsonPath('data.tasks.0.label', 'Riset')
            ->assertJsonPath('data.tasks.0.file_hasil_name', null);

        $submissionToken = $staffMarketing->createToken('project-submission')->plainTextToken;
        $this->withToken($submissionToken)
            ->postJson('/api/staff/projects/task/' . $project->tasks()->firstOrFail()->id_task . '/submit', [
                'file_hasil' => UploadedFile::fake()->create('hasil-riset.pdf', 10, 'application/pdf'),
            ])
            ->assertOk()
            ->assertJsonPath('data.file_hasil_name', 'hasil-riset.pdf');

        $projectTask = $project->tasks()->firstOrFail()->fresh();
        $this->assertNotNull($projectTask->tanggal_submit);
        Storage::disk('public')->assertExists($projectTask->file_hasil_path);

        $this->withToken($staffMarketing->createToken('project-detail-after-submit')->plainTextToken)
            ->getJson('/api/staff/projects/' . $project->id_project)
            ->assertOk()
            ->assertJsonPath('data.tasks.0.file_hasil_name', 'hasil-riset.pdf');

        Auth::guard('web')->logout();
        Auth::forgetGuards();
        $this->withToken($this->staff->createToken('cannot-submit-project-task')->plainTextToken)
            ->postJson('/api/staff/projects/task/' . $projectTask->id_task . '/submit', [
                'file_hasil' => UploadedFile::fake()->create('hasil-lain.pdf', 10, 'application/pdf'),
            ])
            ->assertNotFound();

        $this->actingAs($staffMarketing)
            ->get('/project/detail/' . $project->id_project)
            ->assertOk()
            ->assertSee('Detail Project');

        Auth::guard('web')->logout();
        Auth::forgetGuards();
        $this->withToken($this->staff->createToken('other-staff-project')->plainTextToken)
            ->getJson('/api/staff/projects/' . $project->id_project)
            ->assertNotFound();

        Livewire::actingAs($this->kadiv)->test(TugasTable::class)
            ->set('namaProject', 'Project Tidak Valid')
            ->set('taskLabels', ['Task HRD'])
            ->set('taskKaryawan', ['HRD-1'])
            ->call('createProject')
            ->assertHasErrors('taskKaryawan.0');

        $this->assertDatabaseMissing('projects', ['nama_project' => 'Project Tidak Valid']);

        Livewire::actingAs($this->kadiv)->test(TugasTable::class)
            ->set('namaProject', 'Project Untuk Kadiv')
            ->set('taskLabels', ['Task Kadiv'])
            ->set('taskKaryawan', ['KD-MKT'])
            ->call('createProject')
            ->assertHasErrors('taskKaryawan.0');

        $this->assertDatabaseMissing('projects', ['nama_project' => 'Project Untuk Kadiv']);
    }
}
