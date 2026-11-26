<?php

namespace Tests\Feature\Security;

use App\Models\CoaAkun;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\KPengeluaran;
use App\Models\KPermintDana;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regression test for the Phase 5 hardening: an approved request can only be
 * disbursed once, even when two submissions race or are repeated.
 */
class Phase5DisbursementRaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'kadiv'],
        ]);

        Divisi::insert([
            ['id_divisi' => 'DIV-A', 'kode_divisi' => 'A', 'nama_divisi' => 'Divisi A', 'status_aktif' => 'Aktif'],
            ['id_divisi' => 'DIV-FIN', 'kode_divisi' => 'FIN', 'nama_divisi' => 'Finance & Accounting', 'status_aktif' => 'Aktif'],
        ]);

        CoaAkun::insert([
            ['kode' => '1101', 'nama' => 'Kas', 'kelompok' => 'Aset', 'aktif' => true],
            ['kode' => '5102', 'nama' => 'Beban Operasional', 'kelompok' => 'Beban', 'aktif' => true],
        ]);
    }

    private function createUser(string $id, string $roleId, string $divisiId): User2
    {
        Karyawan::create([
            'id_karyawan' => 'K-'.$id,
            'nama' => $id,
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1990-01-01',
            'tanggal_masuk' => '2024-01-01',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => $divisiId,
            'status_aktif' => 'Aktif',
            'aktif' => true,
        ]);

        return User2::create([
            'id_user' => $id,
            'username' => strtolower($id),
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => 'K-'.$id,
        ]);
    }

    public function test_approved_request_can_only_be_disbursed_once(): void
    {
        $owner = $this->createUser('U-STA', 'ROLE-STAFF', 'DIV-A');
        $finance = $this->createUser('U-FIN', 'ROLE-KADIV', 'DIV-FIN');

        KPermintDana::create([
            'id_kpd' => 'KPD001',
            'nomor_pengajuan' => 'REQ001',
            'judul_pengajuan' => 'Perjalanan dinas',
            'keperluan' => 'Kunjungan klien.',
            'nominal_diajukan' => 1500000,
            'status' => KPermintDana::STATUS_DISETUJUI,
            'divisi_id_divisi' => 'DIV-A',
            'user_id_user' => $owner->id_user,
        ]);

        $payload = [
            'tanggal_cair' => now()->toDateString(),
            'metode_pembayaran' => 'Transfer Bank',
            'keterangan' => 'Pencairan pertama.',
        ];

        $this->actingAs($finance)
            ->post(route('kadiv.keuangan.cairkan', 'KPD001'), $payload)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(1, KPengeluaran::where('kpd_id_kpd', 'KPD001')->count());
        $this->assertSame(KPermintDana::STATUS_DICAIRKAN, KPermintDana::findOrFail('KPD001')->status);

        // A repeated (or concurrently arriving) request must not create a
        // second cash-out or journal entry.
        $this->actingAs($finance)
            ->post(route('kadiv.keuangan.cairkan', 'KPD001'), $payload)
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(1, KPengeluaran::where('kpd_id_kpd', 'KPD001')->count());
        $this->assertSame(1, \App\Models\JurnalEntry::where('source_type', KPengeluaran::class)
            ->where('source_id', KPengeluaran::firstOrFail()->id_kpengeluaran)
            ->count());
    }
}
