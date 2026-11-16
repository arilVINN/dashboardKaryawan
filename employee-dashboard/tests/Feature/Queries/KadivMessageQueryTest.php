<?php

namespace Tests\Feature\Queries;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Pesan;
use App\Models\Role;
use App\Models\User2;
use App\Queries\KadivMessageQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KadivMessageQueryTest extends TestCase
{
    use RefreshDatabase;

    private User2 $kadiv;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
        ]);
        Divisi::create(['id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT', 'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif']);

        $this->kadiv = $this->account('KD-1', 'Kadiv IT', 'ROLE-KADIV', 'kadiv.it');
        $abby = $this->account('ST-A', 'Abby', 'ROLE-STAFF', 'abby');
        $zed = $this->account('ST-Z', 'Zed', 'ROLE-STAFF', 'zed');

        $this->message('P-1', 'Laporan Bulanan', $abby->id_user, $this->kadiv->id_user);
        $this->message('P-2', 'Rapat', $zed->id_user, $this->kadiv->id_user);
        $this->message('P-3', 'Balasan', $this->kadiv->id_user, $abby->id_user);
    }

    private function account(string $id, string $nama, string $role, string $username): User2
    {
        Karyawan::create(['id_karyawan' => $id, 'nama' => $nama, 'jenis_kelamin' => 'Laki-laki', 'jabatan' => $nama, 'divisi_id_divisi' => 'DIV-IT']);

        return User2::create([
            'id_user' => 'USR-' . $id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $role,
            'karyawan_id_karyawan' => $id,
        ]);
    }

    private function message(string $id, string $judul, string $pengirim, string $penerima): void
    {
        Pesan::create([
            'id_pesan' => $id,
            'judul_pesan' => $judul,
            'deskripsi' => $judul . ' body',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-09-30',
            'pengirim_id_user' => $pengirim,
            'penerima_id_user' => $penerima,
        ]);
    }

    public function test_sorts_filters_and_searches(): void
    {
        $this->assertSame(
            ['P-3', 'P-1', 'P-2'],
            KadivMessageQuery::forUser($this->kadiv)->apply(['sort' => 'judul', 'dir' => 'asc'])->pluck('id_pesan')->all()
        );

        $this->assertSame(
            ['P-1', 'P-3', 'P-2'],
            KadivMessageQuery::forUser($this->kadiv)->apply(['sort' => 'pengirim', 'dir' => 'asc'])->pluck('id_pesan')->all()
        );

        $this->assertSame(
            ['P-1', 'P-2'],
            KadivMessageQuery::forUser($this->kadiv)->apply(['arah' => 'masuk'])->pluck('id_pesan')->sort()->values()->all()
        );

        DB::statement('PRAGMA case_sensitive_like = ON');
        $this->assertSame(
            ['P-1'],
            KadivMessageQuery::forUser($this->kadiv)->apply(['q' => 'laporan'])->pluck('id_pesan')->all()
        );
    }
}
