<?php

namespace Tests\Feature;

use App\Models\ContentAccount;
use App\Models\ContentEvent;
use App\Models\ContentType;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use App\Support\ContentCalendarAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    private User2 $superadmin;

    private User2 $direktur;

    private User2 $hrd;

    private Divisi $dirut;

    private Divisi $hr;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'RL-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'RL-KADIV', 'nama_role' => 'kadiv'],
            ['id_role' => 'RL-HRD', 'nama_role' => 'hrd'],
            ['id_role' => 'RL-DIR', 'nama_role' => 'direktur'],
            ['id_role' => 'RL-SUPER', 'nama_role' => 'superadmin'],
        ]);

        $this->dirut = Divisi::create([
            'id_divisi' => 'DIV-DIRUT',
            'kode_divisi' => 'DIRUT',
            'nama_divisi' => 'Direktur Utama',
            'status_aktif' => 'Aktif',
        ]);
        $this->hr = Divisi::create([
            'id_divisi' => 'DIV-HR',
            'kode_divisi' => 'HR',
            'nama_divisi' => 'HR',
            'status_aktif' => 'Aktif',
        ]);

        $this->superadmin = $this->makeUser('USR-SUPER', 'superadmin', 'Super Admin', 'Superadmin', $this->dirut, 'RL-SUPER');
        $this->direktur = $this->makeUser('USR-DIR', 'direkturtest', 'Direktur', 'Direktur Utama', $this->dirut, 'RL-DIR');
        $this->hrd = $this->makeUser('USR-HRD', 'hrduser', 'HRD User', 'HRD Manager', $this->hr, 'RL-HRD');
    }

    public function test_superadmin_lands_on_the_direktur_dashboard_after_login(): void
    {
        $this->post('/login', [
            'username' => 'superadmin',
            'password' => 'rahasia123',
        ])->assertRedirect('/direktur/dashboard');

        $this->assertAuthenticatedAs($this->superadmin->fresh(), 'web');
    }

    public function test_superadmin_can_open_every_role_dashboard(): void
    {
        $this->actingAs($this->superadmin);

        $this->get('/direktur/dashboard')->assertOk();
        $this->get('/hrd/dashboard')->assertOk();
        $this->get('/kadiv/dashboard')->assertOk();
        $this->get('/kalender-konten')->assertOk();
    }

    public function test_superadmin_can_write_while_direktur_stays_read_only(): void
    {
        $payload = [
            'kode_divisi' => 'NEW',
            'nama_divisi' => 'Unit Baru',
        ];

        $this->actingAs($this->superadmin)
            ->postJson('/hrd/divisi', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('divisis', ['kode_divisi' => 'NEW']);

        $this->actingAs($this->direktur)
            ->postJson('/hrd/divisi', [
                'kode_divisi' => 'NEW2',
                'nama_divisi' => 'Unit Ditolak',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('divisis', ['kode_divisi' => 'NEW2']);
    }

    public function test_hrd_cannot_create_superadmin_through_the_staff_api(): void
    {
        $response = $this->actingAs($this->hrd)->postJson('/hrd/staff', [
            'nama' => 'Penyusup Superadmin',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-HR',
            'username' => 'penyusup',
            'password' => 'rahasia123',
            'role_id_role' => 'RL-SUPER',
        ]);

        $response->assertCreated();

        $this->assertSame(
            'RL-STAFF',
            User2::where('username', 'penyusup')->value('role_id_role')
        );
        $this->assertSame(1, User2::whereHas('role', fn ($q) => $q->where('nama_role', 'superadmin'))->count());
    }

    public function test_hrd_cannot_edit_deactivate_or_delete_a_superadmin(): void
    {
        $this->actingAs($this->hrd)
            ->putJson('/hrd/staff/EMP-USR-SUPER', ['nama' => 'Diubah HRD'])
            ->assertForbidden();

        $this->actingAs($this->hrd)
            ->deleteJson('/hrd/staff/EMP-USR-SUPER')
            ->assertForbidden();

        $this->assertDatabaseHas('karyawans', [
            'id_karyawan' => 'EMP-USR-SUPER',
            'nama' => 'Super Admin',
            'status_aktif' => 'Aktif',
        ]);
    }

    public function test_superadmin_command_creates_a_usable_account(): void
    {
        $this->artisan('superadmin:create', [
            'username' => 'rootadmin',
            '--name' => 'Root Admin',
            '--password' => 'rahasia123',
            '--division' => 'DIRUT',
        ])->assertExitCode(0);

        $this->assertDatabaseHas('roles', ['nama_role' => 'superadmin']);

        $user = User2::with(['role', 'karyawan'])->where('username', 'rootadmin')->first();

        $this->assertNotNull($user);
        $this->assertSame('superadmin', $user->role->nama_role);
        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->isDirektur());
        $this->assertSame('Superadmin', $user->karyawan->jabatan);
        $this->assertSame('DIV-DIRUT', $user->karyawan->divisi_id_divisi);
        $this->assertTrue(Hash::check('rahasia123', $user->password));

        // Re-running without --force is refused, --force updates the password.
        $this->artisan('superadmin:create', [
            'username' => 'rootadmin',
            '--password' => 'rahasia123',
            '--division' => 'DIRUT',
        ])->assertExitCode(1);

        $this->artisan('superadmin:create', [
            'username' => 'rootadmin',
            '--password' => 'barupassword',
            '--division' => 'DIRUT',
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertTrue(Hash::check('barupassword', $user->fresh()->password));
    }

    public function test_superadmin_has_full_content_calendar_access(): void
    {
        ContentType::insert([
            ['slug' => 'reels', 'name' => 'Reels', 'sort_order' => 1],
        ]);

        $account = ContentAccount::create([
            'slug' => 'akun-pb', 'name' => 'Akun PB', 'handle' => '@pb',
            'platform' => 'Instagram', 'sort_order' => 1, 'is_active' => true,
        ]);

        $event = ContentEvent::create([
            'title' => 'Konten Uji',
            'event_date' => '2026-11-12',
            'content_type' => 'reels',
            'content_account_id' => $account->id,
            'status' => 'process',
            'created_by' => $this->superadmin->id_user,
        ]);

        ContentCalendarAccess::flush();

        $this->assertTrue(ContentCalendarAccess::allows($this->superadmin));
        $this->assertTrue(ContentCalendarAccess::canManage($this->superadmin));

        $gate = Gate::forUser($this->superadmin);
        $this->assertTrue($gate->allows('view', $event));
        $this->assertTrue($gate->allows('create', ContentEvent::class));
        $this->assertTrue($gate->allows('update', $event));
        $this->assertTrue($gate->allows('delete', $event));
    }

    private function makeUser(
        string $id,
        string $username,
        string $nama,
        string $jabatan,
        Divisi $divisi,
        string $roleId
    ): User2 {
        Karyawan::create([
            'id_karyawan' => 'EMP-'.$id,
            'nama' => $nama,
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => $jabatan,
            'divisi_id_divisi' => $divisi->id_divisi,
            'status_aktif' => 'Aktif',
            'aktif' => true,
        ]);

        return User2::create([
            'id_user' => $id,
            'username' => $username,
            'password' => Hash::make('rahasia123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => 'EMP-'.$id,
        ]);
    }
}
