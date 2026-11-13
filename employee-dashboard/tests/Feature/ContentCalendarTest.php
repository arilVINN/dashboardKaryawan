<?php

namespace Tests\Feature;

use App\Livewire\ContentCalendar;
use App\Models\ContentAccount;
use App\Models\ContentEvent;
use App\Models\ContentEventComment;
use App\Models\ContentType;
use App\Models\Divisi;
use App\Models\Holiday;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use App\Support\ContentCalendarAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ContentCalendarTest extends TestCase
{
    use RefreshDatabase;

    private Divisi $it;

    private Divisi $hr;

    private Divisi $markom;

    private User2 $staffHr;

    private User2 $otherStaffHr;

    private User2 $staffMarkom;

    private User2 $staffIt;

    private User2 $kadivHr;

    private User2 $hrd;

    private ContentAccount $defaultAccount;

    private ContentAccount $secondAccount;

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

        $this->markom = Divisi::create([
            'id_divisi' => 'DV-MK',
            'kode_divisi' => 'MK',
            'nama_divisi' => 'Marketing Communication',
            'status_aktif' => 'Aktif',
        ]);

        $this->staffHr = $this->makeUser('U-STAFF-HR', 'staffhr', 'Staff HR', 'Staff', $this->hr, 'RL-STAFF');
        $this->otherStaffHr = $this->makeUser('U-STAFF-HR-2', 'staffhr2', 'Staff HR Lain', 'Staff', $this->hr, 'RL-STAFF');
        $this->staffMarkom = $this->makeUser('U-STAFF-MK', 'staffmk', 'Staff MarKom', 'Staff', $this->markom, 'RL-STAFF');
        $this->staffIt = $this->makeUser('U-STAFF-IT', 'staffit', 'Staff IT', 'Staff', $this->it, 'RL-STAFF');
        $this->kadivHr = $this->makeUser('U-KADIV-HR', 'kadivhr', 'Kadiv HR', 'Kepala Divisi HR', $this->hr, 'RL-KADIV');
        $this->hrd = $this->makeUser('U-HRD', 'hrduser', 'HRD Manager', 'HRD Manager', $this->hr, 'RL-HRD');

        ContentCalendarAccess::flush();

        ContentType::insert([
            ['slug' => 'reels', 'name' => 'Reels', 'sort_order' => 1],
            ['slug' => 'single-image', 'name' => 'Single Image', 'sort_order' => 2],
            ['slug' => 'carousel', 'name' => 'Carousel', 'sort_order' => 3],
        ]);

        $this->defaultAccount = ContentAccount::create([
            'slug' => 'silindo-official',
            'name' => 'Silindo Official',
            'handle' => '@silindo.official',
            'platform' => 'Instagram',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->secondAccount = ContentAccount::create([
            'slug' => 'silindo-tiktok',
            'name' => 'Silindo TikTok',
            'handle' => '@silindo.id',
            'platform' => 'TikTok',
            'sort_order' => 2,
            'is_active' => true,
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
            'content_account_id' => $this->defaultAccount->id,
            'status' => 'process',
            'description' => null,
            'content_url' => null,
            'created_by' => $creator->id_user,
        ]);
    }

    public function test_page_renders_for_every_role(): void
    {
        foreach ([$this->staffHr, $this->staffMarkom, $this->kadivHr, $this->hrd] as $user) {
            $this->actingAs($user)
                ->get('/kalender-konten')
                ->assertOk()
                ->assertSee('Kalender Konten - Silindo Official')
                ->assertSee('Silindo TikTok')
                ->assertSee('Tambah event')
                ->assertSee('Semua jenis')
                ->assertSee('Semua status');
        }
    }

    public function test_account_dropdown_defaults_and_lists_active_accounts(): void
    {
        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->assertSet('account', 'silindo-official')
            ->assertSee('Silindo Official')
            ->assertSee('Silindo TikTok');

        $inactive = ContentAccount::create([
            'slug' => 'silindo-arsip',
            'name' => 'Silindo Arsip',
            'sort_order' => 99,
            'is_active' => false,
        ]);

        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->assertDontSee($inactive->name);
    }

    public function test_calendar_only_shows_the_selected_accounts_events(): void
    {
        $this->event($this->staffHr, ['title' => 'Event Official']);
        $this->event($this->staffHr, [
            'title' => 'Event TikTok',
            'content_account_id' => $this->secondAccount->id,
        ]);

        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertSet('account', 'silindo-official')
            ->assertSee('Event Official')
            ->assertDontSee('Event TikTok')
            ->set('account', 'silindo-tiktok')
            ->assertSee('Event TikTok')
            ->assertDontSee('Event Official');

        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->set('view', 'minggu')
            ->set('week', '2026-11-09')
            ->set('account', 'silindo-tiktok')
            ->assertSee('Event TikTok')
            ->assertDontSee('Event Official');

        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->set('view', 'daftar')
            ->set('account', 'silindo-tiktok')
            ->assertSee('Event TikTok')
            ->assertDontSee('Event Official');
    }

    public function test_selecting_an_account_dispatches_the_page_title_event(): void
    {
        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->set('account', 'silindo-tiktok')
            ->assertSet('account', 'silindo-tiktok')
            ->assertDispatched('content-account-changed', name: 'Silindo TikTok');
    }

    public function test_an_invalid_account_falls_back_to_the_first_active_account(): void
    {
        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->set('account', 'akun-tidak-ada')
            ->assertSet('account', 'silindo-official');

        $this->actingAs($this->staffHr)
            ->get('/kalender-konten?account=akun-tidak-ada')
            ->assertOk()
            ->assertSee('Kalender Konten - Silindo Official');
    }

    public function test_create_assigns_the_active_account(): void
    {
        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->set('account', 'silindo-tiktok')
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Konten TikTok')
            ->set('content_type', 'reels')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('content_events', [
            'title' => 'Konten TikTok',
            'content_account_id' => $this->secondAccount->id,
        ]);
    }

    public function test_reset_filters_keeps_the_active_account(): void
    {
        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->set('account', 'silindo-tiktok')
            ->set('typeFilter', 'reels')
            ->set('statusFilter', 'process')
            ->call('resetFilters')
            ->assertSet('account', 'silindo-tiktok')
            ->assertSet('typeFilter', 'semua')
            ->assertSet('statusFilter', 'semua');
    }

    public function test_page_title_follows_the_account_query_parameter(): void
    {
        $this->actingAs($this->staffHr)
            ->get('/kalender-konten')
            ->assertOk()
            ->assertSee('<title>Kalender Konten - Silindo Official - PT Silindo</title>', false);

        $this->actingAs($this->staffHr)
            ->get('/kalender-konten?account=silindo-tiktok')
            ->assertOk()
            ->assertSee('<title>Kalender Konten - Silindo TikTok - PT Silindo</title>', false)
            ->assertSee('Kalender Konten - Silindo TikTok');
    }

    public function test_staff_can_create_an_event(): void
    {
        Livewire::actingAs($this->staffHr)
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
            'content_account_id' => $this->defaultAccount->id,
            'status' => 'process',
            'content_url' => 'https://example.test/konten',
            'created_by' => $this->staffHr->id_user,
        ]);
    }

    public function test_create_requires_title_date_and_content_type(): void
    {
        Livewire::actingAs($this->staffHr)
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
        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', str_repeat('a', 151))
            ->set('content_type', 'reels')
            ->call('save')
            ->assertHasErrors(['title']);
    }

    public function test_content_url_must_be_http_or_https(): void
    {
        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event')
            ->set('content_type', 'reels')
            ->set('content_url', 'bukan-url')
            ->call('save')
            ->assertHasErrors(['content_url']);

        Livewire::actingAs($this->staffHr)
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
        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event')
            ->set('content_type', 'reels')
            ->set('post_time', '25:00')
            ->call('save')
            ->assertHasErrors(['post_time']);
    }

    public function test_post_time_field_is_a_manual_text_input(): void
    {
        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->assertSeeHtml('id="content-time" type="text"')
            ->assertSeeHtml('wire:model.blur.live="post_time"');
    }

    public function test_manual_post_time_is_normalized_on_save(): void
    {
        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event jam manual')
            ->set('content_type', 'reels')
            ->set('post_time', '9:30')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('content_events', [
            'title' => 'Event jam manual',
            'post_time' => '09:30',
        ]);
    }

    public function test_manual_post_time_accepts_compact_and_hour_only_input(): void
    {
        foreach (['1730' => '17:30', '930' => '09:30', '9' => '09:00'] as $input => $expected) {
            Livewire::actingAs($this->staffHr)
                ->test(ContentCalendar::class)
                ->call('openCreate', '2026-11-12')
                ->set('title', 'Event '.$input)
                ->set('content_type', 'reels')
                ->set('post_time', $input)
                ->call('save')
                ->assertHasNoErrors();

            $this->assertDatabaseHas('content_events', [
                'title' => 'Event '.$input,
                'post_time' => $expected,
            ]);
        }
    }

    public function test_detail_post_time_autosave_normalizes_manual_input(): void
    {
        $event = $this->event($this->staffHr, ['post_time' => '09:00']);

        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->set('post_time', '1730')
            ->assertSet('post_time', '17:30');

        $this->assertDatabaseHas('content_events', [
            'id' => $event->id,
            'post_time' => '17:30',
        ]);
    }

    public function test_content_type_must_exist_in_the_managed_list(): void
    {
        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event')
            ->set('content_type', 'podcast')
            ->call('save')
            ->assertHasErrors(['content_type']);
    }

    public function test_card_shows_date_time_type_and_status(): void
    {
        $this->event($this->staffHr, [
            'title' => 'Reels Produk',
            'event_date' => '2026-11-10',
            'post_time' => '17:00',
            'content_type' => 'carousel',
            'status' => 'publish',
        ]);

        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertSee('Reels Produk')
            ->assertSee('10 Nov 2026')
            ->assertSee('17:00')
            ->assertSee('Carousel')
            ->assertSee('Publish')
            ->assertSee('background-color: #D9F5E5')
            ->assertSee('background-color: #F3A8C9; border-color: #F3A8C9; color: #172B36;')
            ->assertDontSee('11/10/2026');
    }

    public function test_event_card_type_tags_match_the_form_border_colour(): void
    {
        $this->event($this->staffHr, [
            'title' => 'Reels Produk',
            'content_type' => 'carousel',
            'status' => 'publish',
        ]);

        $html = Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->html();

        $this->assertStringContainsString(
            'background-color: #F3A8C9; border-color: #F3A8C9; color: #172B36;',
            $html
        );

        $formHtml = Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('content_type', 'carousel')
            ->html();

        $this->assertStringContainsString(
            'background-color: #FCE7F1; border-color: #F3A8C9; color: #172B36;',
            $formHtml
        );
    }

    public function test_event_card_background_follows_status_except_revisi(): void
    {
        $this->event($this->staffHr, ['title' => 'Event Publish', 'event_date' => '2026-11-10', 'status' => 'publish']);
        $this->event($this->staffHr, ['title' => 'Event Process', 'event_date' => '2026-11-11', 'status' => 'process']);
        $this->event($this->staffHr, ['title' => 'Event Revisi', 'event_date' => '2026-11-12', 'status' => 'revisi']);

        $html = Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->html();

        $this->assertStringContainsString('data-status="publish"', $html);
        $this->assertStringContainsString('background-color: #D9F5E5; border-color: #86D9AE;', $html);

        $this->assertStringContainsString('data-status="process"', $html);
        $this->assertStringContainsString('background-color: #FEF0C3; border-color: #F5C85C;', $html);

        $this->assertStringContainsString('data-status="revisi"', $html);
        $this->assertStringContainsString('background-color: #FFFFFF; border-color: #94A3B8;', $html);
    }

    public function test_event_card_status_pills_match_the_form_border_colour(): void
    {
        $this->event($this->staffHr, ['title' => 'Event Publish', 'status' => 'publish']);

        $html = Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->html();

        $this->assertStringContainsString(
            'background-color: #86D9AE; border-color: #86D9AE; color: #172B36;',
            $html
        );

        $formHtml = Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('formStatus', 'publish')
            ->html();

        $this->assertStringContainsString(
            'background-color: #D9F5E5; border-color: #86D9AE; color: #172B36;',
            $formHtml
        );
    }

    public function test_card_hides_the_time_line_when_no_time_is_set(): void
    {
        $this->event($this->staffHr, [
            'title' => 'Tanpa Jam',
            'event_date' => '2026-11-10',
            'post_time' => null,
        ]);

        $html = Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->html();

        $this->assertStringContainsString('Tanpa Jam', $html);
        $this->assertStringNotContainsString('10 Nov 2026 · 00:00', $html);
        $this->assertSame(0, substr_count($html, '00:00'));
    }

    public function test_week_view_shows_the_date_line(): void
    {
        $this->event($this->staffHr, [
            'title' => 'Kartu Mingguan',
            'event_date' => '2026-11-10',
        ]);

        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->set('view', 'minggu')
            ->set('week', '2026-11-09')
            ->assertSee('Kartu Mingguan')
            ->assertSee('10 Nov 2026');
    }

    public function test_month_view_orders_cards_by_time_and_caps_at_two(): void
    {
        $this->event($this->staffHr, ['title' => 'Siang', 'event_date' => '2026-11-10', 'post_time' => '12:00']);
        $this->event($this->staffHr, ['title' => 'Pagi', 'event_date' => '2026-11-10', 'post_time' => '08:00']);
        $this->event($this->staffHr, ['title' => 'Sore', 'event_date' => '2026-11-10', 'post_time' => '17:00']);

        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertSeeInOrder(['Pagi', 'Siang'])
            ->assertSee('+1 lainnya');
    }

    public function test_week_view_lists_every_card_sorted_by_time(): void
    {
        $this->event($this->staffHr, ['title' => 'Tanpa jam', 'event_date' => '2026-11-10', 'post_time' => null]);
        $this->event($this->staffHr, ['title' => 'Sore', 'event_date' => '2026-11-10', 'post_time' => '17:00']);
        $this->event($this->staffHr, ['title' => 'Pagi', 'event_date' => '2026-11-10', 'post_time' => '07:00']);

        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->set('view', 'minggu')
            ->set('week', '2026-11-09')
            ->assertSeeInOrder(['Pagi', 'Sore', 'Tanpa jam']);
    }

    public function test_period_titles_use_indonesian_and_monday_first_weeks(): void
    {
        $this->event($this->staffHr, ['title' => 'Reels Produk']);

        Livewire::actingAs($this->staffHr)
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
        Livewire::actingAs($this->staffHr)
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
        $this->event($this->staffHr, ['title' => 'Reels On Process', 'content_type' => 'reels', 'status' => 'process']);
        $this->event($this->staffHr, ['title' => 'Carousel Publish', 'content_type' => 'carousel', 'status' => 'publish']);

        Livewire::actingAs($this->staffHr)
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
        $this->event($this->staffHr, ['title' => 'Reels Produk']);

        Livewire::actingAs($this->staffHr)
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
        Livewire::actingAs($this->staffHr)
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

        $this->event($this->staffHr, ['title' => 'Reels Produk']);

        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->set('month', '2026-11')
            ->assertDontSee('Hari Libur Contoh');
    }

    public function test_status_options_use_the_content_workflow(): void
    {
        Livewire::actingAs($this->staffHr)
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
        Livewire::actingAs($this->staffHr)
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
        Livewire::actingAs($this->staffHr)
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
        $event = $this->event($this->staffHr, [
            'todos' => [
                ['text' => 'Siapkan brief', 'done' => false],
                ['text' => 'Desain carousel', 'done' => true],
            ],
        ]);

        Livewire::actingAs($this->staffHr)
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
        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->call('openCreate', '2026-11-12')
            ->set('title', 'Event')
            ->set('content_type', 'reels')
            ->call('addTodo')
            ->set('todos.0.text', str_repeat('a', 201))
            ->call('save')
            ->assertHasErrors(['todos.0.text']);
    }

    public function test_other_staff_can_edit_todo_items(): void
    {
        $event = $this->event($this->staffHr, [
            'todos' => [['text' => 'Siapkan brief', 'done' => false]],
        ]);

        Livewire::actingAs($this->otherStaffHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->assertSet('readOnly', false)
            ->assertSee('Siapkan brief')
            ->assertSeeHtml('wire:click="toggleTodo(0)"')
            ->assertSeeHtml('wire:click="addTodo"')
            ->assertSeeHtml('wire:click="removeTodo(0)"')
            ->assertSeeHtml('wire:model.blur.live="todos.0.text"');
    }

    public function test_non_creators_can_toggle_and_edit_todos(): void
    {
        $event = $this->event($this->staffHr, [
            'todos' => [
                ['text' => 'Siapkan brief', 'done' => false],
                ['text' => 'Desain carousel', 'done' => false],
            ],
        ]);

        Livewire::actingAs($this->otherStaffHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->call('toggleTodo', 0)
            ->assertDispatched('content-saved');

        $this->assertTrue($event->fresh()->todos[0]['done']);

        Livewire::actingAs($this->otherStaffHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->set('todos.0.text', 'Diubah orang lain');

        $this->assertSame('Diubah orang lain', $event->fresh()->todos[0]['text']);

        Livewire::actingAs($this->otherStaffHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->call('addTodo')
            ->set('todos.2.text', 'Tambahan orang lain');

        $this->assertCount(3, $event->fresh()->todos);

        Livewire::actingAs($this->otherStaffHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->call('removeTodo', 1);

        $this->assertCount(2, $event->fresh()->todos);
    }

    public function test_only_hr_and_marketing_communication_can_open_the_calendar(): void
    {
        foreach ([$this->staffHr, $this->staffMarkom, $this->hrd] as $user) {
            $this->actingAs($user)->get('/kalender-konten')->assertOk();
        }

        $this->actingAs($this->staffIt)->get('/kalender-konten')->assertForbidden();

        $this->assertTrue(ContentCalendarAccess::allows($this->staffHr));
        $this->assertTrue(ContentCalendarAccess::allows($this->staffMarkom));
        $this->assertFalse(ContentCalendarAccess::allows($this->staffIt));
    }

    public function test_livewire_guard_rejects_other_divisions(): void
    {
        $this->actingAs($this->staffIt);

        $this->expectException(HttpException::class);

        (new ContentCalendar)->booted();
    }

    public function test_profile_dropdown_links_to_the_users_own_profile_on_the_shared_page(): void
    {
        $this->actingAs($this->kadivHr)
            ->get('/kalender-konten')
            ->assertOk()
            ->assertSee('/kadiv/profile');

        $this->actingAs($this->hrd)
            ->get('/kalender-konten')
            ->assertOk()
            ->assertSee('/hrd/profile');
    }

    public function test_menu_link_is_hidden_outside_allowed_divisions(): void
    {
        $this->actingAs($this->staffHr)->get('/')->assertOk()->assertSee('Kalender Konten');
        $this->actingAs($this->staffIt)->get('/')->assertOk()->assertDontSee('Kalender Konten');
    }

    public function test_allowed_employees_can_comment_on_an_event(): void
    {
        $event = $this->event($this->staffHr, ['title' => 'Event HR']);

        foreach ([$this->staffHr, $this->otherStaffHr, $this->hrd, $this->staffMarkom] as $index => $user) {
            Livewire::actingAs($user)
                ->test(ContentCalendar::class)
                ->call('openEvent', $event->id)
                ->set('commentBody', 'Komentar '.$index)
                ->call('addComment')
                ->assertHasNoErrors();
        }

        $this->assertSame(4, ContentEventComment::where('content_event_id', $event->id)->count());

        Livewire::actingAs($this->otherStaffHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->assertSee('Komentar 0')
            ->assertSee('Komentar 3');
    }

    public function test_employees_can_only_delete_their_own_comments(): void
    {
        $event = $this->event($this->staffHr, ['title' => 'Event HR']);

        $creatorComment = ContentEventComment::create([
            'content_event_id' => $event->id,
            'user_id_user' => $this->staffHr->id_user,
            'body' => 'Komentar kreator',
        ]);

        $otherComment = ContentEventComment::create([
            'content_event_id' => $event->id,
            'user_id_user' => $this->otherStaffHr->id_user,
            'body' => 'Komentar lain',
        ]);

        Livewire::actingAs($this->otherStaffHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->call('deleteComment', $otherComment->id);

        $this->assertDatabaseMissing('content_event_comments', ['id' => $otherComment->id]);

        Livewire::actingAs($this->otherStaffHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->call('deleteComment', $creatorComment->id);

        $this->assertDatabaseHas('content_event_comments', ['id' => $creatorComment->id]);

        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->call('deleteComment', $creatorComment->id);

        $this->assertDatabaseMissing('content_event_comments', ['id' => $creatorComment->id]);
    }

    public function test_comment_body_is_required_and_limited(): void
    {
        $event = $this->event($this->staffHr);

        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->set('commentBody', '')
            ->call('addComment')
            ->assertHasErrors(['commentBody']);

        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->set('commentBody', str_repeat('a', 1001))
            ->call('addComment')
            ->assertHasErrors(['commentBody']);
    }

    public function test_comments_are_removed_with_the_event(): void
    {
        $event = $this->event($this->staffHr);

        $comment = ContentEventComment::create([
            'content_event_id' => $event->id,
            'user_id_user' => $this->staffHr->id_user,
            'body' => 'Komentar',
        ]);

        $event->delete();

        $this->assertDatabaseMissing('content_event_comments', ['id' => $comment->id]);
    }

    public function test_other_staff_can_edit_and_delete_an_event(): void
    {
        $event = $this->event($this->staffHr, ['title' => 'Event Staff Lain']);

        Livewire::actingAs($this->otherStaffHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->assertSet('readOnly', false)
            ->assertSee('Event Staff Lain')
            ->assertSee('content-date')
            ->assertSee('content-url')
            ->assertSeeHtml('wire:click="deleteEvent"');
    }

    public function test_creator_autosaves_a_field_and_shows_the_saved_indicator(): void
    {
        $event = $this->event($this->staffHr, ['title' => 'Judul Lama']);

        Livewire::actingAs($this->staffHr)
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
        $event = $this->event($this->staffHr, ['title' => 'Judul Lama']);

        Livewire::actingAs($this->staffHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->set('title', '')
            ->call('saveField', 'title')
            ->assertHasErrors(['title']);

        $this->assertDatabaseHas('content_events', ['id' => $event->id, 'title' => 'Judul Lama']);
    }

    public function test_every_allowed_user_can_edit_delete_or_drag_an_event(): void
    {
        $event = $this->event($this->staffHr, ['title' => 'Event HR']);

        foreach ([$this->staffHr, $this->otherStaffHr, $this->kadivHr, $this->hrd, $this->staffMarkom] as $user) {
            $this->assertTrue(Gate::forUser($user)->allows('update', $event));
            $this->assertTrue(Gate::forUser($user)->allows('delete', $event));
        }

        Livewire::actingAs($this->otherStaffHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->assertSet('readOnly', false)
            ->set('title', 'Diubah Staff Lain')
            ->call('saveField', 'title')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('content_events', ['id' => $event->id, 'title' => 'Diubah Staff Lain']);

        Livewire::actingAs($this->hrd)
            ->test(ContentCalendar::class)
            ->call('moveEvent', $event->id, '2026-11-20');

        $this->assertDatabaseHas('content_events', [
            'id' => $event->id,
            'event_date' => '2026-11-20',
        ]);

        Livewire::actingAs($this->kadivHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->call('deleteEvent');

        $this->assertDatabaseMissing('content_events', ['id' => $event->id]);
    }

    public function test_hrd_can_view_comment_and_edit(): void
    {
        $event = $this->event($this->staffHr, ['title' => 'Event HR']);

        Livewire::actingAs($this->hrd)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->assertSet('readOnly', false)
            ->assertSee('Komentar')
            ->set('commentBody', 'Mohon cek brief')
            ->call('addComment')
            ->assertHasNoErrors()
            ->set('title', 'Event HRD')
            ->call('saveField', 'title')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('content_event_comments', [
            'content_event_id' => $event->id,
            'user_id_user' => $this->hrd->id_user,
            'body' => 'Mohon cek brief',
        ]);

        $this->assertDatabaseHas('content_events', ['id' => $event->id, 'title' => 'Event HRD']);
    }

    public function test_other_staff_can_delete(): void
    {
        $event = $this->event($this->staffHr);

        $this->assertTrue(Gate::forUser($this->otherStaffHr)->allows('delete', $event));

        Livewire::actingAs($this->otherStaffHr)
            ->test(ContentCalendar::class)
            ->call('openEvent', $event->id)
            ->call('deleteEvent');

        $this->assertDatabaseMissing('content_events', ['id' => $event->id]);
    }

    public function test_drag_moves_the_event_date_and_can_be_undone(): void
    {
        $event = $this->event($this->staffHr, ['event_date' => '2026-11-10']);

        Livewire::actingAs($this->staffHr)
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

    public function test_week_cards_are_draggable_for_every_allowed_user(): void
    {
        $this->event($this->staffHr, ['event_date' => '2026-11-10']);

        foreach ([$this->staffHr, $this->otherStaffHr] as $user) {
            Livewire::actingAs($user)
                ->test(ContentCalendar::class)
                ->set('view', 'minggu')
                ->set('week', '2026-11-09')
                ->assertSeeHtml('draggable="true"');
        }
    }

    public function test_other_staff_can_drag_an_event(): void
    {
        $event = $this->event($this->staffHr, ['event_date' => '2026-11-10']);

        Livewire::actingAs($this->otherStaffHr)
            ->test(ContentCalendar::class)
            ->call('moveEvent', $event->id, '2026-11-12');

        $this->assertDatabaseHas('content_events', [
            'id' => $event->id,
            'event_date' => '2026-11-12',
        ]);
    }

    public function test_closing_a_dirty_create_modal_is_guarded_on_the_server(): void
    {
        Livewire::actingAs($this->staffHr)
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
