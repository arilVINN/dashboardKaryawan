<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrganizationChartPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_hrd_can_view_organization_chart_from_sidebar_route(): void
    {
        Role::create(['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD']);
        Divisi::create([
            'id_divisi' => 'DIV-HR',
            'kode_divisi' => 'HR',
            'nama_divisi' => 'Human Resources',
            'status_aktif' => 'Aktif',
        ]);
        Karyawan::create([
            'id_karyawan' => 'EMP-HRD',
            'nama' => 'HRD User',
            'jenis_kelamin' => 'Perempuan',
            'jabatan' => 'HRD Manager',
            'divisi_id_divisi' => 'DIV-HR',
        ]);
        $hrd = User2::create([
            'id_user' => 'USR-HRD',
            'username' => 'hrd',
            'password' => Hash::make('password'),
            'role_id_role' => 'ROLE-HRD',
            'karyawan_id_karyawan' => 'EMP-HRD',
        ]);

        $this->actingAs($hrd)
            ->get('/hrd/strukturOrganisasi')
            ->assertOk()
            ->assertSee('Struktur Organisasi')
            ->assertDontSee('PENGAWAS PERUSAHAAN')
            ->assertDontSee('PUBLIKASI BUKU')
            ->assertSee('aria-label="Bagan struktur organisasi"', false)
            ->assertSee('Profil Karyawan')
            ->assertSee('Tanggal Lahir')
            ->assertSee('Email')
            ->assertSee('data-employee=', false)
            ->assertSee("card.addEventListener('click'", false)
            ->assertSee('/hrd/strukturOrganisasi', false);
    }

    public function test_non_hrd_cannot_view_hrd_organization_chart(): void
    {
        $this->get('/hrd/strukturOrganisasi')->assertRedirect('/login');
    }
}
