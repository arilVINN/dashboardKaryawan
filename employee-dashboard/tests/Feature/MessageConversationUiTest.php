<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Pesan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MessageConversationUiTest extends TestCase
{
    use RefreshDatabase;

    private User2 $hrd;

    private User2 $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'RL-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'RL-ST', 'nama_role' => 'Staff'],
        ]);
        Divisi::create([
            'id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif',
        ]);

        $this->hrd = $this->account('K-HRD', 'HRD Satu', 'RL-HRD');
        $this->staff = $this->account('K-ST', 'Staff Satu', 'RL-ST');

        Pesan::create([
            'id_pesan' => 'PSN-THREAD',
            'judul_pesan' => 'Koordinasi jadwal terbit',
            'deskripsi' => "Baris pertama\nBaris kedua",
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-11-20',
            'created_at' => '2026-11-20 09:15:00',
            'updated_at' => '2026-11-20 09:15:00',
            'pengirim_id_user' => $this->hrd->id_user,
            'penerima_id_user' => $this->staff->id_user,
        ]);
        Pesan::create([
            'id_pesan' => 'PSN-REPLY',
            'judul_pesan' => 'Balasan',
            'deskripsi' => 'Baik, saya siapkan.',
            'tipe' => 'pesan',
            'tanggal_pesan' => '2026-11-20',
            'created_at' => '2026-11-20 10:30:00',
            'updated_at' => '2026-11-20 10:30:00',
            'pengirim_id_user' => $this->staff->id_user,
            'penerima_id_user' => $this->hrd->id_user,
            'balasan_dari_id_pesan' => 'PSN-THREAD',
        ]);
    }

    public function test_detail_page_is_a_single_column_conversation(): void
    {
        $html = $this->actingAs($this->hrd)
            ->get('/hrd/detailPesan/PSN-THREAD')
            ->assertOk()
            ->assertSee('Koordinasi jadwal terbit')
            ->assertSee('Daftar Pesan')
            ->assertSee('20 Nov 2026')
            ->assertSee('Baris pertama')
            ->assertSee('Baik, saya siapkan.')
            ->assertSee('Kirim balasan')
            ->assertSee('Ctrl + Enter untuk mengirim')
            ->assertDontSee('Detail Pesan', false)
            ->getContent();

        $this->assertStringContainsString('data-message-conversation', $html);
        $this->assertStringContainsString('max-w-[760px]', $html);
        $this->assertStringContainsString('Anda', $html);
        $this->assertMatchesRegularExpression('/\d{2} Nov 2026, \d{2}:\d{2}/', $html);
    }

    public function test_staff_detail_without_a_valid_message_returns_404(): void
    {
        // The app's global handler turns web 404s into a redirect with an error.
        $this->actingAs($this->staff)->get('/pesan/detail')
            ->assertRedirect()->assertSessionHas('error');
        $this->actingAs($this->staff)->get('/pesan/detail/PSN-TIDAK-ADA')
            ->assertRedirect()->assertSessionHas('error');
    }

    public function test_detail_pages_use_shared_breadcrumb_and_standard_spacing(): void
    {
        foreach (['staff', 'kadiv', 'hrd'] as $role) {
            $html = (string) file_get_contents(resource_path('views/'.$role.'/detailPesan.blade.php'));

            $this->assertStringContainsString('component.breadcrumbs', $html, $role);
            $this->assertStringContainsString("'inline' => true", $html, $role);
            $this->assertStringContainsString('px-6 pb-8 pt-6 sm:px-8', $html, $role);
        }

        $partial = (string) file_get_contents(resource_path('views/components/message-conversation.blade.php'));
        $this->assertStringNotContainsString('data-breadcrumb-parent', $partial);
        $this->assertStringContainsString('max-w-[760px]', $partial);
        $this->assertStringNotContainsString('mx-auto', $partial);
    }

    public function test_non_replyable_conversation_shows_single_line(): void
    {
        Pesan::whereKey('PSN-THREAD')->update(['tipe' => 'surat']);

        $this->actingAs($this->hrd)
            ->get('/hrd/detailPesan/PSN-THREAD')
            ->assertOk()
            ->assertSee('Surat izin')
            ->assertSee('Percakapan ini tidak dapat dibalas.')
            ->assertDontSee('data-reply-submit disabled');
    }

    private function account(string $id, string $nama, string $role): User2
    {
        Karyawan::create([
            'id_karyawan' => $id, 'nama' => $nama, 'jenis_kelamin' => 'Laki-laki',
            'jabatan' => 'Staff', 'divisi_id_divisi' => 'DIV-IT',
        ]);

        return User2::create([
            'id_user' => $id, 'username' => strtolower($id),
            'password' => Hash::make('pass123'), 'role_id_role' => $role,
            'karyawan_id_karyawan' => $id,
        ]);
    }
}
