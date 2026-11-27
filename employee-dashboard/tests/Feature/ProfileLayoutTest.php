<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProfileLayoutTest extends TestCase
{
    public function test_header_lives_outside_cards_with_avatar_and_identity(): void
    {
        $html = $this->html();
        $start = strpos($html, '<header');
        $end = strpos($html, '</header>');

        $this->assertNotFalse($start);
        $this->assertNotFalse($end);

        $header = substr($html, $start, $end - $start);

        $this->assertStringContainsString('h-24 w-24', $header);
        $this->assertStringContainsString('bg-[#AFD3E2]', $header);
        $this->assertStringContainsString('text-[#0B4A66]', $header);
        $this->assertStringContainsString('id="prof-foto"', $header);
        $this->assertStringContainsString('id="prof-inisial"', $header);
        $this->assertStringContainsString('id="btn-ubah-foto"', $header);
        $this->assertStringContainsString('aria-label="Ubah foto"', $header);
        $this->assertStringContainsString('border border-[#D5DEE3]', $header);
        $this->assertStringContainsString('id="hdr-nama"', $header);
        $this->assertStringContainsString('text-2xl font-semibold', $header);
        $this->assertStringContainsString('id="hdr-jabatan-divisi"', $header);
        $this->assertStringContainsString('text-sm text-[#4B5563]', $header);
        $this->assertStringContainsString('<x-status-badge', $header);
        $this->assertStringContainsString('status="aktif"', $header);
        $this->assertStringContainsString('id="hdr-status-karyawan"', $header);
        $this->assertStringContainsString('h-[22px]', $header);
        $this->assertStringContainsString('rounded-md', $header);
        $this->assertStringContainsString('bg-[#F1F5F9]', $header);

        // The header must sit above the cards, not inside a card.
        $this->assertLessThan(strpos($html, 'id="view-info"'), $start);
    }

    public function test_cards_use_two_columns_until_900px_and_shared_card_tokens(): void
    {
        $html = $this->html();
        $card = $this->informasiPribadiCard();

        $this->assertStringContainsString('grid grid-cols-1 items-start gap-6 min-[900px]:grid-cols-2', $html);
        $this->assertStringContainsString('border border-[#E2E8EC]', $card);
        $this->assertStringContainsString('rounded-xl', $card);
        $this->assertStringContainsString('p-4', $card);
        $this->assertStringContainsString('px-6 pb-8 pt-6 sm:px-8', $html);
    }

    public function test_informasi_pribadi_uses_edit_button_and_label_value_fields(): void
    {
        $card = $this->informasiPribadiCard();

        $this->assertStringContainsString('>Informasi pribadi</h2>', $card);
        $this->assertStringContainsString('id="btn-edit-info"', $card);
        $this->assertStringContainsString('aria-label', $card);
        $this->assertStringContainsString('h-8', $card);
        $this->assertStringContainsString('rounded-lg', $card);
        $this->assertStringContainsString('Edit', $card);

        $fields = [
            'prof-email' => 'Email',
            'prof-telepon' => 'No. telepon',
            'prof-gender' => 'Jenis kelamin',
            'prof-tgl-lahir' => 'Tanggal lahir',
            'prof-pendidikan' => 'Pendidikan terakhir',
            'prof-alamat' => 'Alamat',
            'prof-rekening' => 'No. rekening',
        ];

        foreach ($fields as $id => $label) {
            $this->assertStringContainsString('id="'.$id.'"', $card);
            $this->assertStringContainsString('>'.$label.'</dt>', $card);
        }

        $this->assertStringContainsString('sm:grid-cols-2', $card);
        $this->assertStringContainsString('text-[12px] text-[#4B5563]', $card);
        $this->assertStringContainsString('text-[13px] font-medium text-[#4B5563]', $card);
        $this->assertStringContainsString('text-[#172B36]', $this->html());
        $this->assertStringContainsString('Belum diisi', $card);
        $this->assertStringContainsString('id="btn-toggle-rekening"', $card);
        $this->assertStringContainsString('aria-label="Tampilkan nomor rekening"', $card);
        $this->assertStringContainsString('••••', $this->html());
        $this->assertStringNotContainsString('id="prof-jobdesk"', $card);
    }

    public function test_edit_profile_action_opens_a_modal_instead_of_an_inline_card(): void
    {
        $html = $this->html();
        $start = strpos($html, 'id="edit-profile-modal"');

        $this->assertNotFalse($start);
        $this->assertStringNotContainsString('id="edit-info"', $html);

        $dialog = substr($html, $start);

        $this->assertStringContainsString('bg-[rgba(15,40,55,.45)]', $dialog);
        $this->assertStringContainsString('max-w-[520px]', $dialog);
        $this->assertStringContainsString('rounded-xl', $dialog);
        $this->assertStringContainsString('aria-label="Tutup dialog"', $dialog);
        $this->assertStringContainsString('>Edit informasi pribadi</h2>', $dialog);
        $this->assertStringContainsString('id="form-edit-profil"', $dialog);
        $this->assertStringContainsString('id="btn-cancel-edit"', $dialog);
        $this->assertStringContainsString('id="btn-submit-edit"', $dialog);
        $this->assertStringContainsString('>Batal', $dialog);
        $this->assertStringContainsString('>Simpan', $dialog);

        foreach (['input-nama' => 'Nama', 'input-email' => 'Email', 'input-telepon' => 'No. telepon', 'input-rekening' => 'No. rekening', 'input-alamat' => 'Alamat'] as $id => $label) {
            $this->assertStringContainsString('id="'.$id.'"', $dialog);
            $this->assertStringContainsString('>'.$label.'</label>', $dialog);
        }

        $this->assertStringContainsString('h-10', $dialog);
        $this->assertStringContainsString('border border-[#D5DEE3]', $dialog);
    }

    public function test_pekerjaan_is_read_only_and_akun_group_is_separated(): void
    {
        $html = $this->html();

        $this->assertStringContainsString('>Pekerjaan</h2>', $html);
        $this->assertStringContainsString('Diubah oleh HRD', $html);

        $fields = [
            'prof-id' => 'ID karyawan',
            'prof-jabatan' => 'Jabatan',
            'prof-divisi' => 'Divisi',
            'prof-job-level' => 'Job level',
            'prof-rekrut' => 'Tanggal masuk',
            'prof-akhir-kontrak' => 'Tanggal akhir kontrak',
            'prof-role' => 'Role akun',
            'prof-dibuat' => 'Akun dibuat',
        ];

        foreach ($fields as $id => $label) {
            $this->assertStringContainsString('id="'.$id.'"', $html);
            $this->assertStringContainsString('>'.$label.'</dt>', $html);
        }

        $this->assertStringContainsString('bg-[#EEF2F5]', $html);
        $this->assertStringContainsString('>Akun</h3>', $html);
        $this->assertStringContainsString('text-sm font-semibold text-[#172B36]', $html);
        $this->assertStringNotContainsString('Keterangan Karyawan', $html);
        $this->assertStringNotContainsString('Keterangan karyawan', $html);
        $this->assertStringNotContainsString('id="prof-jobdesk"', $html);
    }

    public function test_uppercase_subheadings_and_old_page_heading_are_removed(): void
    {
        $html = $this->html();

        $this->assertStringNotContainsString('Informasi Kepegawaian', $html);
        $this->assertStringNotContainsString('Informasi Pekerjaan', $html);
        $this->assertStringNotContainsString('>Informasi Pribadi<', $html);
        $this->assertStringNotContainsString('>Detail Karyawan<', $html);
        $this->assertStringNotContainsString('uppercase tracking-wide', $html);
        $this->assertStringNotContainsString('>Profil Saya<', $html);
        $this->assertStringNotContainsString('Kelola informasi pribadi dan pengaturan akun Anda.', $html);
        $this->assertStringNotContainsString('Perbarui data pribadi dan keamanan akun Anda.', $html);
    }

    public function test_security_card_is_a_slim_row_and_dialog_is_complete(): void
    {
        $html = $this->html();

        $start = strpos($html, 'Keamanan akun');
        $end = strpos($html, 'id="password-modal"');
        $this->assertNotFalse($start);
        $this->assertNotFalse($end);

        $security = substr($html, $start, $end - $start);
        $this->assertStringContainsString('id="btn-open-password"', $security);
        $this->assertStringContainsString('Ganti password secara berkala untuk menjaga akun tetap aman.', $security);
        $this->assertStringContainsString('Ubah password', $security);
        $this->assertStringContainsString('h-9', $security);
        $this->assertStringNotContainsString('id="form-password"', $security);

        $dialog = substr($html, $end);
        $this->assertStringContainsString('id="form-password"', $dialog);
        $this->assertStringContainsString('max-w-[400px]', $dialog);
        $this->assertStringContainsString('rounded-xl', $dialog);
        $this->assertStringContainsString('bg-[rgba(15,40,55,.45)]', $dialog);
        $this->assertStringContainsString('>Ubah password</h2>', $dialog);

        foreach (['current_password' => 'Password saat ini', 'new_password' => 'Password baru', 'confirm_password' => 'Ulangi password baru'] as $id => $label) {
            $this->assertStringContainsString('id="'.$id.'"', $dialog);
            $this->assertStringContainsString('>'.$label.'</label>', $dialog);
            $this->assertStringContainsString('id="'.$id.'-error"', $dialog);
            $this->assertStringContainsString('data-toggle-password="'.$id.'"', $dialog);
            $this->assertStringContainsString('aria-label="Tampilkan password"', $dialog);
        }

        $this->assertStringContainsString('autocomplete="current-password"', $dialog);
        $this->assertStringContainsString('autocomplete="new-password"', $dialog);
        $this->assertStringContainsString('h-10', $dialog);
        $this->assertStringContainsString('border border-[#D5DEE3]', $dialog);
        $this->assertStringContainsString('Minimal 8 karakter.', $dialog);
        $this->assertStringContainsString('id="btn-cancel-password"', $dialog);
        $this->assertStringContainsString('id="btn-submit-password" disabled', $dialog);
        $this->assertStringContainsString('Perbarui password', $dialog);
        $this->assertStringContainsString('text-[#B91C1C]', $dialog);
        $this->assertStringNotContainsString('value="********"', $dialog);
        $this->assertStringNotContainsString('👁', $dialog);
    }

    private function html(): string
    {
        return (string) file_get_contents(resource_path('views/profile/index.blade.php'));
    }

    private function informasiPribadiCard(): string
    {
        $html = $this->html();
        $start = strpos($html, 'id="view-info"');
        $end = strpos($html, '>Keamanan akun</h2>');

        $this->assertNotFalse($start);
        $this->assertNotFalse($end);

        return substr($html, $start, $end - $start);
    }
}
