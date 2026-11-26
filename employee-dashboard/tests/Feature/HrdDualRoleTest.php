<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HrdDualRoleTest extends TestCase
{
    use RefreshDatabase;

    private User2 $direktur;
    private User2 $hrd;
    private Karyawan $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'R-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'R-KADIV', 'nama_role' => 'kadiv'],
            ['id_role' => 'R-HRD', 'nama_role' => 'hrd'],
            ['id_role' => 'R-DIR', 'nama_role' => 'direktur'],
            ['id_role' => 'R-SUPER', 'nama_role' => 'superadmin'],
        ]);

        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'tipe' => Divisi::TIPE_DIVISI,
            'status_aktif' => 'Aktif',
        ]);
        Jabatan::create([
            'division_id' => 'DIV-IT',
            'nama' => 'Staff',
            'role_id_role' => 'R-STAFF',
        ]);

        $this->direktur = $this->buatUser('K-DIR', 'Direktur', 'R-DIR');
        $this->hrd = $this->buatUser('K-HRD', 'HRD', 'R-HRD');
        $this->karyawan = Karyawan::create([
            'id_karyawan' => 'K-STAFF',
            'nama' => 'Staff Satu',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => 'DIV-IT',
        ]);
        $this->buatUser('K-STAFF', 'Staff Satu', 'R-STAFF', 'staffsatu');
    }

    private function buatUser(string $id, string $nama, string $role, ?string $username = null): User2
    {
        if (! Karyawan::whereKey($id)->exists()) {
            Karyawan::create([
                'id_karyawan' => $id,
                'nama' => $nama,
                'jenis_kelamin' => 'Laki-laki',
                'jabatan' => $nama,
                'divisi_id_divisi' => 'DIV-IT',
            ]);
        }

        return User2::create([
            'id_user' => 'U-'.$id,
            'username' => $username ?? strtolower(str_replace(' ', '', $nama)),
            'password' => Hash::make('rahasia123'),
            'role_id_role' => $role,
            'karyawan_id_karyawan' => $id,
        ]);
    }

    public function test_has_role_checks_primary_and_additional_roles(): void
    {
        $user = User2::where('username', 'staffsatu')->firstOrFail();
        $this->assertFalse($user->hasRole('kadiv'));

        $user->roles()->attach('R-KADIV');
        $user->unsetRelation('roles');
        $user->load('roles');

        $this->assertTrue($user->hasRole('staff'));
        $this->assertTrue($user->hasRole('kadiv'));
        $this->assertTrue($user->hasAnyRole(['hrd', 'kadiv']));
        $this->assertSame(['kadiv'], $user->peranTambahan());
    }

    public function test_additional_role_grants_middleware_access(): void
    {
        $user = User2::where('username', 'staffsatu')->firstOrFail();
        $user->roles()->attach('R-KADIV');

        $this->actingAs($user)
            ->get('/kadiv/dashboard')
            ->assertOk();
    }

    public function test_direktur_can_assign_additional_role_but_hrd_cannot(): void
    {
        $this->actingAs($this->hrd)
            ->put(route('hrd.karyawan.peran', $this->karyawan->id_karyawan), ['roles' => ['R-KADIV']])
            ->assertForbidden();

        $this->actingAs($this->direktur)
            ->put(route('hrd.karyawan.peran', $this->karyawan->id_karyawan), ['roles' => ['R-KADIV']])
            ->assertRedirect(route('hrd.detailKaryawan', $this->karyawan->id_karyawan));

        $user = User2::where('username', 'staffsatu')->firstOrFail();
        $this->assertTrue($user->fresh()->roles()->where('roles.id_role', 'R-KADIV')->exists());

        $this->assertDatabaseHas('audit_logs', [
            'aksi' => 'ubah_role_tambahan',
            'target_id' => $this->karyawan->id_karyawan,
        ]);
    }

    public function test_superadmin_cannot_be_granted_and_primary_role_is_ignored(): void
    {
        $this->actingAs($this->direktur)
            ->put(route('hrd.karyawan.peran', $this->karyawan->id_karyawan), [
                'roles' => ['R-SUPER', 'R-STAFF'],
            ])->assertRedirect();

        $user = User2::where('username', 'staffsatu')->firstOrFail()->fresh();
        $this->assertSame([], $user->roles()->pluck('roles.id_role')->all());
    }

    public function test_jabatan_page_lists_jabatan_and_rejects_superadmin_role(): void
    {
        $this->actingAs($this->hrd)
            ->get(route('hrd.jabatan.index'))
            ->assertOk()
            ->assertSee('Manajemen Jabatan')
            ->assertSee('Staff')
            ->assertSee('Tambah jabatan');

        $this->actingAs($this->hrd)
            ->postJson(route('hrd.jabatan.store'), [
                'division_id' => 'DIV-IT',
                'nama' => 'Kepala IT',
                'role_id_role' => 'R-SUPER',
            ])->assertUnprocessable()->assertJsonValidationErrors('role_id_role');
    }

    public function test_employee_form_no_longer_has_add_jabatan_dialog(): void
    {
        $html = $this->actingAs($this->hrd)
            ->get(route('hrd.daftarKaryawan'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('buatJabatanDialog', $html);
        $this->assertStringNotContainsString('Tambah jabatan baru', $html);
    }
}
