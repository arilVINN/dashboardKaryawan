<?php

namespace Tests\Feature;

use Tests\TestCase;

class GlobalLoaderTest extends TestCase
{
    public function test_ajax_forms_do_not_trigger_the_sticky_page_loader(): void
    {
        $js = (string) file_get_contents(resource_path('js/app.js'));

        $this->assertStringContainsString('e.defaultPrevented', $js);
        $this->assertStringContainsString("addEventListener('submit'", $js);
    }

    public function test_ajax_forms_that_call_prevent_default(): void
    {
        $login = (string) file_get_contents(resource_path('views/component/loginForm.blade.php'));
        $this->assertStringContainsString('e.preventDefault()', $login);

        $profile = (string) file_get_contents(resource_path('views/profile/index.blade.php'));
        $this->assertStringContainsString('e.preventDefault()', $profile);

        $loginView = (string) file_get_contents(resource_path('views/login.blade.php'));
        $this->assertStringContainsString("@vite(['resources/css/app.css', 'resources/js/app.js'])", $loginView);
    }
}
