<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProfileLayoutTest extends TestCase
{
    public function test_profile_picture_lives_in_informasi_pribadi_card_and_is_larger(): void
    {
        $card = $this->informasiPribadiCard();

        $this->assertStringContainsString('id="prof-foto"', $card);
        $this->assertStringContainsString('id="prof-inisial"', $card);
        $this->assertStringContainsString('h-32 w-32', $card);
    }

    public function test_name_email_and_phone_sit_beside_the_picture(): void
    {
        $card = $this->informasiPribadiCard();

        $this->assertStringContainsString('id="hdr-nama"', $card);
        $this->assertStringContainsString('id="hdr-email"', $card);
        $this->assertStringContainsString('id="hdr-telp"', $card);

        $html = (string) file_get_contents(resource_path('views/profile/index.blade.php'));
        $header = substr($html, 0, strpos($html, 'id="view-info"'));
        $this->assertStringNotContainsString('id="hdr-nama"', $header);
        $this->assertStringNotContainsString('Foto profil</p>', $html);
    }

    public function test_address_and_rekening_are_inline_and_details_are_not_duplicated(): void
    {
        $card = $this->informasiPribadiCard();

        $this->assertStringContainsString('id="prof-alamat"', $card);
        $this->assertStringContainsString('id="prof-rekening"', $card);
        $this->assertStringNotContainsString('id="prof-nama"', $card);
        $this->assertStringNotContainsString('id="prof-email"', $card);
        $this->assertStringNotContainsString('id="prof-telepon"', $card);
        $this->assertStringContainsString('sm:grid-cols-2', $card);
    }

    public function test_informasi_pribadi_comes_before_detail_karyawan(): void
    {
        $html = (string) file_get_contents(resource_path('views/profile/index.blade.php'));

        $this->assertLessThan(
            strpos($html, '>Detail Karyawan<'),
            strpos($html, '>Informasi Pribadi<')
        );
    }

    public function test_page_heading_and_grouped_employee_details(): void
    {
        $html = (string) file_get_contents(resource_path('views/profile/index.blade.php'));

        $this->assertStringNotContainsString('Kelola informasi pribadi dan pengaturan akun Anda.', $html);
        $this->assertStringContainsString('px-6 pb-8 pt-6 sm:px-8', $html);
        $this->assertStringContainsString('text-xl font-semibold text-[#172B36]', $html);
        $this->assertStringContainsString('text-[12px] font-semibold uppercase tracking-wide text-[#475569]', $html);
        $this->assertStringContainsString('Informasi Kepegawaian', $html);
        $this->assertStringContainsString('Informasi Pekerjaan', $html);
        $this->assertStringContainsString('Informasi Personal', $html);
        $this->assertStringContainsString('id="prof-status-karyawan"', $html);
        $this->assertStringContainsString('id="prof-status-aktif"', $html);
    }

    public function test_password_form_is_empty_with_toggles_and_inline_feedback(): void
    {
        $html = (string) file_get_contents(resource_path('views/profile/index.blade.php'));

        foreach (['current_password', 'new_password', 'confirm_password'] as $id) {
            $this->assertStringContainsString('id="'.$id.'"', $html);
        }
        $this->assertStringNotContainsString('value="********"', $html);
        $this->assertStringContainsString('data-toggle-password="current_password"', $html);
        $this->assertStringContainsString('id="password-error"', $html);
        $this->assertStringContainsString('Minimal 6 karakter', $html);
    }

    public function test_account_created_at_sits_in_pekerjaan_and_logout_is_removed(): void
    {
        $html = (string) file_get_contents(resource_path('views/profile/index.blade.php'));

        $start = strpos($html, '>Informasi Pekerjaan<');
        $end = strpos($html, '>Informasi Personal<');
        $this->assertNotFalse($start);
        $this->assertNotFalse($end);

        $pekerjaan = substr($html, $start, $end - $start);
        $this->assertStringContainsString('Akun Dibuat', $pekerjaan);
        $this->assertStringContainsString('id="prof-dibuat"', $pekerjaan);
        $this->assertStringNotContainsString('id="btn-logout"', $html);
        $this->assertStringNotContainsString('>Informasi Akun<', $html);
    }

    private function informasiPribadiCard(): string
    {
        $html = (string) file_get_contents(resource_path('views/profile/index.blade.php'));
        $start = strpos($html, 'id="view-info"');
        $end = strpos($html, 'id="edit-info"');

        $this->assertNotFalse($start);
        $this->assertNotFalse($end);

        return substr($html, $start, $end - $start);
    }
}
