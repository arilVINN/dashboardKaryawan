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
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class KaryawanFormTest extends TestCase
{
    use RefreshDatabase;

    protected User2 $hrd;
    protected Jabatan $jabatan;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
        ]);

        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'tipe' => Divisi::TIPE_DIVISI,
            'status_aktif' => 'Aktif',
        ]);

        $this->jabatan = Jabatan::create([
            'division_id' => 'DIV-IT',
            'nama' => 'Staff',
            'role_id_role' => 'ROLE-STAFF',
        ]);

        $karyawanHrd = Karyawan::create([
            'id_karyawan' => 'KRY-HRD',
            'nama' => 'HRD User',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_masuk' => '2020-01-01',
            'jabatan' => 'HRD Manager',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        $this->hrd = User2::create([
            'id_user' => 'USR-HRD',
            'username' => 'hrduser',
            'password' => Hash::make('rahasia123'),
            'role_id_role' => 'ROLE-HRD',
            'karyawan_id_karyawan' => $karyawanHrd->id_karyawan,
        ]);
    }

    public function test_shared_form_can_create_an_employee_and_account(): void
    {
        Livewire::actingAs($this->hrd)
            ->test(KaryawanForm::class)
            ->call('buka', 'create')
            ->assertSet('show', true)
            ->set('nama', 'Budi Santoso')
            ->set('jenisKelamin', 'Laki-laki')
            ->set('email', 'budi@silindo.co.id')
            ->set('divisiId', 'DIV-IT')
            ->set('jabatanId', $this->jabatan->id)
            ->set('tanggalMasuk', '2026-01-10')
            ->set('username', 'budi.santoso')
            ->set('password', 'rahasia123')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('show', false);

        $this->assertDatabaseHas('karyawans', [
            'nama' => 'Budi Santoso',
            'jabatan' => 'Staff',
            'jabatan_id' => $this->jabatan->id,
        ]);

        $this->assertDatabaseHas('users2', [
            'username' => 'budi.santoso',
            'role_id_role' => 'ROLE-STAFF',
        ]);
    }

    public function test_changing_division_resets_jabatan_and_hints_about_it(): void
    {
        Divisi::create([
            'id_divisi' => 'DIV-HR',
            'kode_divisi' => 'HR',
            'nama_divisi' => 'HRD',
            'tipe' => Divisi::TIPE_DIVISI,
            'status_aktif' => 'Aktif',
        ]);

        $component = Livewire::actingAs($this->hrd)
            ->test(KaryawanForm::class)
            ->call('buka', 'create')
            ->set('divisiId', 'DIV-IT')
            ->set('jabatanId', $this->jabatan->id)
            ->set('divisiId', 'DIV-HR');

        $component->assertSet('jabatanId', null)
            ->assertSet('jabatanResetNote', 'Jabatan direset karena divisi berubah.');
    }

    public function test_invalid_dates_are_rejected(): void
    {
        Livewire::actingAs($this->hrd)
            ->test(KaryawanForm::class)
            ->call('buka', 'create')
            ->set('nama', 'Budi Santoso')
            ->set('jenisKelamin', 'Laki-laki')
            ->set('email', 'budi@silindo.co.id')
            ->set('divisiId', 'DIV-IT')
            ->set('jabatanId', $this->jabatan->id)
            ->set('tanggalMasuk', '2026-03-01')
            ->set('tanggalAkhirKontrak', '2026-02-01')
            ->set('username', 'budi.santoso')
            ->set('password', 'rahasia123')
            ->call('simpan')
            ->assertHasErrors('tanggalAkhirKontrak');
    }

    public function test_edit_mode_preselects_division_and_jabatan(): void
    {
        $karyawan = Karyawan::create([
            'id_karyawan' => 'KRY-EDIT',
            'nama' => 'Andi Wijaya',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_masuk' => '2025-01-01',
            'email' => 'andi@example.test',
            'jabatan' => 'Staff',
            'jabatan_id' => $this->jabatan->id,
            'divisi_id_divisi' => 'DIV-IT',
        ]);
        User2::create([
            'id_user' => 'USR-EDIT',
            'username' => 'andi.wijaya',
            'password' => Hash::make('rahasia123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => $karyawan->id_karyawan,
        ]);

        Livewire::actingAs($this->hrd)
            ->test(KaryawanForm::class)
            ->call('buka', 'edit', $karyawan->id_karyawan)
            ->assertSet('divisiId', 'DIV-IT')
            ->assertSet('jabatanId', $this->jabatan->id)
            ->assertSet('username', 'andi.wijaya')
            ->assertSet('hasAccount', true);
    }

    public function test_password_reset_sets_flag_and_writes_audit_log(): void
    {
        $karyawan = Karyawan::create([
            'id_karyawan' => 'KRY-PASS',
            'nama' => 'Siti Rahma',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_masuk' => '2025-01-01',
            'jabatan' => 'Staff',
            'jabatan_id' => $this->jabatan->id,
            'divisi_id_divisi' => 'DIV-IT',
        ]);
        $user = User2::create([
            'id_user' => 'USR-PASS',
            'username' => 'siti.rahma',
            'password' => Hash::make('rahasia123'),
            'role_id_role' => 'ROLE-STAFF',
            'karyawan_id_karyawan' => $karyawan->id_karyawan,
        ]);
        $versiLama = $user->session_version;

        Livewire::actingAs($this->hrd)
            ->test(KaryawanForm::class)
            ->call('buka', 'edit', $karyawan->id_karyawan)
            ->call('konfirmasiResetPassword')
            ->assertSet('showResetDialog', true)
            ->call('resetPassword')
            ->assertSet('showResetDialog', false);

        $user->refresh();
        $this->assertTrue($user->must_change_password);
        $this->assertNotSame($versiLama, $user->session_version);
        $this->assertDatabaseHas('audit_logs', [
            'aksi' => 'reset_password_karyawan',
            'target_id' => $karyawan->id_karyawan,
        ]);
    }


    public function test_divisi_and_jabatan_selects_sync_immediately(): void
    {
        $component = Livewire::actingAs($this->hrd)
            ->test(KaryawanForm::class)
            ->call('buka', 'create')
            ->assertSee('wire:model.live="divisiId"', false)
            ->assertSee('wire:model.live="jabatanId"', false)
            ->set('divisiId', 'DIV-IT');

        $html = $component->html();
        $pos = strpos($html, '<select id="kf-jabatan"');
        $tag = substr($html, $pos, strpos($html, '>', $pos) - $pos);
        $markup = substr($html, $pos, 2500);

        $this->assertSame(0, preg_match('/\sdisabled(?![\w:-])/', $tag));
        $this->assertStringContainsString('Pilih jabatan', $markup);
        $this->assertStringContainsString('Staff', $markup);
    }


    public function test_date_field_blur_validation_calls_a_component_method(): void
    {
        Livewire::actingAs($this->hrd)
            ->test(KaryawanForm::class)
            ->call('buka', 'create')
            ->assertSee('wire:blur="validateField(', false)
            ->call('validateField', 'tanggalMasuk')
            ->assertHasErrors('tanggalMasuk');
    }


    public function test_jobdesk_is_descriptive_and_saved_with_employee(): void
    {
        Livewire::actingAs($this->hrd)
            ->test(KaryawanForm::class)
            ->call('buka', 'create')
            ->set('nama', 'Budi Santoso')
            ->set('jenisKelamin', 'Laki-laki')
            ->set('email', 'budi@silindo.co.id')
            ->set('divisiId', 'DIV-IT')
            ->set('jabatanId', $this->jabatan->id)
            ->set('tanggalMasuk', '2026-01-10')
            ->set('username', 'budi.santoso')
            ->set('password', 'rahasia123')
            ->set('jobdesk', 'Editorial')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('karyawans', [
            'nama' => 'Budi Santoso',
            'jobdesk' => 'Editorial',
        ]);
        $this->assertDatabaseHas('users2', [
            'username' => 'budi.santoso',
            'role_id_role' => 'ROLE-STAFF',
        ]);
    }

}
