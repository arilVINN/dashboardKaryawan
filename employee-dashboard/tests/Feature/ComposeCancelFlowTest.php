<?php

namespace Tests\Feature;

use App\Livewire\Hrd\ComposeMessage as HrdCompose;
use App\Livewire\Kadiv\PesanTable as KadivPesanTable;
use App\Livewire\Staff\ComposeMessage as StaffCompose;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ComposeCancelFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Divisi::create([
            'id_divisi' => 'DIV-IT', 'kode_divisi' => 'IT',
            'nama_divisi' => 'Teknologi Informasi', 'status_aktif' => 'Aktif',
        ]);
        Role::insert([
            ['id_role' => 'RL-HRD', 'nama_role' => 'HRD'],
            ['id_role' => 'RL-ST', 'nama_role' => 'Staff'],
            ['id_role' => 'RL-KD', 'nama_role' => 'Kadiv'],
        ]);
        foreach ([['U-HRD', 'RL-HRD'], ['U-ST', 'RL-ST'], ['U-KD', 'RL-KD']] as [$id, $role]) {
            Karyawan::create([
                'id_karyawan' => 'K-'.$id, 'nama' => 'User '.$id,
                'jenis_kelamin' => 'Laki-laki', 'jabatan' => 'Staff', 'divisi_id_divisi' => 'DIV-IT',
            ]);
            User2::create([
                'id_user' => $id, 'username' => strtolower($id),
                'password' => Hash::make('pass123'), 'role_id_role' => $role,
                'karyawan_id_karyawan' => 'K-'.$id,
            ]);
        }
    }

    public function test_hrd_cancel_flow(): void
    {
        Livewire::actingAs(User2::find('U-HRD'))
            ->test(HrdCompose::class)
            ->call('open')
            ->set('isi', 'Draft pesan')
            ->call('requestClose')
            ->assertSet('confirmDiscard', true)
            ->assertSet('showModal', true)
            ->call('discardDraft')
            ->assertSet('showModal', false)
            ->assertSet('confirmDiscard', false);
    }

    public function test_staff_cancel_flow(): void
    {
        Livewire::actingAs(User2::find('U-ST'))
            ->test(StaffCompose::class)
            ->call('openModal')
            ->set('divisiId', 'DIV-IT')
            ->set('isi', 'Draft pesan')
            ->call('requestClose')
            ->assertSet('confirmDiscard', true)
            ->call('discardDraft')
            ->assertSet('showModal', false);
    }

    public function test_kadiv_cancel_flow(): void
    {
        Livewire::actingAs(User2::find('U-KD'))
            ->test(KadivPesanTable::class)
            ->call('openCompose')
            ->set('isi', 'Draft pesan')
            ->call('requestClose')
            ->assertSet('confirmDiscard', true)
            ->call('discardDraft')
            ->assertSet('showCompose', false);
    }

    public function test_backdrop_click_is_bound_on_the_dimmed_overlay(): void
    {
        foreach ([
            'livewire/hrd/compose-message.blade.php',
            'livewire/staff/compose-message.blade.php',
            'livewire/kadiv/pesan-table.blade.php',
        ] as $view) {
            $html = (string) file_get_contents(resource_path('views/'.$view));
            preg_match_all('/<div\b[^>]*>/s', $html, $tags);

            $hasBackdropHandler = false;
            foreach ($tags[0] as $tag) {
                if (str_contains($tag, 'fixed inset-0') && str_contains($tag, 'wire:click.self="requestClose"')) {
                    $hasBackdropHandler = true;
                    break;
                }
            }

            $this->assertTrue($hasBackdropHandler, $view.' must close on backdrop click');
        }
    }
}
