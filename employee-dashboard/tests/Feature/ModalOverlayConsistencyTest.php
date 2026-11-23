<?php

namespace Tests\Feature;

use Tests\TestCase;

class ModalOverlayConsistencyTest extends TestCase
{
    public function test_dialog_overlays_use_the_single_dim_token(): void
    {
        $token = 'bg-[rgba(15,40,55,.45)]';
        $allowedBlur = 'components/birthday-popup.blade.php';
        $count = 0;

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'))
        );

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $text = (string) file_get_contents($file->getPathname());
            $relative = str_replace(resource_path('views').'/', '', $file->getPathname());

            preg_match_all('/class="([^"]*)"/s', $text, $matches);

            foreach ($matches[1] as $classes) {
                if (! preg_match('/\b(?:fixed|absolute)\s+inset-0\b/', $classes)) {
                    continue;
                }
                if (! preg_match('/bg-(?:\[rgba\(|slate-(?:900|950)\/|black\/)/', $classes)) {
                    continue;
                }

                $count++;

                if ($relative === $allowedBlur) {
                    // The birthday popup is the one deliberate blur exception.
                    continue;
                }

                $this->assertStringContainsString($token, $classes, $relative);
                $this->assertStringNotContainsString('backdrop-blur', $classes, $relative);
            }
        }

        $this->assertGreaterThan(30, $count, 'Expected to check all dialog overlays.');
    }

    public function test_dialog_entrance_animation_is_defined_and_applied_to_form_modals(): void
    {
        $css = (string) file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('@keyframes modal-overlay-in', $css);
        $this->assertStringContainsString('@keyframes modal-panel-in', $css);

        $views = [
            'livewire/hrd/compose-message.blade.php',
            'livewire/staff/compose-message.blade.php',
            'livewire/kadiv/pesan-table.blade.php',
            'finance/pemasukan/index.blade.php',
            'finance/pengeluaran/index.blade.php',
            'finance/laporan/index.blade.php',
            'kadiv/laporanLabaRugi.blade.php',
            'hrd/detailDivisi.blade.php',
            'components/carousel.blade.php',
            'livewire/finance/arus-kas.blade.php',
            'livewire/finance/jurnal-umum-table.blade.php',
        ];

        foreach ($views as $view) {
            $html = (string) file_get_contents(resource_path('views/'.$view));
            $this->assertStringContainsString('modal-overlay-in', $html, $view);
            $this->assertStringContainsString('modal-panel-in', $html, $view);
        }
    }
}
