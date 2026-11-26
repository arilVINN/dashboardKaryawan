<?php

namespace Tests\Feature\Livewire;

use App\Livewire\TowerDatabaseTable;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\TowerDatabase;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class TowerDatabaseTableTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staffIt;

    private User2 $kadivIt;

    private User2 $hrd;

    private Divisi $divisiIt;

    private Divisi $divisiHr;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);

        $this->divisiIt = Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'status_aktif' => 'Aktif',
        ]);

        $this->divisiHr = Divisi::create([
            'id_divisi' => 'DIV-HR',
            'kode_divisi' => 'HR',
            'nama_divisi' => 'Human Resources',
            'status_aktif' => 'Aktif',
        ]);

        $this->staffIt = $this->makeUser('KRY-IT-1', 'Staff IT', 'DIV-IT', 'ROLE-STAFF', 'staff.it');
        $this->kadivIt = $this->makeUser('KRY-IT-2', 'Kadiv IT', 'DIV-IT', 'ROLE-KADIV', 'kadiv.it');
        $this->hrd = $this->makeUser('KRY-HR-1', 'HRD User', 'DIV-HR', 'ROLE-HRD', 'hrd.user');
    }

    private function makeUser(string $id, string $nama, string $divisiId, string $roleId, string $username): User2
    {
        Karyawan::create([
            'id_karyawan' => $id,
            'nama' => $nama,
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1995-01-01',
            'tanggal_masuk' => '2026-01-01',
            'no_telepon' => '081234567890',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => $divisiId,
            'status_aktif' => 'Aktif',
        ]);

        return User2::create([
            'id_user' => 'USR-'.$id,
            'username' => $username,
            'password' => Hash::make('password123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $id,
        ]);
    }

    public function test_staff_sees_general_and_own_division_files_but_not_other_divisions(): void
    {
        TowerDatabase::create([
            'nama' => 'SOP Umum Perusahaan',
            'jenis' => 'link',
            'url_link' => 'https://example.com/sop-umum',
            'divisi_id_divisi' => null, // General
        ]);

        TowerDatabase::create([
            'nama' => 'Dokumen Arsitektur IT',
            'jenis' => 'link',
            'url_link' => 'https://example.com/arsitektur-it',
            'divisi_id_divisi' => 'DIV-IT', // Khusus IT
        ]);

        TowerDatabase::create([
            'nama' => 'Gaji dan Benefit HR',
            'jenis' => 'link',
            'url_link' => 'https://example.com/hr-benefit',
            'divisi_id_divisi' => 'DIV-HR', // Khusus HR
        ]);

        Livewire::actingAs($this->staffIt)
            ->test(TowerDatabaseTable::class)
            ->assertSee('SOP Umum Perusahaan')
            ->assertSee('Dokumen Arsitektur IT')
            ->assertDontSee('Gaji dan Benefit HR');
    }

    public function test_staff_can_filter_by_general_and_own_division(): void
    {
        TowerDatabase::create([
            'nama' => 'Panduan Libur Nasional',
            'jenis' => 'link',
            'url_link' => 'https://example.com/libur',
            'divisi_id_divisi' => null,
        ]);

        TowerDatabase::create([
            'nama' => 'Kredensial Server Database IT',
            'jenis' => 'nomor',
            'nilai_kontak' => '0811223344',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        // Filter General
        Livewire::actingAs($this->staffIt)
            ->test(TowerDatabaseTable::class)
            ->set('filterAkses', 'general')
            ->assertSee('Panduan Libur Nasional')
            ->assertDontSee('Kredensial Server Database IT');

        // Filter Divisi Saya
        Livewire::actingAs($this->staffIt)
            ->test(TowerDatabaseTable::class)
            ->set('filterAkses', 'my-division')
            ->assertSee('Kredensial Server Database IT')
            ->assertDontSee('Panduan Libur Nasional');
    }

    public function test_hrd_can_see_all_files_and_filter_by_any_division(): void
    {
        TowerDatabase::create([
            'nama' => 'File Umum HR & IT',
            'jenis' => 'link',
            'url_link' => 'https://example.com/umum',
            'divisi_id_divisi' => null,
        ]);

        TowerDatabase::create([
            'nama' => 'File Internal IT Only',
            'jenis' => 'link',
            'url_link' => 'https://example.com/it',
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        TowerDatabase::create([
            'nama' => 'File Internal HR Only',
            'jenis' => 'link',
            'url_link' => 'https://example.com/hr',
            'divisi_id_divisi' => 'DIV-HR',
        ]);

        // HRD sees all 3
        Livewire::actingAs($this->hrd)
            ->test(TowerDatabaseTable::class)
            ->assertSee('File Umum HR & IT')
            ->assertSee('File Internal IT Only')
            ->assertSee('File Internal HR Only');

        // HRD filters by IT
        Livewire::actingAs($this->hrd)
            ->test(TowerDatabaseTable::class)
            ->set('filterAkses', 'DIV-IT')
            ->assertSee('File Internal IT Only')
            ->assertDontSee('File Internal HR Only')
            ->assertDontSee('File Umum HR & IT');
    }

    public function test_kadiv_can_create_general_and_division_restricted_items(): void
    {
        // 1. Kadiv IT creates General item
        Livewire::actingAs($this->kadivIt)
            ->test(TowerDatabaseTable::class)
            ->set('formJenis', 'link')
            ->set('formNama', 'Portal Intranet General')
            ->set('formUrlLink', 'https://intranet.silindo.test')
            ->set('formAkses', 'general')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tower_databases', [
            'nama' => 'Portal Intranet General',
            'divisi_id_divisi' => null,
        ]);

        // 2. Kadiv IT creates division-restricted item
        Livewire::actingAs($this->kadivIt)
            ->test(TowerDatabaseTable::class)
            ->set('formJenis', 'link')
            ->set('formNama', 'Akses Server Khusus IT')
            ->set('formUrlLink', 'https://server.it.silindo.test')
            ->set('formAkses', 'divisi')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tower_databases', [
            'nama' => 'Akses Server Khusus IT',
            'divisi_id_divisi' => 'DIV-IT',
        ]);
    }

    public function test_staff_cannot_download_restricted_file_of_another_division(): void
    {
        Storage::fake('public');
        $fakeFile = UploadedFile::fake()->create('rahasia_hr.pdf', 100);
        $path = $fakeFile->store('tower_files', 'public');

        $hrItem = TowerDatabase::create([
            'nama' => 'Laporan Rahasia HR',
            'jenis' => 'file',
            'file_path' => $path,
            'file_name' => 'rahasia_hr.pdf',
            'divisi_id_divisi' => 'DIV-HR',
        ]);

        Livewire::actingAs($this->staffIt)
            ->test(TowerDatabaseTable::class)
            ->call('downloadFile', $hrItem->id)
            ->assertSee('Anda tidak memiliki izin akses');
    }

    public function test_kadiv_cannot_edit_or_delete_another_division_item(): void
    {
        $hrItem = TowerDatabase::create([
            'nama' => 'Arsip Rahasia HR',
            'jenis' => 'link',
            'url_link' => 'https://hr.example.com',
            'divisi_id_divisi' => 'DIV-HR',
        ]);

        // Kadiv IT tries to edit HR item
        Livewire::actingAs($this->kadivIt)
            ->test(TowerDatabaseTable::class)
            ->call('bukaModalEdit', $hrItem->id)
            ->assertSee('Anda tidak memiliki hak untuk mengedit data ini.');

        // Kadiv IT tries to delete HR item
        Livewire::actingAs($this->kadivIt)
            ->test(TowerDatabaseTable::class)
            ->call('hapus', $hrItem->id)
            ->assertSee('Anda tidak memiliki hak untuk menghapus data ini.');

        $this->assertDatabaseHas('tower_databases', ['id' => $hrItem->id]);
    }

    public function test_reset_filters_clears_search_jenis_and_akses(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(TowerDatabaseTable::class)
            ->set('search', 'kata kunci')
            ->set('jenis', 'file')
            ->set('filterAkses', 'general')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('jenis', '')
            ->assertSet('filterAkses', '');
    }
}
