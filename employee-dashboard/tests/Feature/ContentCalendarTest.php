<?php

namespace Tests\Feature;

use App\Livewire\ContentCalendar;
use App\Models\ContentEvent;
use App\Models\Divisi;
use App\Models\Holiday;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ContentCalendarTest extends TestCase
{
    use RefreshDatabase;

    private Divisi $it;

    private Divisi $hr;

    private User2 $staffIt;

    private User2 $otherStaffIt;

    private User2 $kadivIt;

    private User2 $kadivHr;

    private User2 $hrd;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'RL-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'RL-KADIV', 'nama_role' => 'kadiv'],
            ['id_role' => 'RL-HRD', 'nama_role' => 'hrd'],
        ]);

        $this->it = Divisi::create([
            'id_divisi' => 'DV-IT',
            'kode_divisi' => 'IT',
            'nama_divisi' => 'Information Technology',
            'status_aktif' => 'Aktif',
        ]);

        $this->hr = Divisi::create([
            'id_divisi' => 'DV-HR',
            'kode_divisi' => 'HR',
            'nama_divisi' => 'Human Resources',
            'status_aktif' => 'Aktif',
        ]);

        $this->staffIt = $this->makeUser('U-STAFF-IT', 'staffit', 'Staff IT', 'Staff', $this->it, 'RL-STAFF');
        $this->otherStaffIt = $this->makeUser('U-STAFF-IT-2', 'staffit2', 'Staff IT Lain', 'Staff', $this->it, 'RL-STAFF');
        $this->kadivIt = $this->makeUser('U-KADIV-IT', 'kadivit', 'Kadiv IT', 'Kepala Divisi IT', $this->it, 'RL-KADIV');
        $this->kadivHr = $this->makeUser('U-KADIV-HR', 'kadivhr', 'Kadiv HR', 'Kepala Divisi HR', $this->hr, 'RL-KADIV');
        $this->hrd = $this->makeUser('U-HRD', 'hrduser', 'HRD Manager', 'HRD Manager', $this->hr, 'RL-HRD');
    }

    private function makeUser(
        string $id,
        string $username,
        string $name,
        string $jabatan,
        Divisi $division,
        string $roleId
    ): User2 {
        $employee = Karyawan::create([
            'id_karyawan' => 'K-'.$id,
            'nama' => $name,
            'jenis_kelamin' => 'Laki-laki',
            'email' => $username.'@example.test',
            'jabatan' => $jabatan,
            'divisi_id_divisi' => $division->id_divisi,
        ]);

        return User2::create([
            'id_user' => $id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => $employee->id_karyawan,
        ]);
    }

    public function test_page_renders_for_every_role(): void
    {
        foreach ([$this->staffIt, $this->kadivIt, $this->hrd] as $user) {
            $this->actingAs($user)
                ->get('/kalender-konten')
                ->assertOk()
                ->assertSee('Kalender Konten')
                ->assertSee('Tambah event');
        }
    }

    public function test_staff_can_create_an_event_and_it_defaults_to_their_division(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->call('openCreate', '2026-11-12')
            ->assertSet('formOpen', true)
            ->assertSet('drawerOpen', false)
            ->assertSet('division_id', 'DV-IT')
            ->set('title', 'Kampanye Ramadan')
            ->set('end_date', '2026-11-14')
            ->set('description', 'Teaser, carousel, dan reels.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('formOpen', false)
            ->assertSet('editingId', null);

        $event = ContentEvent::firstOrFail();

        $this->assertSame('Kampanye Ramadan', $event->title);
        $this->assertSame('2026-11-12', $event->start_date->toDateString());
        $this->assertSame('2026-11-14', $event->end_date->toDateString());
        $this->assertSame('DV-IT', $event->division_id);
        $this->assertSame('baru', $event->status);
        $this->assertSame('U-STAFF-IT', $event->created_by);
    }

    public function test_event_times_are_saved_and_shown_on_pills(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Webinar Produk')
            ->set('start_time', '09:00')
            ->set('end_time', '11:00')
            ->call('save')
            ->assertHasNoErrors();

        $event = ContentEvent::firstOrFail();

        $this->assertSame('09:00', $event->startTimeLabel());
        $this->assertSame('11:00', $event->endTimeLabel());
        $this->assertSame('09:00–11:00', $event->timeLabel());

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertSee('09:00');

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->set('view', 'daftar')
            ->assertSee('09:00–11:00');

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->assertSet('start_time', '09:00')
            ->assertSet('end_time', '11:00');
    }

    public function test_end_time_cannot_be_before_start_time_on_the_same_day(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event')
            ->set('start_time', '11:00')
            ->set('end_time', '09:00')
            ->call('save')
            ->assertHasErrors(['end_time']);

        $this->assertDatabaseCount('content_events', 0);
    }

    public function test_end_time_requires_a_start_time(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event')
            ->set('end_time', '09:00')
            ->call('save')
            ->assertHasErrors(['end_time']);

        $this->assertDatabaseCount('content_events', 0);
    }

    public function test_time_fields_use_a_scrollable_dropdown(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->assertSet('formOpen', true)
            ->assertSeeHtml('data-content-form')
            ->assertSeeHtml('id="content-start-time"')
            ->assertSeeHtml('id="content-end-time"')
            ->assertSeeHtml('role="listbox"')
            ->assertDontSeeHtml('type="time"');
    }

    public function test_event_color_can_be_chosen_and_is_rendered(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event Merah')
            ->set('color', 'red')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('formOpen', false);

        $event = ContentEvent::firstOrFail();

        $this->assertSame('red', $event->color);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertSee('Event Merah')
            ->assertSeeHtml('#FEE2E2');
    }

    public function test_event_color_must_come_from_the_palette(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event')
            ->set('color', 'pink')
            ->call('save')
            ->assertHasErrors(['color']);

        $this->assertDatabaseCount('content_events', 0);
    }

    public function test_title_and_start_date_are_required(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', '   ')
            ->set('start_date', '')
            ->call('save')
            ->assertHasErrors(['title', 'start_date']);

        $this->assertDatabaseCount('content_events', 0);
    }

    public function test_end_date_cannot_be_before_start_date(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event')
            ->set('end_date', '2026-11-10')
            ->call('save')
            ->assertHasErrors(['end_date']);
    }

    public function test_creator_can_edit_and_delete_their_event(): void
    {
        $event = $this->event($this->staffIt, 'DV-IT');

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->assertSet('readOnly', false)
            ->assertSet('drawerOpen', true)
            ->assertSet('formOpen', false)
            ->call('editEvent')
            ->assertSet('drawerOpen', false)
            ->assertSet('formOpen', true)
            ->set('title', 'Judul baru')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('formOpen', false);

        $this->assertSame('Judul baru', $event->fresh()->title);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->call('editEvent')
            ->call('deleteEvent');

        $this->assertDatabaseMissing('content_events', ['id' => $event->id]);
    }

    public function test_other_staff_sees_read_only_drawer_and_cannot_update(): void
    {
        $event = $this->event($this->staffIt, 'DV-IT');

        Livewire::actingAs($this->otherStaffIt)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->assertSet('readOnly', true)
            ->assertDontSee('Simpan')
            ->assertDontSee('Hapus');

        $this->assertFalse($this->otherStaffIt->can('update', $event));
        $this->assertFalse($this->otherStaffIt->can('delete', $event));
    }

    public function test_kadiv_can_manage_events_in_their_own_division_only(): void
    {
        $itEvent = $this->event($this->staffIt, 'DV-IT');
        $hrEvent = $this->event($this->staffIt, 'DV-HR');

        $this->assertTrue($this->kadivIt->can('update', $itEvent));
        $this->assertTrue($this->kadivIt->can('delete', $itEvent));
        $this->assertFalse($this->kadivIt->can('update', $hrEvent));

        $this->assertTrue($this->kadivHr->can('update', $hrEvent));
        $this->assertFalse($this->kadivHr->can('update', $itEvent));
    }

    public function test_hrd_can_manage_every_event(): void
    {
        $itEvent = $this->event($this->staffIt, 'DV-IT');
        $umumEvent = $this->event($this->staffIt, null);

        $this->assertTrue($this->hrd->can('update', $itEvent));
        $this->assertTrue($this->hrd->can('delete', $itEvent));
        $this->assertTrue($this->hrd->can('update', $umumEvent));
    }

    public function test_moving_an_event_shifts_start_and_end_and_can_be_undone(): void
    {
        $event = $this->event($this->staffIt, 'DV-IT', '2026-11-12', '2026-11-14');

        $component = Livewire::actingAs($this->kadivIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->call('moveEvent', $event->id, '2026-11-19');

        $event->refresh();
        $this->assertSame('2026-11-19', $event->start_date->toDateString());
        $this->assertSame('2026-11-21', $event->end_date->toDateString());

        $component->call('undoMove');

        $event->refresh();
        $this->assertSame('2026-11-12', $event->start_date->toDateString());
        $this->assertSame('2026-11-14', $event->end_date->toDateString());
    }

    public function test_week_view_renders_its_week_and_navigates_week_by_week(): void
    {
        $this->event($this->staffIt, 'DV-IT', '2026-11-12', null, 'Event Minggu Kedua');

        $component = Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->call('setView', 'minggu')
            ->set('week', '2026-11-09')
            ->assertSee('Event Minggu Kedua')
            ->assertDontSee('Belum ada event bulan ini');

        $component->call('next')
            ->assertSet('week', '2026-11-16')
            ->assertDontSee('Event Minggu Kedua');

        $component->call('previous')
            ->assertSet('week', '2026-11-09')
            ->assertSee('Event Minggu Kedua');
    }

    public function test_month_and_week_views_keep_the_grid_visible_when_empty(): void
    {
        foreach (['bulan' => 'Sen', 'minggu' => 'Tidak ada event'] as $view => $expected) {
            Livewire::actingAs($this->staffIt)
                ->test(ContentCalendar::class)
                ->set('month', '2026-11')
                ->set('view', $view)
                ->assertDontSee('Belum ada event bulan ini')
                ->assertSee($expected);
        }
    }

    public function test_calendar_fetches_holidays_for_the_visible_year(): void
    {
        config([
            'services.holiday.auto_sync' => true,
            'services.holiday.include_joint_leave' => false,
        ]);
        Cache::flush();

        Http::fake([
            '*data-libur*' => Http::response([
                'libur_nasional' => [
                    ['date' => '2026-08-17', 'name' => 'Hari Proklamasi Kemerdekaan RI'],
                ],
            ]),
        ]);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-08')
            ->assertSee('Hari Proklamasi Kemerdekaan RI');

        $this->assertDatabaseHas('holidays', [
            'name' => 'Hari Proklamasi Kemerdekaan RI',
            'source' => 'api',
        ]);
    }

    public function test_holidays_are_rendered_as_read_only_entries(): void
    {
        Holiday::create(['date' => '2026-11-25', 'name' => 'Hari Libur Contoh']);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertSee('Hari Libur Contoh');

        $this->assertDatabaseCount('content_events', 0);
    }

    public function test_only_day_off_row_types_are_rendered(): void
    {
        Holiday::create([
            'date' => '2026-11-25',
            'name' => 'Hari Libur Contoh',
            'type' => Holiday::TYPE_NATIONAL,
        ]);
        Holiday::create([
            'date' => '2026-11-25',
            'name' => 'Hari Besar Lama',
            'type' => 'hari_besar_nasional',
        ]);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertSee('Hari Libur Contoh')
            ->assertDontSee('Hari Besar Lama');
    }

    public function test_division_filter_applies_to_the_calendar_data(): void
    {
        $this->event($this->staffIt, 'DV-IT', null, null, 'Event IT', 'baru');
        $this->event($this->hrd, 'DV-HR', null, null, 'Event HR', 'disetujui');

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->set('division', 'DV-HR')
            ->assertSee('Event HR')
            ->assertDontSee('Event IT')
            ->set('division', 'semua')
            ->assertSee('Event IT')
            ->assertSee('Event HR');
    }

    public function test_validation_rejects_an_unknown_division_or_pic(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event')
            ->set('division_id', 'DV-NOPE')
            ->set('pic_user_id', 'U-NOPE')
            ->call('save')
            ->assertHasErrors(['division_id', 'pic_user_id']);
    }

    public function test_read_only_peek_has_no_edit_action(): void
    {
        $event = $this->event($this->staffIt, 'DV-IT');

        Livewire::actingAs($this->otherStaffIt)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->assertSet('drawerOpen', true)
            ->assertSet('formOpen', false)
            ->assertSee('Tutup')
            ->assertDontSee('Edit')
            ->assertDontSee('Simpan');
    }

    public function test_edit_dialog_tracks_changes_and_resets_on_close(): void
    {
        $event = $this->event($this->staffIt, 'DV-IT');

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->call('editEvent')
            ->assertSet('formDirty', false)
            ->set('title', 'Judul berubah')
            ->assertSet('formDirty', true)
            ->call('closeForm')
            ->assertSet('formOpen', false)
            ->assertSet('editingId', null)
            ->assertSet('formDirty', false);
    }

    public function test_events_are_ordered_by_start_time_not_creation_order(): void
    {
        $this->timedEvent('Event Sore', '15:00');
        $this->timedEvent('Event Pagi', '09:00');

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertSeeInOrder(['Event Pagi', 'Event Sore']);
    }

    public function test_all_day_events_sort_above_timed_events(): void
    {
        $this->timedEvent('Event Jam', '09:00');
        $this->event($this->staffIt, 'DV-IT', null, null, 'Event Seharian');

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertSeeInOrder(['Event Seharian', 'Event Jam']);
    }

    public function test_holidays_and_multi_day_events_stay_above_single_day_items(): void
    {
        Holiday::create(['date' => '2026-11-13', 'name' => 'Libur Uji']);
        $this->event($this->staffIt, 'DV-IT', '2026-11-13', '2026-11-14', 'Event Band');
        $this->timedEvent('Event Jam', '08:00', '2026-11-14');

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertSeeInOrder(['Libur Uji', 'Event Band', 'Event Jam']);
    }

    public function test_month_cell_shows_three_pills_before_overflowing(): void
    {
        $this->timedEvent('Event Satu', '09:00');
        $this->timedEvent('Event Dua', '10:00');
        $this->timedEvent('Event Tiga', '11:00');

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertSeeInOrder(['Event Satu', 'Event Dua', 'Event Tiga'])
            ->assertDontSee('lainnya');

        $this->timedEvent('Event Empat', '12:00');

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertSee('+1 lainnya')
            ->assertDontSee('Event Empat');
    }

    public function test_multi_day_events_are_ordered_by_time_on_their_start_day(): void
    {
        $long = $this->timedEvent('Event Panjang', '06:10', '2026-11-17');
        $long->update(['end_date' => '2026-11-18']);
        $this->timedEvent('Event Pendek', '02:30', '2026-11-17');

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertSeeInOrder(['Event Pendek', 'Event Panjang']);
    }

    private function timedEvent(string $title, string $startTime, string $date = '2026-11-12'): ContentEvent
    {
        return ContentEvent::create([
            'title' => $title,
            'start_date' => $date,
            'start_time' => $startTime,
            'division_id' => 'DV-IT',
            'status' => 'baru',
            'created_by' => $this->staffIt->id_user,
        ]);
    }

    private function event(
        User2 $creator,
        ?string $divisionId,
        ?string $start = null,
        ?string $end = null,
        string $title = 'Event Konten',
        string $status = 'baru'
    ): ContentEvent {
        return ContentEvent::create([
            'title' => $title,
            'start_date' => $start ?? '2026-11-12',
            'end_date' => $end,
            'division_id' => $divisionId,
            'status' => $status,
            'description' => 'Deskripsi event.',
            'created_by' => $creator->id_user,
        ]);
    }
}
