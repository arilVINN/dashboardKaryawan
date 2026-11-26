<?php

namespace Tests\Feature\Security;

use App\Models\DailyJournal;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\KPermintDana;
use App\Models\Pesan;
use App\Models\Role;
use App\Models\SubmitTugas;
use App\Models\SuratIzin;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regression tests for the Phase 2 hardening:
 *  - Sensitive uploads land on the private disk, never the public one.
 *  - /files/<sensitive-path> is authorized against the owning record.
 *  - Dangerous upload types are rejected.
 *  - The storage:privatize command moves legacy public files.
 */
class Phase2StorageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::insert([
            ['id_role' => 'ROLE-STAFF', 'nama_role' => 'staff'],
            ['id_role' => 'ROLE-KADIV', 'nama_role' => 'kadiv'],
            ['id_role' => 'ROLE-HRD', 'nama_role' => 'hrd'],
            ['id_role' => 'ROLE-DIR', 'nama_role' => 'direktur'],
            ['id_role' => 'ROLE-SUPER', 'nama_role' => 'superadmin'],
        ]);

        Divisi::insert([
            ['id_divisi' => 'DIV-A', 'kode_divisi' => 'A', 'nama_divisi' => 'Divisi A', 'status_aktif' => 'aktif'],
            ['id_divisi' => 'DIV-B', 'kode_divisi' => 'B', 'nama_divisi' => 'Divisi B', 'status_aktif' => 'aktif'],
            ['id_divisi' => 'DIV-FIN', 'kode_divisi' => 'FIN', 'nama_divisi' => 'Finance & Accounting', 'status_aktif' => 'aktif'],
        ]);
    }

    private function createUser(string $id, string $username, string $roleId, string $divisiId): User2
    {
        Karyawan::create([
            'id_karyawan' => 'K-'.$id,
            'nama' => $username,
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '1990-01-01',
            'tanggal_masuk' => '2024-01-01',
            'no_rekening' => 'BCA-'.$id,
            'no_telepon' => '081234567890',
            'email' => $username.'@example.test',
            'alamat' => 'Jl. Rahasia No. 1',
            'jabatan' => 'Staff',
            'divisi_id_divisi' => $divisiId,
            'status_aktif' => 'Aktif',
            'aktif' => true,
        ]);

        return User2::create([
            'id_user' => $id,
            'username' => $username,
            'password' => Hash::make('pass123'),
            'role_id_role' => $roleId,
            'karyawan_id_karyawan' => 'K-'.$id,
        ]);
    }

    private function createTask(string $id, string $employeeId): Tugas
    {
        return Tugas::create([
            'id_tugas' => $id,
            'karyawan_id_karyawan' => $employeeId,
            'judul_tugas' => 'Tugas '.$id,
            'deskripsi' => 'Detail tugas.',
            'deadline' => now()->addDays(7)->toDateString(),
            'progress' => '0',
            'status' => Tugas::STATUS_BERJALAN,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);
    }

    private function putPrivate(string $path, string $contents = 'sensitive content'): void
    {
        Storage::disk('private')->put($path, $contents);
    }

    public function test_finance_proof_requires_authentication_and_finance_access(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $owner = $this->createUser('U-OWNER', 'staff.a', 'ROLE-STAFF', 'DIV-A');
        $outsider = $this->createUser('U-OUT', 'staff.b', 'ROLE-STAFF', 'DIV-B');
        $finance = $this->createUser('U-FIN', 'finance', 'ROLE-KADIV', 'DIV-FIN');
        $director = $this->createUser('U-DIR', 'direktur', 'ROLE-DIR', 'DIV-A');

        KPermintDana::create([
            'id_kpd' => 'KPD001',
            'nomor_pengajuan' => 'REQ001',
            'judul_pengajuan' => 'Perjalanan dinas',
            'keperluan' => 'Kunjungan klien.',
            'nominal_diajukan' => 1000000,
            'lampiran_proposal' => 'keuangan/proposal/secret.pdf',
            'status' => KPermintDana::STATUS_MENUNGGU,
            'divisi_id_divisi' => 'DIV-A',
            'user_id_user' => $owner->id_user,
        ]);

        $this->putPrivate('keuangan/proposal/secret.pdf');

        $this->get('/files/keuangan/proposal/secret.pdf')->assertRedirect('/login');

        $this->actingAs($outsider)
            ->get('/files/keuangan/proposal/secret.pdf')
            ->assertForbidden();

        $this->actingAs($owner)
            ->get('/files/keuangan/proposal/secret.pdf')
            ->assertOk()
            ->assertDownload('secret.pdf');

        $this->actingAs($finance)
            ->get('/files/keuangan/proposal/secret.pdf')
            ->assertOk();

        $this->actingAs($director)
            ->get('/files/keuangan/proposal/secret.pdf')
            ->assertOk();
    }

    public function test_message_attachment_is_limited_to_participants_hrd_and_executives(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $sender = $this->createUser('U-SEND', 'staff.a', 'ROLE-STAFF', 'DIV-A');
        $recipient = $this->createUser('U-RECV', 'kadiv.a', 'ROLE-KADIV', 'DIV-A');
        $outsider = $this->createUser('U-OUT', 'staff.b', 'ROLE-STAFF', 'DIV-B');
        $hrd = $this->createUser('U-HRD', 'hrd', 'ROLE-HRD', 'DIV-A');
        $director = $this->createUser('U-DIR', 'direktur', 'ROLE-DIR', 'DIV-A');

        Pesan::create([
            'id_pesan' => 'PSN-001',
            'judul_pesan' => 'Lampiran privat',
            'deskripsi' => 'Mohon dicek.',
            'tipe' => 'pesan',
            'file_lampiran' => 'pesan-lampiran/secret.pdf',
            'tanggal_pesan' => now()->toDateString(),
            'pengirim_id_user' => $sender->id_user,
            'penerima_id_user' => $recipient->id_user,
        ]);

        $this->putPrivate('pesan-lampiran/secret.pdf');

        $this->actingAs($outsider)
            ->get('/files/pesan-lampiran/secret.pdf')
            ->assertForbidden();

        $this->actingAs($sender)
            ->get('/files/pesan-lampiran/secret.pdf')
            ->assertOk();

        $this->actingAs($recipient)
            ->get('/files/pesan-lampiran/secret.pdf')
            ->assertOk();

        $this->actingAs($hrd)
            ->get('/files/pesan-lampiran/secret.pdf')
            ->assertOk();

        $this->actingAs($director)
            ->get('/files/pesan-lampiran/secret.pdf')
            ->assertOk();
    }

    public function test_task_files_are_scoped_to_the_assignee_and_their_division(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $assignee = $this->createUser('U-STA', 'staff.a', 'ROLE-STAFF', 'DIV-A');
        $divisionKadiv = $this->createUser('U-KAD', 'kadiv.a', 'ROLE-KADIV', 'DIV-A');
        $otherKadiv = $this->createUser('U-KADB', 'kadiv.b', 'ROLE-KADIV', 'DIV-B');
        $outsider = $this->createUser('U-OUT', 'staff.b', 'ROLE-STAFF', 'DIV-B');

        $task = $this->createTask('TG001', $assignee->karyawan_id_karyawan);
        $task->update(['file_pendukung' => 'tugas_pendukung/brief.pdf']);

        SubmitTugas::create([
            'id_submit_tugas' => 'SB001',
            'tugas_id_tugas' => $task->id_tugas,
            'tugas_karyawan_id_karyawan' => $assignee->karyawan_id_karyawan,
            'file_hasil' => 'submissions/result.pdf',
            'status_review' => 'submitted',
        ]);

        $this->putPrivate('tugas_pendukung/brief.pdf');
        $this->putPrivate('submissions/result.pdf');

        $this->actingAs($outsider)
            ->get('/files/tugas_pendukung/brief.pdf')
            ->assertForbidden();
        $this->actingAs($outsider)
            ->get('/files/submissions/result.pdf')
            ->assertForbidden();
        $this->actingAs($otherKadiv)
            ->get('/files/tugas_pendukung/brief.pdf')
            ->assertForbidden();

        $this->actingAs($assignee)
            ->get('/files/tugas_pendukung/brief.pdf')
            ->assertOk();
        $this->actingAs($assignee)
            ->get('/files/submissions/result.pdf')
            ->assertOk();

        $this->actingAs($divisionKadiv)
            ->get('/files/tugas_pendukung/brief.pdf')
            ->assertOk();
    }

    public function test_journal_and_leave_letter_files_require_the_right_viewer(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $owner = $this->createUser('U-OWNER', 'staff.a', 'ROLE-STAFF', 'DIV-A');
        $outsider = $this->createUser('U-OUT', 'staff.b', 'ROLE-STAFF', 'DIV-B');

        DailyJournal::create([
            'user_id_user' => $owner->id_user,
            'karyawan_id_karyawan' => $owner->karyawan_id_karyawan,
            'tanggal' => now()->toDateString(),
            'aktivitas' => 'Aktivitas.',
            'prioritas' => 'sedang',
            'status' => 'selesai',
            'file_bukti' => 'daily-journals/evidence.pdf',
        ]);

        SuratIzin::create([
            'id_surat' => 'SRT001',
            'karyawan_id_karyawan' => $owner->karyawan_id_karyawan,
            'jenis_izin' => 'Sakit',
            'judul_surat' => 'Surat sakit',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'file' => 'surat_izin/surat.pdf',
            'status' => 'Pending',
        ]);

        $this->putPrivate('daily-journals/evidence.pdf');
        $this->putPrivate('surat_izin/surat.pdf');

        $this->actingAs($outsider)
            ->get('/files/daily-journals/evidence.pdf')
            ->assertForbidden();
        $this->actingAs($outsider)
            ->get('/files/surat_izin/surat.pdf')
            ->assertForbidden();

        $this->actingAs($owner)
            ->get('/files/daily-journals/evidence.pdf')
            ->assertOk();
        $this->actingAs($owner)
            ->get('/files/surat_izin/surat.pdf')
            ->assertOk();
    }

    public function test_task_submission_is_written_to_private_disk(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $staff = $this->createUser('U-STA', 'staff.a', 'ROLE-STAFF', 'DIV-A');
        $task = $this->createTask('TG001', $staff->karyawan_id_karyawan);

        $this->actingAs($staff, 'sanctum')
            ->postJson('/api/staff/tugas/'.$task->id_tugas.'/submit', [
                'file_hasil' => UploadedFile::fake()->create('result.pdf', 10, 'application/pdf'),
                'catatan_karyawan' => 'Selesai.',
                'progress' => 100,
            ])
            ->assertOk();

        $submission = SubmitTugas::where('tugas_id_tugas', $task->id_tugas)->firstOrFail();

        $this->assertNotSame('-', $submission->file_hasil);
        Storage::disk('private')->assertExists($submission->file_hasil);
        Storage::disk('public')->assertMissing($submission->file_hasil);
    }

    public function test_leave_letter_rejects_executable_upload(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $staff = $this->createUser('U-STA', 'staff.a', 'ROLE-STAFF', 'DIV-A');

        $this->actingAs($staff)
            ->post('/surat-izin', [
                'jenis_izin' => 'Sakit',
                'judul_surat' => 'Surat sakit',
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => now()->addDay()->toDateString(),
                'file' => UploadedFile::fake()->create('shell.php', 1, 'application/x-php'),
            ])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('surat_izin', 0);
    }

    public function test_privatize_command_moves_legacy_public_files(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        Storage::disk('public')->put('keuangan/proposal/legacy.pdf', 'legacy');
        Storage::disk('public')->put('pesan-lampiran/legacy.pdf', 'legacy');
        Storage::disk('public')->put('carousels/public.png', 'public-by-design');

        $this->artisan('storage:privatize')->assertExitCode(0);

        Storage::disk('private')->assertExists('keuangan/proposal/legacy.pdf');
        Storage::disk('private')->assertExists('pesan-lampiran/legacy.pdf');
        Storage::disk('public')->assertMissing('keuangan/proposal/legacy.pdf');
        Storage::disk('public')->assertMissing('pesan-lampiran/legacy.pdf');
        // Public-by-design assets must not be touched.
        Storage::disk('public')->assertExists('carousels/public.png');
    }
}
