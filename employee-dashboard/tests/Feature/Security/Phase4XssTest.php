<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

/**
 * Regression tests for the Phase 4 XSS fixes:
 *  - the compact staff task table escapes task titles like the full table,
 *  - the org-chart data-employee attribute cannot be broken out of.
 */
class Phase4XssTest extends TestCase
{
    public function test_both_task_table_brackets_escape_task_titles(): void
    {
        $source = file_get_contents(resource_path('views/component/tableTugas.blade.php'));

        // Compact card and full table must both pass judul_tugas through the
        // HTML escaper before inserting it with innerHTML.
        $this->assertSame(
            2,
            substr_count($source, 'const judul = staffEscapeHtml(t.judul_tugas);'),
        );
    }

    public function test_org_chart_card_escapes_employee_payload(): void
    {
        $member = (object) [
            'id' => 'NODE-1',
            'nama' => "O'Reilly <script>alert(1)</script>",
            'jabatan' => 'Direktur',
            'divisi' => 'A',
            'status_kerja' => 'tetap',
            'garis_putus' => false,
            'email' => 'x@example.test',
            'no_telepon' => '123',
            'keterangan' => '-',
            'foto' => null,
            'karyawan' => null,
            'parent_id' => null,
            'urutan' => 1,
        ];

        $html = view('hrd.partials.org_card_item', [
            'member' => $member,
            'canManage' => false,
        ])->render();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('&#039;', $html);
    }
}
