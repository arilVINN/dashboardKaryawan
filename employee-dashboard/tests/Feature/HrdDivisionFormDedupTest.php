<?php

namespace Tests\Feature;

use App\Models\Divisi;
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

    public function test_api_rejects_unlisted_jabatan(): void
    {
        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Calon Karyawan',
                'jenis_kelamin' => 'Laki-laki',
                'jabatan' => 'Kepala Divisi HR',
                'divisi_id_divisi' => 'DIV-EMPTY',
                'username' => 'calonsatu',
                'password' => 'password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('jabatan');
    }

    public function test_api_accepts_listed_jabatan(): void
    {
        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Calon Karyawan',
                'jenis_kelamin' => 'Laki-laki',
                'jabatan' => 'Staff',
                'divisi_id_divisi' => 'DIV-EMPTY',
                'username' => 'calondua',
                'password' => 'password',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('karyawans', ['jabatan' => 'Staff']);
    }

    public function test_api_rejects_lowercase_variant(): void
    {
        // The `in:` rule is exact-case; the UI select always sends exact values.
        $this->actingAs($this->hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Calon Karyawan',
                'jenis_kelamin' => 'Laki-laki',
                'jabatan' => 'staff',
                'divisi_id_divisi' => 'DIV-EMPTY',
                'username' => 'calontiga',
                'password' => 'password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('jabatan');
    }

    public function test_modals_render_jabatan_select_from_fixed_list(): void
    {
        $html = $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('<select name="jabatan"', $html);
        $this->assertStringNotContainsString('type="text" name="jabatan"', $html);
        foreach (Karyawan::JABATAN_OPTIONS as $option) {
            $this->assertStringContainsString('<option value="'.$option.'">'.$option.'</option>', $html);
        }
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

        $this->assertDatabaseHas('karyawans', [
            'alamat' => 'Jl. Merpati No. 10',
        ]);
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

        $this->assertDatabaseHas('karyawans', [
            'id_karyawan' => $id,
            'alamat' => 'Jl. Kenari No. 5',
        ]);
    }

    public function test_modals_have_alamat_and_photo_in_first_section(): void
    {
        $html = $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->getContent();

        // Alamat textarea in both create and edit forms.
        $this->assertSame(2, substr_count($html, 'name="alamat"'));

        // Within the create form, alamat + foto come before the Kepegawaian section.
        $createChunk = substr($html, strpos($html, 'formTambahKaryawan'));
        $createChunk = substr($createChunk, 0, strpos($createChunk, 'Kepegawaian'));
        $this->assertStringContainsString('name="alamat"', $createChunk);
        $this->assertStringContainsString('input-foto-karyawan', $createChunk);

        // Same for the edit form.
        $editChunk = substr($html, strpos($html, 'formEditKaryawan'));
        $editChunk = substr($editChunk, 0, strpos($editChunk, 'Kepegawaian'));
        $this->assertStringContainsString('name="alamat"', $editChunk);
        $this->assertStringContainsString('input-foto-edit-karyawan', $editChunk);
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

        $this->assertDatabaseHas('karyawans', [
            'tanggal_masuk' => '2026-02-01',
        ]);
    }

    public function test_modals_offer_tanggal_masuk_date_input(): void
    {
        $html = $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->getContent();

        $this->assertSame(2, substr_count($html, 'type="date" name="tanggal_masuk"'));

        $createChunk = substr($html, strpos($html, 'formTambahKaryawan'));
        $createChunk = substr($createChunk, 0, strpos($createChunk, 'Kepegawaian'));

        // Email and phone share a row: telepon, then email, with
        // no other field between them.
        $posTelepon = strpos($createChunk, 'name="no_telepon"');
        $posEmail = strpos($createChunk, 'name="email"');
        $this->assertLessThan($posEmail, $posTelepon);
        $between = substr($createChunk, $posTelepon, $posEmail - $posTelepon);
        $this->assertSame(1, substr_count($between, 'name="'));
    }

    public function test_tanggal_masuk_lives_in_kepegawaian_section(): void
    {
        $html = $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->getContent();

        foreach (['formTambahKaryawan', 'formEditKaryawan'] as $formId) {
            $chunk = substr($html, strpos($html, $formId));
            $pegawaiChunk = substr($chunk, 0, strpos($chunk, 'Kepegawaian'));
            $this->assertStringNotContainsString('name="tanggal_masuk"', $pegawaiChunk);

            $kepegawaianChunk = substr($chunk, strpos($chunk, 'Kepegawaian'));
            $kepegawaianChunk = substr($kepegawaianChunk, 0, strpos($kepegawaianChunk, 'Akun Login'));
            $this->assertStringContainsString('type="date" name="tanggal_masuk"', $kepegawaianChunk);
        }
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

    public function test_modals_offer_status_select_in_kepegawaian(): void
    {
        $html = $this->actingAs($this->hrd)
            ->get('/hrd/detailDivisi/DIV-EMPTY')
            ->assertOk()
            ->getContent();

        $this->assertSame(2, substr_count($html, '<select name="status_aktif"'));

        foreach (['formTambahKaryawan', 'formEditKaryawan'] as $formId) {
            $chunk = substr($html, strpos($html, $formId));
            $kepegawaianChunk = substr($chunk, strpos($chunk, 'Kepegawaian'));
            $kepegawaianChunk = substr($kepegawaianChunk, 0, strpos($kepegawaianChunk, 'Akun Login'));
            $this->assertStringContainsString('<select name="status_aktif"', $kepegawaianChunk);
            $this->assertStringContainsString('<option value="Aktif">Aktif</option>', $kepegawaianChunk);
            $this->assertStringContainsString('<option value="Tidak Aktif">Tidak Aktif</option>', $kepegawaianChunk);
        }
    }
}
