<?php

namespace Tests\Feature;

use App\Livewire\ContentCalendar;
use App\Models\ContentEvent;
use App\Models\ContentType;
use App\Models\Divisi;
use App\Models\Holiday;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
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

        ContentType::insert([
            ['slug' => 'reels', 'name' => 'Reels', 'sort_order' => 1],
            ['slug' => 'single-image', 'name' => 'Single Image', 'sort_order' => 2],
            ['slug' => 'carousel', 'name' => 'Carousel', 'sort_order' => 3],
        ]);
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

    private function event(User2 $creator, array $overrides = []): ContentEvent
    {
        return ContentEvent::create($overrides + [
            'title' => 'Event contoh',
            'event_date' => '2026-11-10',
            'post_time' => '09:00',
            'content_type' => 'reels',
            'status' => 'process',
            'description' => null,
            'content_url' => null,
            'created_by' => $creator->id_user,
        ]);
    }

    public function test_page_renders_for_every_role(): void
    {
        foreach ([$this->staffIt, $this->kadivIt, $this->hrd] as $user) {
            $this->actingAs($user)
                ->get('/kalender-konten')
                ->assertOk()
                ->assertSee('Kalender Konten')
                ->assertSee('Tambah event')
                ->assertSee('Semua jenis')
                ->assertSee('Semua status');
        }
    }

    public function test_staff_can_create_an_event(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->call('openCreate', '2026-11-12')
            ->assertSet('formOpen', true)
            ->assertSet('event_date', '2026-11-12')
            ->assertSet('formStatus', 'process')
            ->set('title', 'Kampanye Ramadan')
            ->set('post_time', '17:00')
            ->set('content_type', 'carousel')
            ->set('description', 'Teaser dan carousel.')
            ->set('content_url', 'https://example.test/konten')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('formOpen', false)
            ->assertSet('editingId', null)
            ->assertDispatched('content-toast');

        $this->assertDatabaseHas('content_events', [
            'title' => 'Kampanye Ramadan',
            'event_date' => '2026-11-12',
            'post_time' => '17:00',
            'content_type' => 'carousel',
            'status' => 'process',
            'content_url' => 'https://example.test/konten',
            'created_by' => $this->staffIt->id_user,
        ]);
    }

    public function test_create_requires_title_date_and_content_type(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', '')
            ->set('event_date', '')
            ->set('content_type', '')
            ->call('save')
            ->assertHasErrors(['title', 'event_date', 'content_type']);
    }

    public function test_title_is_limited_to_150_characters(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', str_repeat('a', 151))
            ->set('content_type', 'reels')
            ->call('save')
            ->assertHasErrors(['title']);
    }

    public function test_content_url_must_be_http_or_https(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event')
            ->set('content_type', 'reels')
            ->set('content_url', 'bukan-url')
            ->call('save')
            ->assertHasErrors(['content_url']);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event')
            ->set('content_type', 'reels')
            ->set('content_url', 'ftp://example.test/file')
            ->call('save')
            ->assertHasErrors(['content_url']);
    }

    public function test_post_time_uses_24_hour_format(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event')
            ->set('content_type', 'reels')
            ->set('post_time', '25:00')
            ->call('save')
            ->assertHasErrors(['post_time']);
    }

    public function test_content_type_must_exist_in_the_managed_list(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event')
            ->set('content_type', 'podcast')
            ->call('save')
            ->assertHasErrors(['content_type']);
    }

    public function test_card_shows_date_time_type_and_status(): void
    {
        $this->event($this->staffIt, [
            'title' => 'Reels Produk',
            'event_date' => '2026-11-10',
            'post_time' => '17:00',
            'content_type' => 'carousel',
            'status' => 'publish',
        ]);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertSee('Reels Produk')
            ->assertSee('10 Nov 2026')
            ->assertSee('17:00')
            ->assertSee('Carousel')
            ->assertSee('Publish')
            ->assertSee('background-color: #D9F5E5')
            ->assertSee('background-color: #FCE7F1')
            ->assertDontSee('11/10/2026');
    }

    public function test_card_hides_the_time_line_when_no_time_is_set(): void
    {
        $this->event($this->staffIt, [
            'title' => 'Tanpa Jam',
            'event_date' => '2026-11-10',
            'post_time' => null,
        ]);

        $html = Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->html();

        $this->assertStringContainsString('Tanpa Jam', $html);
        $this->assertStringNotContainsString('10 Nov 2026 · 00:00', $html);
        $this->assertSame(0, substr_count($html, '00:00'));
    }

    public function test_week_view_hides_the_repeated_date_line(): void
    {
        $this->event($this->staffIt, [
            'title' => 'Kartu Mingguan',
            'event_date' => '2026-11-10',
        ]);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('view', 'minggu')
            ->set('week', '2026-11-09')
            ->assertSee('Kartu Mingguan')
            ->assertDontSee('10 Nov 2026');
    }

    public function test_month_view_orders_cards_by_time_and_caps_at_two(): void
    {
        $this->event($this->staffIt, ['title' => 'Siang', 'event_date' => '2026-11-10', 'post_time' => '12:00']);
        $this->event($this->staffIt, ['title' => 'Pagi', 'event_date' => '2026-11-10', 'post_time' => '08:00']);
        $this->event($this->staffIt, ['title' => 'Sore', 'event_date' => '2026-11-10', 'post_time' => '17:00']);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertSeeInOrder(['Pagi', 'Siang'])
            ->assertSee('+1 lainnya');
    }

    public function test_week_view_lists_every_card_sorted_by_time(): void
    {
        $this->event($this->staffIt, ['title' => 'Tanpa jam', 'event_date' => '2026-11-10', 'post_time' => null]);
        $this->event($this->staffIt, ['title' => 'Sore', 'event_date' => '2026-11-10', 'post_time' => '17:00']);
        $this->event($this->staffIt, ['title' => 'Pagi', 'event_date' => '2026-11-10', 'post_time' => '07:00']);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('view', 'minggu')
            ->set('week', '2026-11-09')
            ->assertSeeInOrder(['Pagi', 'Sore', 'Tanpa jam']);
    }

    public function test_period_titles_use_indonesian_and_monday_first_weeks(): void
    {
        $this->event($this->staffIt, ['title' => 'Reels Produk']);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('view', 'bulan')
            ->set('month', '2026-11')
            ->assertSee('November 2026')
            ->assertSee('Sen')
            ->assertSee('Min')
            ->set('view', 'minggu')
            ->set('week', '2026-11-09')
            ->assertSee('9–15 Nov 2026');
    }

    public function test_previous_and_next_move_the_visible_period(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->call('previous')
            ->assertSet('month', '2026-10')
            ->call('next')
            ->assertSet('month', '2026-11')
            ->set('view', 'minggu')
            ->set('week', '2026-11-09')
            ->call('previous')
            ->assertSet('week', '2026-11-02')
            ->call('next')
            ->assertSet('week', '2026-11-09');
    }

    public function test_type_and_status_filters_apply_live(): void
    {
        $this->event($this->staffIt, ['title' => 'Reels On Process', 'content_type' => 'reels', 'status' => 'process']);
        $this->event($this->staffIt, ['title' => 'Carousel Publish', 'content_type' => 'carousel', 'status' => 'publish']);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->set('typeFilter', 'carousel')
            ->assertSee('Carousel Publish')
            ->assertDontSee('Reels On Process')
            ->set('typeFilter', 'semua')
            ->set('statusFilter', 'process')
            ->assertSee('Reels On Process')
            ->assertDontSee('Carousel Publish');
    }

    public function test_reset_only_appears_when_a_filter_is_active(): void
    {
        $this->event($this->staffIt, ['title' => 'Reels Produk']);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertDontSee('Reset')
            ->set('statusFilter', 'process')
            ->assertSee('Reset')
            ->call('resetFilters')
            ->assertSet('statusFilter', 'semua');
    }

    public function test_empty_state_is_shown_when_the_month_has_no_events(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-12')
            ->assertSee('Belum ada event bulan ini');
    }

    public function test_national_holidays_are_not_rendered_on_the_content_calendar(): void
    {
        Holiday::create([
            'date' => '2026-11-25',
            'name' => 'Hari Libur Contoh',
            'type' => Holiday::TYPE_NATIONAL,
        ]);

        $this->event($this->staffIt, ['title' => 'Reels Produk']);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertDontSee('Hari Libur Contoh');
    }

    public function test_status_options_use_the_content_workflow(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->assertSee('Revisi')
            ->assertSee('Cancel')
            ->assertSee('Approved')
            ->assertSee('On process')
            ->assertSee('Publish')
            ->assertDontSee('Menunggu persetujuan');
    }

    public function test_legacy_status_values_are_rejected(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event status lama')
            ->set('content_type', 'reels')
            ->set('formStatus', 'baru')
            ->call('save')
            ->assertHasErrors(['formStatus']);
    }

    public function test_todos_are_saved_with_a_new_event(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event dengan to-do')
            ->set('content_type', 'reels')
            ->call('addTodo')
            ->set('todos.0.text', 'Siapkan brief')
            ->set('todos.0.done', true)
            ->call('addTodo')
            ->set('todos.1.text', 'Desain carousel')
            ->call('save')
            ->assertHasNoErrors();

        $event = ContentEvent::where('title', 'Event dengan to-do')->firstOrFail();

        $this->assertSame([
            ['text' => 'Siapkan brief', 'done' => true],
            ['text' => 'Desain carousel', 'done' => false],
        ], $event->todos);
    }

    public function test_todos_are_toggled_and_removed_from_the_detail_modal(): void
    {
        $event = $this->event($this->staffIt, [
            'todos' => [
                ['text' => 'Siapkan brief', 'done' => false],
                ['text' => 'Desain carousel', 'done' => true],
            ],
        ]);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->assertSet('todos.0.text', 'Siapkan brief')
            ->call('toggleTodo', 0)
            ->set('todos.0.text', 'Brief final')
            ->call('removeTodo', 1)
            ->assertDispatched('content-saved');

        $this->assertSame([
            ['text' => 'Brief final', 'done' => true],
        ], $event->fresh()->todos);
    }

    public function test_todo_text_is_limited_to_200_characters(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event')
            ->set('content_type', 'reels')
            ->call('addTodo')
            ->set('todos.0.text', str_repeat('a', 201))
            ->call('save')
            ->assertHasErrors(['todos.0.text']);
    }

    public function test_read_only_to_do_list_has_no_edit_controls(): void
    {
        $event = $this->event($this->staffIt, [
            'todos' => [['text' => 'Siapkan brief', 'done' => false]],
        ]);

        Livewire::actingAs($this->otherStaffIt)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->assertSee('Siapkan brief')
            ->assertDontSeeHtml('wire:click="addTodo"')
            ->assertDontSeeHtml('wire:click="toggleTodo(0)"');
    }

    public function test_read_only_detail_hides_editing_controls_for_other_staff(): void
    {
        $event = $this->event($this->staffIt, ['title' => 'Event Staff Lain']);

        Livewire::actingAs($this->otherStaffIt)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->assertSet('readOnly', true)
            ->assertSee('Event Staff Lain')
            ->assertDontSee('content-date')
            ->assertDontSee('content-url')
            ->assertDontSeeHtml('wire:click="deleteEvent"');
    }

    public function test_creator_autosaves_a_field_and_shows_the_saved_indicator(): void
    {
        $event = $this->event($this->staffIt, ['title' => 'Judul Lama']);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->assertSet('readOnly', false)
            ->set('title', 'Judul Baru')
            ->call('saveField', 'title')
            ->assertHasNoErrors()
            ->assertDispatched('content-saved');

        $this->assertDatabaseHas('content_events', ['id' => $event->id, 'title' => 'Judul Baru']);
    }

    public function test_autosave_rejects_an_invalid_value(): void
    {
        $event = $this->event($this->staffIt, ['title' => 'Judul Lama']);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->set('title', '')
            ->call('saveField', 'title')
            ->assertHasErrors(['title']);

        $this->assertDatabaseHas('content_events', ['id' => $event->id, 'title' => 'Judul Lama']);
    }

    public function test_kadiv_can_edit_creators_division_but_not_another_division(): void
    {
        $event = $this->event($this->staffIt, ['title' => 'Event IT']);

        $this->assertTrue(Gate::forUser($this->kadivIt)->allows('update', $event));
        $this->assertFalse(Gate::forUser($this->kadivHr)->allows('update', $event));

        Livewire::actingAs($this->kadivIt)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->assertSet('readOnly', false)
            ->set('title', 'Diubah Kadiv IT')
            ->call('saveField', 'title')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('content_events', ['id' => $event->id, 'title' => 'Diubah Kadiv IT']);

        Livewire::actingAs($this->kadivHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->assertSet('readOnly', true)
            ->set('title', 'Diubah Kadiv HR')
            ->call('saveField', 'title');

        Livewire::actingAs($this->kadivHr)
            ->test(ContentCalendar::class)
            ->call('moveEvent', $event->id, '2026-11-20');

        Livewire::actingAs($this->kadivHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->call('deleteEvent');

        $this->assertDatabaseHas('content_events', [
            'id' => $event->id,
            'title' => 'Diubah Kadiv IT',
            'event_date' => '2026-11-10',
        ]);
    }

    public function test_hrd_can_edit_and_delete_any_event(): void
    {
        $event = $this->event($this->staffIt, ['title' => 'Event IT']);

        Livewire::actingAs($this->hrd)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->assertSet('readOnly', false)
            ->set('title', 'Diubah HRD')
            ->call('saveField', 'title')
            ->assertHasNoErrors()
            ->call('deleteEvent')
            ->assertSet('detailOpen', false);

        $this->assertDatabaseMissing('content_events', ['id' => $event->id]);
    }

    public function test_other_staff_cannot_delete(): void
    {
        $event = $this->event($this->staffIt);

        $this->assertFalse(Gate::forUser($this->otherStaffIt)->allows('delete', $event));

        Livewire::actingAs($this->otherStaffIt)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->call('deleteEvent');

        $this->assertDatabaseHas('content_events', ['id' => $event->id]);
    }

    public function test_drag_moves_the_event_date_and_can_be_undone(): void
    {
        $event = $this->event($this->staffIt, ['event_date' => '2026-11-10']);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->call('moveEvent', $event->id, '2026-11-12')
            ->assertDispatched('content-toast')
            ->call('undoMove');

        $this->assertDatabaseHas('content_events', [
            'id' => $event->id,
            'event_date' => '2026-11-10',
        ]);
    }

    public function test_week_cards_are_draggable_for_editors_only(): void
    {
        $event = $this->event($this->staffIt, ['event_date' => '2026-11-10']);

        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->set('view', 'minggu')
            ->set('week', '2026-11-09')
            ->assertSeeHtml('draggable="true"');

        Livewire::actingAs($this->otherStaffIt)
            ->test(ContentCalendar::class)
            ->set('view', 'minggu')
            ->set('week', '2026-11-09')
            ->assertSeeHtml('draggable="false"');
    }

    public function test_drag_is_forbidden_for_other_staff(): void
    {
        $event = $this->event($this->staffIt, ['event_date' => '2026-11-10']);

        Livewire::actingAs($this->otherStaffIt)
            ->test(ContentCalendar::class)
            ->call('moveEvent', $event->id, '2026-11-12');

        $this->assertDatabaseHas('content_events', [
            'id' => $event->id,
            'event_date' => '2026-11-10',
        ]);
    }

    public function test_closing_a_dirty_create_modal_is_guarded_on_the_server(): void
    {
        Livewire::actingAs($this->staffIt)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Belum disimpan')
            ->assertSet('formDirty', true)
            ->call('closeModal')
            ->assertSet('formOpen', true)
            ->call('closeModal', true)
            ->assertSet('formOpen', false);
    }
}
