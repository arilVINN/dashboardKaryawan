<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KalenderTugasLayoutTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;

    private User2 $kadiv;

    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'RL-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'RL-KADIV', 'nama_role' => 'kadiv'],
            ['id_role' => 'RL-HRD', 'nama_role' => 'hrd'],
        ]);

        $divisi = Divisi::create([
            'id_divisi' => 'DV-HR',
            'kode_divisi' => 'HR',
            'nama_divisi' => 'Human Resources',
            'status_aktif' => 'aktif',
        ]);

        $this->staff = $this->makeUser('USR-STAFF', 'KRY-STAFF', 'RL-STAFF', $divisi, 'staff.kalender');
        $this->kadiv = $this->makeUser('USR-KADIV', 'KRY-KADIV', 'RL-KADIV', $divisi, 'kadiv.kalender');
        $this->hrd = $this->makeUser('USR-HRD', 'KRY-HRD', 'RL-HRD', $divisi, 'hrd.kalender');
    }

    private function makeUser(string $userId, string $karyawanId, string $roleId, Divisi $divisi, string $username): User2
    {
        Karyawan::create([
            'id_karyawan' => $karyawanId,
            'nama' => ucfirst(str_replace('.', ' ', $username)),
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '2000-01-01',
            'tanggal_masuk' => '2026-01-10',
            'no_telepon' => '081234567890',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => $divisi->id_divisi,
        ]);

        return User2::create([
            'id_user' => $userId,
            'username' => $username,
            'password' => 'password',
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $karyawanId,
            'last_login_at' => '2026-11-04 08:00:00',
        ]);
    }

    public function test_calendar_toolbars_sit_above_the_calendar_card_for_every_role(): void
    {
        foreach ([
            'staff' => [$this->staff, '/kalender'],
            'kadiv' => [$this->kadiv, '/kadiv/kalender'],
            'hrd' => [$this->hrd, '/hrd/kalender'],
        ] as $role => [$user, $url]) {
            $html = $this->actingAs($user)->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('<div class="cal-nav-group">', $html, $role);
            $this->assertStringContainsString('id="calendar-title"', $html, $role);
            $this->assertStringContainsString('class="cal-view-group"', $html, $role);
            $this->assertStringContainsString('class="cal-note-btn"', $html, $role);

            $cardStart = strpos($html, 'class="calendar-card');
            $mainEnd = strpos($html, '</main>', $cardStart);

            $this->assertNotFalse($cardStart, $role);
            $this->assertNotFalse($mainEnd, $role);

            $cardHtml = substr($html, $cardStart, $mainEnd - $cardStart);

            $this->assertStringContainsString('id="calendar"', $cardHtml, $role);
            $this->assertStringNotContainsString('cal-nav-group', $cardHtml, $role);
            $this->assertStringNotContainsString('cal-view-group', $cardHtml, $role);
            $this->assertStringNotContainsString('cal-note-btn', $cardHtml, $role);
        }
    }
}
