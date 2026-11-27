<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HrdDivisionFormDedupTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);
        Divisi::insert([
            ['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi', 'status_aktif' => 'aktif'],
            ['id_divisi' => 'DIV-EMPTY', 'kode_divisi' => 'EMP', 'nama_divisi' => 'Kosong', 'status_aktif' => 'aktif'],
        ]);

        $this->hrd = $this->createAccount('EMP-HRD', 'HRD', 'DIV-IT', 'ROLE-HRD', 'hrd');
    }

    private function createAccount(
        string $employeeId,
        string $name,
        string $divisionId,
        string $roleId,
        string $username,
    ): User2 {
        DB::table('karyawans')->insert([
            'id_karyawan' => $employeeId,
            'nama' => $name,
            'jenis_kelamin' => 'Laki-laki',
            'email' => $username.'@example.test',
            'jabatan' => $name,
            'divisi_id_divisi' => $divisionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User2::create([
            'id_user' => 'USR-'.$employeeId,
            'username' => $username,
            'password' => 'password',
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $employeeId,
        ]);
    }

    public function test_division_detail_uses_shared_karyawan_modal_with_locked_division(): void
    {
        $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->assertSee('modalTambahKaryawan', false)
            ->assertSee('Tambah Karyawan ke Kosong', false)
            ->assertSee('type="hidden" name="divisi_id_divisi" value="DIV-EMPTY"', false)
            ->assertDontSee('modalTambahAnggota', false)
            ->assertDontSee('bukaModalAnggota', false);
    }

    public function test_division_detail_row_buttons_target_shared_modal(): void
    {
        $this->createAccount('EMP-STAFF', 'Staff', 'DIV-EMPTY', 'ROLE-STAFF', 'staff');

        $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->assertSee('bukaEditKaryawan(this)', false)
            ->assertSee('hapusKaryawan(this)', false)
            ->assertSee('data-divisi="DIV-EMPTY"', false)
            ->assertDontSee('bukaEditAnggota', false)
            ->assertDontSee('hapusAnggota', false)
            ->assertDontSee('modalEditAnggota', false);
    }

    public function test_general_karyawan_page_keeps_unlocked_division_select(): void
    {
        $this->actingAs($this->hrd)
            ->get('/hrd/daftarKaryawan')
            ->assertOk()
            ->assertSee('modalTambahKaryawan', false)
            ->assertSee('<select name="divisi_id_divisi" required', false)
            ->assertDontSee('type="hidden" name="divisi_id_divisi"', false);
    }

    public function test_api_rejects_jabatan_from_another_division(): void
    {
        $jabatanLain = Jabatan::create([
            'division_id' => 'DIV-IT',
            'nama' => 'Staff',
        ]);

        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Calon Karyawan',
                'jenis_kelamin' => 'Laki-laki',
                'jabatan_id' => $jabatanLain->id,
                'divisi_id_divisi' => 'DIV-EMPTY',
                'username' => 'calonsatu',
                'password' => 'password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('jabatan');
    }

    public function test_api_accepts_jabatan_in_selected_division(): void
    {
        $jabatan = Jabatan::create([
            'division_id' => 'DIV-EMPTY',
            'nama' => 'Staff',
        ]);

        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Calon Karyawan',
                'jenis_kelamin' => 'Laki-laki',
                'jabatan_id' => $jabatan->id,
                'divisi_id_divisi' => 'DIV-EMPTY',
                'username' => 'calondua',
                'password' => 'password',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('karyawans', ['jabatan' => 'Staff', 'jabatan_id' => $jabatan->id]);
    }

    public function test_jabatan_names_are_normalised_and_scoped_to_division(): void
    {
        $pertama = $this->actingAs($this->hrd)->postJson('/hrd/staff', [
            'nama' => 'Calon Satu',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => '  Staff   Administrasi  ',
            'divisi_id_divisi' => 'DIV-EMPTY',
            'username' => 'calonnormsatu',
            'password' => 'password',
        ])->assertCreated();

        $this->assertDatabaseHas('jabatan', [
            'division_id' => 'DIV-EMPTY',
            'nama' => 'Staff Administrasi',
        ]);

        // The same name may exist in another division.
        $this->actingAs($this->hrd)->postJson('/hrd/staff', [
            'nama' => 'Calon Dua',
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff Administrasi',
            'divisi_id_divisi' => 'DIV-IT',
            'username' => 'calonnormdua',
            'password' => 'password',
        ])->assertCreated();

        $this->assertSame(2, Jabatan::where('nama', 'Staff Administrasi')->count());
    }

    public function test_form_uses_shared_jabatan_select(): void
    {
        $html = $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('<div id="modalTambahKaryawan"', $html);
        $this->assertStringContainsString('name="jabatan"', $html);
        $this->assertStringContainsString('Pilih divisi dulu', $html);
        $this->assertStringNotContainsString('id="formEditKaryawan"', $html);
    }

    public function test_create_persists_alamat(): void
    {
        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Calon Karyawan',
                'jenis_kelamin' => 'Laki-laki',
                'jabatan' => 'Staff',
                'alamat' => 'Jl. Merpati No. 10',
                'divisi_id_divisi' => 'DIV-EMPTY',
                'username' => 'calonalamat',
                'password' => 'password',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('karyawans', ['alamat' => 'Jl. Merpati No. 10']);
    }

    public function test_update_persists_alamat(): void
    {
        $id = $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Calon Karyawan',
                'jenis_kelamin' => 'Laki-laki',
                'jabatan' => 'Staff',
                'divisi_id_divisi' => 'DIV-EMPTY',
                'username' => 'calonalamatdua',
                'password' => 'password',
            ])
            ->assertCreated()
            ->json('data.karyawan.id_karyawan');

        $this->actingAs($this->hrd)
            ->putJson('/hrd/staff/'.$id, ['alamat' => 'Jl. Kenari No. 5'])
            ->assertOk();

        $this->assertDatabaseHas('karyawans', ['id_karyawan' => $id, 'alamat' => 'Jl. Kenari No. 5']);
    }

    public function test_shared_form_places_alamat_and_photo_before_kepegawaian(): void
    {
        $html = $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($html, 'wire:model.blur="alamat"'));
        $this->assertSame(1, substr_count($html, 'id="kf-foto"'));

        $chunk = substr($html, strpos($html, 'Data karyawan'));
        $chunk = substr($chunk, 0, strpos($chunk, 'Kepegawaian'));
        $this->assertStringContainsString('wire:model.blur="alamat"', $chunk);
        $this->assertStringContainsString('id="kf-foto"', $chunk);
    }

    public function test_create_accepts_tanggal_masuk(): void
    {
        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Calon Karyawan',
                'jenis_kelamin' => 'Laki-laki',
                'jabatan' => 'Staff',
                'tanggal_masuk' => '2026-02-01',
                'divisi_id_divisi' => 'DIV-EMPTY',
                'username' => 'calonrekrut',
                'password' => 'password',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('karyawans', ['tanggal_masuk' => '2026-02-01']);
    }

    public function test_form_uses_styled_date_picker_in_kepegawaian(): void
    {
        $html = $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-name="tanggalMasuk"', $html);
        $this->assertStringContainsString('placeholder="10 Nov 2026"', $html);
        $this->assertStringNotContainsString('type="date"', $html);
    }

    public function test_tanggal_masuk_lives_in_kepegawaian_section(): void
    {
        $html = $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->getContent();

        $dataChunk = substr($html, strpos($html, 'Data karyawan'));
        $dataChunk = substr($dataChunk, 0, strpos($dataChunk, 'Kepegawaian'));
        $this->assertStringNotContainsString('data-name="tanggalMasuk"', $dataChunk);

        $pegawaiChunk = substr($html, strpos($html, 'Kepegawaian'));
        $pegawaiChunk = substr($pegawaiChunk, 0, strpos($pegawaiChunk, 'Akun login'));
        $this->assertStringContainsString('data-name="tanggalMasuk"', $pegawaiChunk);
    }

    public function test_create_defaults_status_to_aktif(): void
    {
        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Calon Karyawan',
                'jenis_kelamin' => 'Laki-laki',
                'jabatan' => 'Staff',
                'divisi_id_divisi' => 'DIV-EMPTY',
                'username' => 'calonstatus',
                'password' => 'password',
            ])
            ->assertCreated();

        $karyawan = Karyawan::whereHas('user', fn ($q) => $q->where('username', 'calonstatus'))->first();
        $this->assertSame('Aktif', $karyawan->status_aktif);
    }

    public function test_create_accepts_tidak_aktif(): void
    {
        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Calon Karyawan',
                'jenis_kelamin' => 'Laki-laki',
                'jabatan' => 'Staff',
                'status_aktif' => 'Tidak Aktif',
                'divisi_id_divisi' => 'DIV-EMPTY',
                'username' => 'calonnonaktif',
                'password' => 'password',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('karyawans', ['status_aktif' => 'Tidak Aktif']);
    }

    public function test_update_status_roundtrip(): void
    {
        $id = $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Calon Karyawan',
                'jenis_kelamin' => 'Laki-laki',
                'jabatan' => 'Staff',
                'divisi_id_divisi' => 'DIV-EMPTY',
                'username' => 'calonstatusdua',
                'password' => 'password',
            ])
            ->assertCreated()
            ->json('data.karyawan.id_karyawan');

        $this->actingAs($this->hrd)
            ->putJson('/hrd/staff/'.$id, ['status_aktif' => 'Tidak Aktif'])
            ->assertOk();

        $this->assertSame('Tidak Aktif', Karyawan::find($id)->status_aktif);

        $this->actingAs($this->hrd)
            ->putJson('/hrd/staff/'.$id, ['status_aktif' => 'Rusak'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status_aktif');
    }

    public function test_shared_form_uses_status_select_in_kepegawaian(): void
    {
        $html = $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($html, 'id="kf-status"'));
        $pegawaiChunk = substr($html, strpos($html, 'Kepegawaian'));
        $pegawaiChunk = substr($pegawaiChunk, 0, strpos($pegawaiChunk, 'Akun login'));
        $this->assertStringContainsString('<option value="Aktif">Aktif</option>', $pegawaiChunk);
        $this->assertStringContainsString('<option value="Tidak Aktif">Tidak Aktif</option>', $pegawaiChunk);
    }
}
