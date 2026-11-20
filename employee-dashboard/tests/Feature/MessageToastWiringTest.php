<?php

namespace Tests\Feature;

use Tests\TestCase;

class MessageToastWiringTest extends TestCase
{
    private function viewSource(string $path): string
    {
        return (string) file_get_contents(resource_path('views/'.$path));
    }

    public function test_topbar_provides_the_reusable_toast_helpers(): void
    {
        $html = $this->viewSource('component/topbar.blade.php');

        $this->assertStringContainsString('id="toast-container"', $html);
        $this->assertStringContainsString('window.appToast', $html);
        $this->assertStringContainsString('window.appToastPending', $html);
        $this->assertStringContainsString("Livewire.on('app-toast'", $html);
    }

    public function test_every_message_success_path_announces_its_result(): void
    {
        $this->assertStringContainsString(
            "appToastPending('Balasan berhasil dikirim.'",
            $this->viewSource('staff/detailPesan.blade.php')
        );
        $this->assertStringContainsString(
            "appToast('Balasan berhasil dikirim.'",
            $this->viewSource('kadiv/detailPesan.blade.php')
        );
        $this->assertStringContainsString(
            "appToastPending('Balasan berhasil dikirim.'",
            $this->viewSource('hrd/detailPesan.blade.php')
        );
        $this->assertStringContainsString(
            "appToastPending('Pesan berhasil dikirim.'",
            $this->viewSource('component_hrd/tabelPesan.blade.php')
        );
    }

    public function test_message_views_no_longer_use_native_alert_for_results(): void
    {
        $paths = [
            'staff/detailPesan.blade.php',
            'kadiv/detailPesan.blade.php',
            'hrd/detailPesan.blade.php',
            'component_hrd/tabelPesan.blade.php',
            'components/modalSuratIzin.blade.php',
            'livewire/kadiv/pesan-table.blade.php',
        ];

        foreach ($paths as $path) {
            $this->assertStringNotContainsString('alert(', $this->viewSource($path), $path);
        }
    }

    public function test_kadiv_message_table_dispatches_toasts_instead_of_flash_banners(): void
    {
        $php = (string) file_get_contents(app_path('Livewire/Kadiv/PesanTable.php'));

        $this->assertStringContainsString("dispatch('app-toast'", $php);
        $this->assertStringNotContainsString('pesan_success', $php);
        $this->assertStringNotContainsString(
            'session(\'pesan_success\')',
            $this->viewSource('livewire/kadiv/pesan-table.blade.php')
        );
    }
}
