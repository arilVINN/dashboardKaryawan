<?php

namespace Tests\Feature;

use App\Support\StatusPalette;
use Tests\TestCase;

class StatusPillConsistencyTest extends TestCase
{
    public function test_workflow_statuses_use_the_canonical_tokens(): void
    {
        $expected = [
            'baru' => ['bg' => '#DCEEFE', 'border' => '#93C5FD', 'dot' => '#2563EB'],
            'berjalan' => ['bg' => '#D9F5E5', 'border' => '#86D9AE', 'dot' => '#059669'],
            'menunggu' => ['bg' => '#FEF0C3', 'border' => '#F5C85C', 'dot' => '#D97706'],
            'disetujui' => ['bg' => '#D5F3F0', 'border' => '#7DD3C8', 'dot' => '#0D9488'],
            'terlambat' => ['bg' => '#FDE0E0', 'border' => '#F5A3A3', 'dot' => '#DC2626'],
            'aktif' => ['bg' => '#D9F5E5', 'border' => '#86D9AE', 'dot' => '#059669'],
            'tidak_aktif' => ['bg' => '#F1F5F9', 'border' => '#CBD5E1', 'dot' => '#64748B'],
        ];

        foreach ($expected as $key => $tokens) {
            $palette = StatusPalette::for($key);

            $this->assertNotNull($palette, $key);
            $this->assertSame('#172B36', $palette['text'], "$key text");

            foreach ($tokens as $token => $value) {
                $this->assertSame($value, $palette[$token], "$key.$token");
            }
        }
    }

    public function test_finance_and_localized_statuses_resolve_through_the_palette(): void
    {
        $this->assertSame('ditolak', StatusPalette::normalize('tolak'));
        $this->assertSame('disetujui', StatusPalette::normalize('ACC'));
        $this->assertSame('menunggu', StatusPalette::normalize('Pending'));
        $this->assertSame('perlu_revisi', StatusPalette::normalize('Perlu revisi'));

        $this->assertSame('Ditolak', StatusPalette::pill('ditolak')['label']);
        $this->assertSame('#E2E8EC', StatusPalette::pill('dicairkan')['bg']);
        $this->assertSame('Terverifikasi', StatusPalette::pill('terverifikasi')['label']);
        $this->assertSame('Custom label', StatusPalette::pill('baru', 'Custom label')['label']);
    }

    public function test_category_pills_use_the_shared_palettes(): void
    {
        $this->assertSame('#FEF0C3', StatusPalette::divisionType('direktur')['bg']);
        $this->assertSame('#EDE9FE', StatusPalette::divisionType('manajemen')['bg']);
        $this->assertSame('#DCEEFE', StatusPalette::divisionType('divisi')['bg']);
        $this->assertSame('#DCEEFE', StatusPalette::divisionType(null)['bg']);

        $this->assertSame('#DCEEFE', StatusPalette::messageType('pesan')['bg']);
        $this->assertSame('#FEF0C3', StatusPalette::messageType('surat')['bg']);
        $this->assertSame('#DCEEFE', StatusPalette::messageType(null)['bg']);
    }

    public function test_shared_badge_component_renders_a_canonical_pill(): void
    {
        $html = view('components.status-badge', ['status' => 'baru', 'dot' => true])->render();

        $this->assertStringContainsString('background-color: #DCEEFE', $html);
        $this->assertStringContainsString('border-color: #93C5FD', $html);
        $this->assertStringContainsString('h-1.5 w-1.5', $html);
        $this->assertStringContainsString('text-[11px]', $html);
        $this->assertStringContainsString('Baru', $html);
    }

    public function test_shared_badge_accepts_category_palette_overrides(): void
    {
        $html = view('components.status-badge', [
            'status' => 'direktur',
            'label' => 'Direktur Utama',
            'palette' => StatusPalette::divisionType('direktur'),
            'dot' => true,
        ])->render();

        $this->assertStringContainsString('background-color: #FEF0C3', $html);
        $this->assertStringContainsString('border-color: #F5C85C', $html);
        $this->assertStringContainsString('#D97706', $html);
        $this->assertStringContainsString('Direktur Utama', $html);
    }

    public function test_shared_js_renderer_is_palette_driven(): void
    {
        $html = view('components.status-badge-js')->render();

        $this->assertStringContainsString('window.statusBadgeHtml', $html);
        $this->assertStringContainsString('window.statusBadgeKey', $html);
        $this->assertStringContainsString('window.statusBadgeRender', $html);
        $this->assertStringContainsString('#DCEEFE', $html);
        $this->assertStringContainsString('h-1.5 w-1.5', $html);
    }
}
