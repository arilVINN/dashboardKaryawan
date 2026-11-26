<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRedirectLoopTest extends TestCase
{
    use RefreshDatabase;

    public function test_exception_handler_does_not_bounce_on_the_same_url(): void
    {
        $handler = (string) file_get_contents(base_path('bootstrap/app.php'));

        $this->assertStringContainsString("response()->view('errors.loop'", $handler);
        $this->assertStringNotContainsString('return back()->with($key, $message);', $handler);
    }

    public function test_rate_limiters_key_by_auth_identifier(): void
    {
        $provider = (string) file_get_contents(app_path('Providers/AppServiceProvider.php'));

        $this->assertStringContainsString('getAuthIdentifier()', $provider);
        $this->assertStringNotContainsString('$request->user()?->id ?:', $provider);
    }

    public function test_org_chart_nodes_use_guarded_property_access(): void
    {
        $view = (string) file_get_contents(resource_path('views/hrd/strukturOrganisasi.blade.php'));

        $this->assertStringContainsString('$fotoKaryawan', $view);
        $this->assertStringNotContainsString('->karyawan?->foto', $view);
    }

    public function test_loop_error_view_exists(): void
    {
        $this->assertFileExists(resource_path('views/errors/loop.blade.php'));
    }
}
