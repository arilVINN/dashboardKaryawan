<?php

namespace Tests\Feature;

use App\Livewire\ContentCalendar;
use App\Models\ContentAccount;
use App\Models\ContentEvent;
use App\Models\ContentType;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use App\Support\ContentCalendarAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ContentCalendarMarkomTest extends TestCase
{
    use RefreshDatabase;

    private User2 $staffPb;

    private User2 $markomPb;

    private User2 $layouterPb;

    private User2 $kadivPb;

    private User2 $staffHr;

    private User2 $direktur;

    private ContentEvent $event;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'RL-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'RL-KADIV', 'nama_role' => 'kadiv'],
            ['id_role' => 'RL-HRD', 'nama_role' => 'hrd'],
            ['id_role' => 'RL-DIR', 'nama_role' => 'direktur'],
        ]);

        $pb = Divisi::create([
            'id_divisi' => 'DV-PB', 'kode_divisi' => 'PB',
            'nama_divisi' => 'Publikasi Buku', 'status_aktif' => 'Aktif',
        ]);
        $hr = Divisi::create([
            'id_divisi' => 'DV-HR', 'kode_divisi' => 'HR',
            'nama_divisi' => 'HR', 'status_aktif' => 'Aktif',
        ]);

        $this->staffPb = $this->makeUser('U-SPB', 'staffpb', 'Staff PB', 'Staff', $pb, 'RL-STAFF');
        $this->markomPb = $this->makeUser('U-MPB', 'markompb', 'Markom PB', 'Markom', $pb, 'RL-STAFF');
        $this->layouterPb = $this->makeUser('U-LPB', 'layouterpb', 'Layouter PB', 'Layouter', $pb, 'RL-STAFF');
        $this->kadivPb = $this->makeUser('U-KPB', 'kadivpb', 'Kadiv PB', 'Kepala Divisi Publikasi Buku', $pb, 'RL-KADIV');
        $this->staffHr = $this->makeUser('U-SHR', 'staffhr', 'Staff HR', 'Staff', $hr, 'RL-STAFF');
        $this->direktur = $this->makeUser('U-DIR', 'direkturtest', 'Direktur', 'Direktur Utama', $hr, 'RL-DIR');

        ContentType::insert([
            ['slug' => 'reels', 'name' => 'Reels', 'sort_order' => 1],
        ]);

        $account = ContentAccount::create([
            'slug' => 'akun-pb', 'name' => 'Akun PB', 'handle' => '@pb',
            'platform' => 'Instagram', 'sort_order' => 1, 'is_active' => true,
        ]);

        $this->event = ContentEvent::create([
            'title' => 'Konten Publikasi Buku',
            'event_date' => '2026-11-12',
            'content_type' => 'reels',
            'content_account_id' => $account->id,
            'status' => 'process',
            'created_by' => $this->kadivPb->id_user,
        ]);

        ContentCalendarAccess::flush();
    }

    public function test_markom_is_an_available_jabatan_option(): void
    {
        $this->assertContains(Karyawan::JABATAN_MARKOM, Karyawan::JABATAN_OPTIONS);
        $this->assertSame('Markom', Karyawan::JABATAN_MARKOM);

        $this->assertTrue($this->markomPb->karyawan->isMarkom());
        $this->assertFalse($this->staffPb->karyawan->isMarkom());

        // Variasi lama "Marcom Buku" juga dikenali.
        $this->markomPb->karyawan->update(['jabatan' => 'Marcom Buku']);
        $this->assertTrue($this->markomPb->karyawan->fresh()->isMarkom());
    }

    public function test_layouter_is_an_available_jabatan_option_for_publikasi_buku(): void
    {
        $this->assertContains(Karyawan::JABATAN_LAYOUTER, Karyawan::JABATAN_OPTIONS);
        $this->assertSame('Layouter', Karyawan::JABATAN_LAYOUTER);

        $this->assertTrue($this->layouterPb->karyawan->hasRole('staff'));
        $this->assertFalse($this->layouterPb->karyawan->isMarkom());
    }

    public function test_layouter_in_publikasi_buku_is_view_and_comment_only(): void
    {
        $this->assertTrue(ContentCalendarAccess::allows($this->layouterPb));
        $this->assertFalse(ContentCalendarAccess::canManage($this->layouterPb));

        $this->assertTrue(Gate::forUser($this->layouterPb)->allows('view', $this->event));
        $this->assertFalse(Gate::forUser($this->layouterPb)->allows('create', ContentEvent::class));
        $this->assertFalse(Gate::forUser($this->layouterPb)->allows('update', $this->event));
        $this->assertFalse(Gate::forUser($this->layouterPb)->allows('delete', $this->event));

        Livewire::actingAs($this->layouterPb)
            ->test(ContentCalendar::class)
            ->call('openEvent', $this->event->id)
            ->assertSet('readOnly', true)
            ->set('commentBody', 'Komentar layouter')
            ->call('addComment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('content_event_comments', [
            'content_event_id' => $this->event->id,
            'user_id_user' => $this->layouterPb->id_user,
            'body' => 'Komentar layouter',
        ]);
    }

    public function test_publikasi_buku_access_rules(): void
    {
        foreach ([$this->staffPb, $this->markomPb, $this->layouterPb, $this->kadivPb] as $user) {
            $this->assertTrue(ContentCalendarAccess::allows($user));
        }

        // Hanya kadiv + markom yang bisa CRUD di Publikasi Buku.
        $this->assertTrue(ContentCalendarAccess::canManage($this->kadivPb));
        $this->assertTrue(ContentCalendarAccess::canManage($this->markomPb));
        $this->assertFalse(ContentCalendarAccess::canManage($this->staffPb));

        // Divisi lain (HR) dan direksi tetap memiliki akses penuh.
        $this->assertTrue(ContentCalendarAccess::canManage($this->staffHr));
        $this->assertTrue(ContentCalendarAccess::canManage($this->direktur));
    }

    public function test_gate_permissions_follow_the_access_rules(): void
    {
        $this->assertTrue(Gate::forUser($this->staffPb)->allows('view', $this->event));
        $this->assertFalse(Gate::forUser($this->staffPb)->allows('create', ContentEvent::class));
        $this->assertFalse(Gate::forUser($this->staffPb)->allows('update', $this->event));
        $this->assertFalse(Gate::forUser($this->staffPb)->allows('delete', $this->event));

        foreach ([$this->markomPb, $this->kadivPb, $this->staffHr] as $user) {
            $this->assertTrue(Gate::forUser($user)->allows('create', ContentEvent::class));
            $this->assertTrue(Gate::forUser($user)->allows('update', $this->event));
            $this->assertTrue(Gate::forUser($user)->allows('delete', $this->event));
        }
    }

    public function test_view_only_staff_can_comment_but_cannot_open_create_form(): void
    {
        Livewire::actingAs($this->staffPb)
            ->test(ContentCalendar::class)
            ->call('openEvent', $this->event->id)
            ->assertSet('detailOpen', true)
            ->assertSet('readOnly', true)
            ->set('commentBody', 'Komentar dari staff PB')
            ->call('addComment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('content_event_comments', [
            'content_event_id' => $this->event->id,
            'user_id_user' => $this->staffPb->id_user,
            'body' => 'Komentar dari staff PB',
        ]);

        // openCreate() mengauthorize 'create', jadi form tidak boleh terbuka.
        Livewire::actingAs($this->staffPb)
            ->test(ContentCalendar::class)
            ->call('openCreate')
            ->assertSet('formOpen', false);
    }

    public function test_create_button_is_hidden_for_view_only_staff(): void
    {
        $htmlForStaff = Livewire::actingAs($this->staffPb)
            ->test(ContentCalendar::class)
            ->html();
        $this->assertStringNotContainsString('id="content-add-event"', $htmlForStaff);

        $htmlForMarkom = Livewire::actingAs($this->markomPb)
            ->test(ContentCalendar::class)
            ->html();
        $this->assertStringContainsString('id="content-add-event"', $htmlForMarkom);
    }

    private function makeUser(
        string $id,
        string $username,
        string $nama,
        string $jabatan,
        Divisi $divisi,
        string $roleId
    ): User2 {
        Karyawan::create([
            'id_karyawan' => 'K-'.$id,
            'nama' => $nama,
            'jenis_kelamin' => 'Laki-laki',
            'jabatan' => $jabatan,
            'divisi_id_divisi' => $divisi->id_divisi,
            'aktif' => true,
        ]);

        return User2::create([
            'id_user' => $id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => 'K-'.$id,
        ]);
    }
}
