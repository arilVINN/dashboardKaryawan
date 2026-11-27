<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\User2;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DivisionHierarchySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DivisionHierarchyTest extends TestCase
{
    use RefreshDatabase;

    private function seedHierarchy(): void
    {
        $this->seed(DivisionHierarchySeeder::class);
    }

    private function seedCompany(): void
    {
        $this->seed(DatabaseSeeder::class);
    }

    public function test_seeder_builds_the_dummy_company_hierarchy(): void
    {
        $this->seedCompany();

        $this->assertSame(11, Divisi::count());

        $direktur = Divisi::where('tipe', Divisi::TIPE_DIREKTUR)->firstOrFail();
        $this->assertSame('Direktur Utama', $direktur->nama_divisi);
        $this->assertNull($direktur->parent_id);
        $this->assertSame(1, $direktur->level);
        $this->assertTrue($direktur->isSinglePerson());
        $this->assertSame('Hendra Wijaya', $direktur->penanggungJawab()?->nama);

        $manajemen = Divisi::where('tipe', Divisi::TIPE_MANAJEMEN)->firstOrFail();
        $this->assertSame('Manajemen', $manajemen->nama_divisi);
        $this->assertSame($direktur->id_divisi, $manajemen->parent_id);
        $this->assertSame(2, $manajemen->level);
        $this->assertTrue($manajemen->isGrouping());
        $this->assertFalse($manajemen->canAcceptMember());

        $hr = Divisi::where('kode_divisi', Divisi::KODE_HR)->firstOrFail();
        $finance = Divisi::where('kode_divisi', Divisi::KODE_FINANCE)->firstOrFail();

        foreach ([$hr, $finance] as $singleUnit) {
            $this->assertSame($manajemen->id_divisi, $singleUnit->parent_id);
            $this->assertSame(3, $singleUnit->level);
            $this->assertTrue($singleUnit->isSinglePerson());
            $this->assertSame(1, $singleUnit->karyawans()->count());
            $this->assertFalse($singleUnit->canAcceptMember());
        }

        $expectedDivisions = [
            'Kelas Kerjasama',
            'Publikasi Buku',
            'Publikasi Jurnal',
            'Academic Event',
            'Kreatif Digital',
            'IT',
            'MARKOM',
        ];

        foreach ($expectedDivisions as $name) {
            $division = Divisi::where('nama_divisi', $name)->firstOrFail();
            $this->assertSame($manajemen->id_divisi, $division->parent_id);
            $this->assertSame(3, $division->level);
            $this->assertNull($division->kapasitas);
            $this->assertTrue($division->canAcceptMember());
        }

        // Only the divisions that currently have a kadiv in the seeder can
        // resolve a penanggung jawab.
        foreach (['Publikasi Buku', 'Publikasi Jurnal'] as $name) {
            $division = Divisi::where('nama_divisi', $name)->firstOrFail();
            $this->assertNotNull($division->penanggungJawab());
            $this->assertTrue($division->penanggungJawab()->isKadiv());
        }

        $this->assertSame([
            'Direktur Utama',
            'Manajemen',
            'HR',
            'Finance & Accounting',
            'Kelas Kerjasama',
            'Publikasi Buku',
            'Publikasi Jurnal',
            'Academic Event',
            'Kreatif Digital',
            'IT',
            'MARKOM',
        ], Divisi::query()->ordered()->pluck('nama_divisi')->all());

        // The structural nodes are not part of the operating divisions used by
        // finance's per-division budget pages.
        $this->assertSame(8, Divisi::query()->unitNonFinance()->count());
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seedHierarchy();
        $this->seedHierarchy();

        $this->assertSame(11, Divisi::count());
    }

    public function test_single_person_and_grouping_units_reject_extra_members(): void
    {
        $this->seedCompany();

        $hrd = User2::where('username', 'agnes')->firstOrFail();

        $this->actingAs($hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Karyawan HR Kedua',
                'jenis_kelamin' => 'Perempuan',
                'jabatan' => 'Staff',
                'username' => 'hrkedua',
                'password' => 'password',
                'divisi_id_divisi' => 'DV002',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('divisi_id_divisi');

        $this->actingAs($hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Anggota Manajemen',
                'jenis_kelamin' => 'Laki-laki',
                'jabatan' => 'Staff',
                'username' => 'manajemenbaru',
                'password' => 'password',
                'divisi_id_divisi' => 'DV005',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('divisi_id_divisi');

        $this->actingAs($hrd)
            ->postJson('/hrd/staff', [
                'nama' => 'Staff Kelas Kerjasama',
                'jenis_kelamin' => 'Laki-laki',
                'jabatan' => 'Staff',
                'username' => 'staffkj',
                'password' => 'password',
                'divisi_id_divisi' => 'DV006',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('karyawans', [
            'nama' => 'Staff Kelas Kerjasama',
            'divisi_id_divisi' => 'DV006',
            'jabatan' => 'Staff',
        ]);
    }

    public function test_finance_access_and_budget_scope_follow_division_capability(): void
    {
        $this->seedCompany();

        $financeHead = User2::where('username', 'sarifin')->firstOrFail();
        $nonFinanceHead = User2::where('username', 'durotur')->firstOrFail();

        $this->assertTrue($financeHead->isKadivFinance());
        $this->assertFalse($nonFinanceHead->isKadivFinance());

        $nonFinance = Divisi::query()->unitNonFinance()->ordered()->pluck('nama_divisi')->all();

        $this->assertContains('HR', $nonFinance);
        $this->assertContains('IT', $nonFinance);
        $this->assertNotContains('Finance & Accounting', $nonFinance);
        $this->assertNotContains('Direktur Utama', $nonFinance);
        $this->assertNotContains('Manajemen', $nonFinance);
    }

    public function test_division_crud_keeps_the_hierarchy_consistent(): void
    {
        $this->seedCompany();

        $hrd = User2::where('username', 'agnes')->firstOrFail();

        $this->actingAs($hrd)
            ->postJson('/hrd/divisi', [
                'kode_divisi' => 'OPS',
                'nama_divisi' => 'Operasional Baru',
                'tipe' => Divisi::TIPE_DIVISI,
                'parent_id' => 'DV005',
            ])
            ->assertCreated()
            ->assertJsonPath('data.parent_id', 'DV005')
            ->assertJsonPath('data.level', 3);

        $this->actingAs($hrd)
            ->putJson('/hrd/divisi/DV005', ['parent_id' => 'DV005'])
            ->assertUnprocessable();

        $this->actingAs($hrd)
            ->putJson('/hrd/divisi/DV005', ['tipe' => Divisi::TIPE_DIVISI])
            ->assertUnprocessable();

        $this->actingAs($hrd)
            ->putJson('/hrd/divisi/DV006', ['tipe' => Divisi::TIPE_MANAJEMEN])
            ->assertStatus(409);

        $this->actingAs($hrd)
            ->deleteJson('/hrd/divisi/DV005')
            ->assertStatus(409);
    }
}
