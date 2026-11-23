<?php

namespace Tests\Feature\Livewire\Hrd;

use App\Livewire\Hrd\KaryawanForm;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class StaffFormFixesTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;
    private Jabatan $jabatanTarget;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'ROLE-ST', 'nama_role' => 'Staff'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'IT', 'status_aktif' => 'Aktif', 'tipe' => Divisi::TIPE_DIVISI]);
        Divisi::create(['id_divisi' => 'DIV-HR', 'kode_divisi' => 'HR', 'nama_divisi' => 'HR', 'status_aktif' => 'Aktif', 'tipe' => Divisi::TIPE_DIVISI]);
        $this->jabatanTarget = Jabatan::create(['division_id' => 'DIV-HR', 'nama' => 'Staff', 'aktif' => true]);

        Karyawan::create([
            'id_karyawan' => 'K-HRD', 'nama' => 'HRD', 'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'HRD', 'divisi_id_divisi' => 'DIV-HR',
        ]);
        $this->hrd = User2::create([
            'id_user' => 'U-HRD', 'username' => 'hrd', 'password' => Hash::make('x'),
            'role_id_role' => 'ROLE-HRD', 'karyawan_id_karyawan' => 'K-HRD',
        ]);
    }

    public function test_editing_division_persists(): void
    {
        $karyawan = $this->makeStaff();

        Livewire::actingAs($this->hrd)
            ->test(KaryawanForm::class)
            ->call('buka', 'edit', $karyawan->id_karyawan)
            ->set('divisiId', 'DIV-HR')
            ->set('jabatanId', $this->jabatanTarget->id)
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertSame('DIV-HR', $karyawan->fresh()->divisi_id_divisi);
    }

    public function test_editing_division_is_not_blocked_by_capacity(): void
    {
        Divisi::whereKey('DIV-HR')->update(['kapasitas' => 1]);
        Karyawan::create([
            'id_karyawan' => 'K-OTHER', 'nama' => 'Other', 'jenis_kelamin' => 'Perempuan',
            'jabatan' => 'Staff', 'divisi_id_divisi' => 'DIV-HR',
        ]);

        $karyawan = $this->makeStaff();

        Livewire::actingAs($this->hrd)
            ->test(KaryawanForm::class)
            ->call('buka', 'edit', $karyawan->id_karyawan)
            ->set('divisiId', 'DIV-HR')
            ->set('jabatanId', $this->jabatanTarget->id)
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertSame('DIV-HR', $karyawan->fresh()->divisi_id_divisi);
    }

    public function test_division_form_no_longer_has_kapasitas_or_urutan(): void
    {
        $html = (string) file_get_contents(resource_path('views/component_hrd/divisiModal.blade.php'));

        $this->assertStringNotContainsString('id="kapasitas"', $html);
        $this->assertStringNotContainsString('id="urutan"', $html);
        $this->assertStringNotContainsString('kapasitas:', $html);
        $this->assertStringNotContainsString('urutan:', $html);
        $this->assertStringNotContainsString('id="tipe"', $html);
        $this->assertStringNotContainsString('id="parent_id"', $html);
        $this->assertStringNotContainsString('TINGKATAN', $html);
        $this->assertStringNotContainsString('INDUK DIVISI', $html);
    }

    public function test_date_picker_initializes_view_month_when_empty(): void
    {
        $html = (string) file_get_contents(resource_path('views/livewire/hrd/karyawan-form.blade.php'));

        $this->assertStringContainsString('picker._kfView = new Date(start.getFullYear(), start.getMonth(), 1);', $html);
        $this->assertStringContainsString("picker.dataset.name === 'tanggalLahir'", $html);

        $picker = (string) file_get_contents(resource_path('views/components/karyawan-date-picker.blade.php'));
        $this->assertStringContainsString('data-kf-tahun', $picker);
        $this->assertStringContainsString('aria-label="Tahun"', $picker);
    }

    private function makeStaff(): Karyawan
    {
        $karyawan = Karyawan::create([
            'id_karyawan' => 'K-1', 'nama' => 'Andi', 'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff', 'divisi_id_divisi' => 'DIV-IT',
        ]);
        User2::create([
            'id_user' => 'U-1', 'username' => 'andi', 'password' => Hash::make('x'),
            'role_id_role' => 'ROLE-ST', 'karyawan_id_karyawan' => $karyawan->id_karyawan,
        ]);

        return $karyawan;
    }
}
