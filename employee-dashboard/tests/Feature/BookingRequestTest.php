<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BookingRequestTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staff;
    private User2 $hrd;
    private User2 $direksi;
    private User2 $kadiv;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'hrd'],
            ['id_role' => 'ROLE-DIR', 'nama_role' => 'direksi'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'kadiv'],
        ]);
        Divisi::create([
            'id_divisi' => 'DIV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi',
            'status_aktif' => 'Aktif',
        ]);

        $this->staff = $this->createUser('STAFF', 'ROLE-STAFF', 'Staff Satu');
        $this->hrd = $this->createUser('HRD', 'ROLE-HRD', 'HRD Satu');
        $this->direksi = $this->createUser('DIR', 'ROLE-DIR', 'Direksi Satu');
        $this->kadiv = $this->createUser('KADIV', 'ROLE-KADIV', 'Kadiv Satu');
    }

    public function test_overlapping_room_and_vehicle_bookings_are_blocked(): void
    {
        $this->actingAs($this->staff)
            ->post('/booking', $this->bookingData())
            ->assertRedirect()
            ->assertSessionHas('success', 'Permintaan booking berhasil dikirim dan menunggu persetujuan.');
        $this->actingAs($this->staff)
            ->from('/pesan')
            ->post('/booking', $this->bookingData(['jam_mulai' => '10:30', 'jam_selesai' => '11:30']))
            ->assertRedirect('/pesan')
            ->assertSessionHasErrors('jam_mulai');

        $this->actingAs($this->staff)
            ->post('/booking', $this->bookingData(['kategori' => 'Kendaraan']))
            ->assertSessionHasErrors('jenis');

        $vehicleBooking = $this->bookingData([
            'kategori' => 'Kendaraan',
            'jenis' => 'Mobil',
        ]);
        $this->actingAs($this->staff)->post('/booking', $vehicleBooking)->assertRedirect();
        $this->actingAs($this->staff)
            ->from('/pesan')
            ->post('/booking', array_merge($vehicleBooking, ['jam_mulai' => '10:30', 'jam_selesai' => '11:30']))
            ->assertRedirect('/pesan')
            ->assertSessionHasErrors([
                'jam_mulai' => 'Mobil (Kendaraan) sudah dibooking pada rentang waktu tersebut. Silakan pilih jadwal lain.',
            ]);

        $this->assertDatabaseCount('booking_requests', 2);
    }

    public function test_hrd_and_direksi_can_view_and_process_booking_requests(): void
    {
        $this->actingAs($this->staff)->post('/booking', $this->bookingData())->assertRedirect();
        $booking = BookingRequest::firstOrFail();

        $this->actingAs($this->direksi)
            ->get(route('hrd.suratBooking'))
            ->assertOk()
            ->assertSee('Meeting')
            ->assertSee('ACC')
            ->assertSee('Tolak');

        $this->actingAs($this->direksi)
            ->post(route('hrd.suratBooking.acc', $booking))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('booking_requests', [
            'id' => $booking->id,
            'status' => 'ACC',
            'diproses_oleh_id_user' => $this->direksi->id_user,
        ]);

        $this->actingAs($this->staff)
            ->from('/pesan')
            ->post('/booking', $this->bookingData(['jam_mulai' => '10:30', 'jam_selesai' => '11:30']))
            ->assertRedirect('/pesan')
            ->assertSessionHasErrors('jam_mulai');

        $this->actingAs($this->hrd)
            ->post(route('hrd.suratBooking.tolak', $booking))
            ->assertStatus(409);

        $this->actingAs($this->staff)->get(route('hrd.suratBooking'))->assertRedirect();
    }

    public function test_rejected_room_request_releases_its_time_slot(): void
    {
        $this->actingAs($this->staff)->post('/booking', $this->bookingData())->assertRedirect();
        $booking = BookingRequest::firstOrFail();

        $this->actingAs($this->hrd)
            ->post(route('hrd.suratBooking.tolak', $booking))
            ->assertRedirect();

        $this->actingAs($this->staff)
            ->post('/booking', $this->bookingData(['jam_mulai' => '10:30', 'jam_selesai' => '11:30']))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('booking_requests', 2);
    }

    public function test_staff_messages_page_contains_booking_popup_form(): void
    {
        $this->withoutVite();

        $this->actingAs($this->staff)
            ->get('/pesan')
            ->assertOk()
            ->assertSee('Booking Kendaraan/Ruangan')
            ->assertSee('id="modalBooking"', false)
            ->assertSee('jam_selesai')
            ->assertSee('value="Ruang Meeting"', false)
            ->assertSee('value="Ruang Writer"', false)
            ->assertSee('value="Mobil"', false)
            ->assertSee('value="Motor"', false);
    }

    public function test_kadiv_messages_page_shows_booking_success_message(): void
    {
        $this->withoutVite();

        $this->actingAs($this->kadiv)
            ->withSession(['success' => 'Permintaan booking berhasil dikirim dan menunggu persetujuan.'])
            ->get('/kadiv/pesan')
            ->assertOk()
            ->assertSee('Permintaan booking berhasil dikirim dan menunggu persetujuan.');
    }

    private function createUser(string $suffix, string $roleId, string $name): User2
    {
        $employeeId = 'EMP-' . $suffix;
        $userId = 'USR-' . $suffix;

        Karyawan::create([
            'id_karyawan' => $employeeId,
            'nama' => $name,
            'jenis_kelamin' => 'Laki-laki',
            'email' => strtolower($suffix) . '@example.test',
            'jabatan' => $name,
            'divisi_id_divisi' => 'DIV-IT',
        ]);

        return User2::create([
            'id_user' => $userId,
            'username' => strtolower($suffix),
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $employeeId,
        ]);
    }

    private function bookingData(array $overrides = []): array
    {
        return array_merge([
            'kategori' => 'Ruangan',
            'jenis' => 'Ruang Meeting',
            'tanggal' => now()->addDay()->toDateString(),
            'jam_mulai' => '10:00',
            'jam_selesai' => '11:00',
            'keperluan' => 'Rapat koordinasi',
        ], $overrides);
    }
}
