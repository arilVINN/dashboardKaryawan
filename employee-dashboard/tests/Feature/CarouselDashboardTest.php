<?php

namespace Tests\Feature;

use App\Models\Carousel;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CarouselDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;
    private User2 $kadiv;
    private User2 $otherDivisionKadiv;
    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'Staff'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'Kadiv'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'HRD'],
        ]);

        foreach ([
            ['DIV-IT', 'IT', 'Teknologi Informasi'],
            ['DIV-MKT', 'MKT', 'Pemasaran'],
        ] as [$id, $code, $name]) {
            Divisi::create([
                'id_divisi' => $id,
                'kode_divisi' => $code,
                'nama_divisi' => $name,
                'status_aktif' => 'Aktif',
            ]);
        }

        $this->staff = $this->createUser('USR-STAFF', 'ROLE-STAFF', 'DIV-IT');
        $this->kadiv = $this->createUser('USR-KADIV', 'ROLE-KADIV', 'DIV-IT');
        $this->otherDivisionKadiv = $this->createUser('USR-KADIV-MKT', 'ROLE-KADIV', 'DIV-MKT');
        $this->hrd = $this->createUser('USR-HRD', 'ROLE-HRD', 'DIV-IT');
        $this->createUser('USR-STAFF-OTHER', 'ROLE-STAFF', 'DIV-MKT');

        Carousel::create([
            'title' => 'Info divisi IT',
            'category' => 'Pengumuman',
            'date' => now()->toDateString(),
            'user_id_user' => $this->kadiv->id_user,
            'divisi_id_divisi' => 'DIV-IT',
        ]);
        Carousel::create([
            'title' => 'Info divisi Pemasaran',
            'category' => 'Pengumuman',
            'date' => now()->toDateString(),
            'user_id_user' => $this->staff->id_user,
            'divisi_id_divisi' => 'DIV-MKT',
        ]);
        Carousel::create([
            'title' => 'Info tanpa divisi',
            'category' => 'Pengumuman',
            'date' => now()->toDateString(),
            'user_id_user' => $this->hrd->id_user,
            'divisi_id_divisi' => null,
        ]);
        Carousel::create([
            'title' => 'Quotes divisi IT',
            'category' => 'Quotes',
            'date' => now()->toDateString(),
            'user_id_user' => $this->kadiv->id_user,
            'divisi_id_divisi' => 'DIV-IT',
        ]);
        Carousel::create([
            'title' => 'Quotes divisi Pemasaran',
            'category' => 'Quotes',
            'date' => now()->toDateString(),
            'user_id_user' => 'USR-STAFF-OTHER',
            'divisi_id_divisi' => 'DIV-MKT',
        ]);
        Carousel::create([
            'title' => 'Quotes HRD untuk semua divisi',
            'category' => 'Quotes',
            'date' => now()->toDateString(),
            'user_id_user' => $this->hrd->id_user,
            'divisi_id_divisi' => 'DIV-IT',
        ]);

    }

    public function test_staff_and_hrd_see_all_carousels_while_kadiv_sees_own_division(): void
    {
        $this->actingAs($this->staff)->get('/')
            ->assertOk()
            ->assertViewHas('carousels', fn ($items) => $items->count() === 5)
            ->assertSee('Info divisi IT')
            ->assertSee('Dibuat oleh USR-KADIV')
            ->assertSee('Teknologi Informasi')
            ->assertSee('Info divisi Pemasaran')
            ->assertSee('Pemasaran')
            ->assertSee('Info tanpa divisi')
            ->assertSee('Seluruh divisi')
            ->assertSee('Quotes divisi IT')
            ->assertDontSee('Quotes divisi Pemasaran')
            ->assertSee('Quotes HRD untuk semua divisi')
            ->assertDontSee('Tambah Konten')
            ->assertDontSee('id="carouselModal"', false);

        $this->actingAs($this->kadiv)->get('/kadiv/dashboard')
            ->assertOk()
            ->assertViewHas('carousels', fn ($items) => $items->count() === 4)
            ->assertSee('Info divisi IT')
            ->assertSee('Quotes divisi IT')
            ->assertSee('Quotes HRD untuk semua divisi')
            ->assertSee('Info tanpa divisi')
            ->assertDontSee('Info divisi Pemasaran')
            ->assertDontSee('Quotes divisi Pemasaran');

        $this->actingAs($this->otherDivisionKadiv)->get('/kadiv/dashboard')
            ->assertOk()
            ->assertViewHas('carousels', fn ($items) => $items->count() === 4)
            ->assertSee('Info divisi Pemasaran')
            ->assertSee('Quotes divisi Pemasaran')
            ->assertSee('Quotes HRD untuk semua divisi')
            ->assertSee('Info tanpa divisi')
            ->assertDontSee('Info divisi IT')
            ->assertDontSee('Quotes divisi IT');

        $this->actingAs($this->hrd)->get('/hrd/dashboard')
            ->assertOk()
            ->assertViewHas('carousels', fn ($items) => $items->count() === 6)
            ->assertSee('Info divisi IT')
            ->assertSee('Dibuat oleh USR-KADIV')
            ->assertSee('Teknologi Informasi')
            ->assertSee('Info divisi Pemasaran')
            ->assertSee('Dibuat oleh USR-STAFF')
            ->assertSee('Pemasaran')
            ->assertSee('Info tanpa divisi')
            ->assertSee('Seluruh divisi')
            ->assertSee('Quotes divisi IT')
            ->assertSee('Quotes divisi Pemasaran')
            ->assertSee('Quotes HRD untuk semua divisi')
            ->assertSee('Tambah Konten')
            ->assertSee('carouselModal');
    }

    public function test_staff_cannot_add_carousel_content(): void
    {
        $this->actingAs($this->staff)
            ->post('/kadiv/carousel', [
                'category' => 'Pengumuman',
                'title' => 'Konten staff',
                'date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('carousels', ['title' => 'Konten staff']);
    }

    public function test_kadiv_cannot_delete_another_division_carousel(): void
    {
        $otherDivisionCarousel = Carousel::where('title', 'Info divisi Pemasaran')->firstOrFail();

        $this->actingAs($this->kadiv)
            ->delete('/kadiv/carousel/' . $otherDivisionCarousel->id)
            ->assertRedirect()
            ->assertSessionHas('error', 'Anda tidak dapat menghapus konten yang dibuat oleh orang lain.');

        $this->assertDatabaseHas('carousels', ['id' => $otherDivisionCarousel->id]);
    }

    public function test_kadiv_cannot_delete_another_users_content_in_their_division(): void
    {
        $otherUserCarousel = Carousel::where('title', 'Quotes HRD untuk semua divisi')->firstOrFail();

        $this->actingAs($this->kadiv)->get('/kadiv/dashboard')
            ->assertOk()
            ->assertSee('Quotes HRD untuk semua divisi')
            ->assertDontSee('action="' . route('carousel.destroy', $otherUserCarousel->id) . '"', false);

        $this->actingAs($this->kadiv)
            ->delete('/kadiv/carousel/' . $otherUserCarousel->id)
            ->assertRedirect()
            ->assertSessionHas('error', 'Anda tidak dapat menghapus konten yang dibuat oleh orang lain.');

        $this->assertDatabaseHas('carousels', ['id' => $otherUserCarousel->id]);
    }

    public function test_birthday_popup_is_shown_on_the_day_and_saved_as_a_notification_not_a_carousel_slide(): void
    {
        Carbon::setTestNow('2026-11-12 10:00:00');
        try {
            $birthdayEmployee = $this->createUser('USR-BIRTHDAY', 'ROLE-STAFF', 'DIV-IT');
            $birthdayEmployee->karyawan()->update([
                'nama' => 'Karyawan Ulang Tahun',
                'tanggal_lahir' => '1995-11-12',
                'foto' => 'profiles/birthday.png',
            ]);

            $upcomingBirthday = $this->createUser('USR-UPCOMING', 'ROLE-STAFF', 'DIV-IT');
            $upcomingBirthday->karyawan()->update([
                'nama' => 'Ulang Tahun Besok',
                'tanggal_lahir' => '1995-11-13',
            ]);

            Carousel::query()->delete();

            $this->actingAs($this->staff)->get('/')
                ->assertOk()
                ->assertViewHas('birthdayEmployees', fn ($employees) => $employees->pluck('nama')->all() === ['Karyawan Ulang Tahun'])
                ->assertSee('id="birthday-popup"', false)
                ->assertSee('Ada yang berulang tahun!')
                ->assertSee('Karyawan Ulang Tahun')
                ->assertSee('storage/profiles/birthday.png')
                ->assertDontSee('Selamat Ulang Tahun!')
                ->assertDontSee('Ulang Tahun Besok');

            $this->assertDatabaseHas('notifikasis', [
                'judul_notifikasi' => 'Selamat Ulang Tahun!',
                'user_id_user' => $this->staff->id_user,
                'tanggal_notifikasi' => '2026-11-12',
            ]);

            $this->actingAs($this->staff)
                ->getJson('/api/staff/dashboard')
                ->assertOk()
                ->assertJsonFragment([
                    'judul_notifikasi' => 'Selamat Ulang Tahun!',
                    'isi_notif' => 'Hari ini Karyawan Ulang Tahun dari divisi Teknologi Informasi berulang tahun.',
                ]);

            Carbon::setTestNow('2026-11-13 00:01:00');

            $this->actingAs($this->staff)->get('/')
                ->assertOk()
                ->assertViewHas('birthdayEmployees', fn ($employees) => $employees->pluck('nama')->all() === ['Ulang Tahun Besok'])
                ->assertDontSee('Karyawan Ulang Tahun')
                ->assertSee('Ulang Tahun Besok')
                ->assertSee('Ada yang berulang tahun!')
                ->assertDontSee('Selamat Ulang Tahun!');

            $this->actingAs($this->staff)
                ->getJson('/api/staff/dashboard')
                ->assertOk()
                ->assertJsonMissing([
                    'isi_notif' => 'Hari ini Karyawan Ulang Tahun dari divisi Teknologi Informasi berulang tahun.',
                ]);

            $this->actingAs($this->staff)
                ->getJson('/api/staff/notifikasi')
                ->assertOk()
                ->assertJsonMissing([
                    'isi_notif' => 'Hari ini Karyawan Ulang Tahun dari divisi Teknologi Informasi berulang tahun.',
                ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createUser(string $id, string $roleId, string $divisionId): User2
    {
        $employeeId = 'EMP-' . substr($id, 4);
        Karyawan::create([
            'id_karyawan' => $employeeId,
            'nama' => $id,
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => $roleId === 'ROLE-KADIV' ? 'Kepala Divisi' : 'Staff',
            'divisi_id_divisi' => $divisionId,
        ]);

        return User2::create([
            'id_user' => $id,
            'username' => strtolower($id),
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $employeeId,
        ]);
    }
}
