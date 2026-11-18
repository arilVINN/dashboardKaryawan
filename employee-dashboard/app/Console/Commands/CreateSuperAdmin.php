<?php

namespace App\Console\Commands;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Membuat akun superadmin (akses baca-tulis penuh).
 *
 * Akun ini sengaja TIDAK bisa dibuat dari UI/API HRD. Role "superadmin"
 * dibuat otomatis oleh perintah ini bila belum ada.
 */
class CreateSuperAdmin extends Command
{
    protected $signature = 'superadmin:create
                            {username : Username login (huruf/angka saja)}
                            {--name= : Nama lengkap (default: username)}
                            {--email= : Email karyawan (opsional)}
                            {--password= : Password login (min. 6 karakter)}
                            {--division=DIRUT : Kode atau ID divisi}
                            {--force : Perbarui password bila akun superadmin sudah ada}';

    protected $description = 'Buat akun superadmin (full read-write) yang hanya dikelola lewat CLI';

    public function handle(): int
    {
        $username = trim((string) $this->argument('username'));

        if ($username === '' || preg_match('/^[A-Za-z0-9]+$/', $username) !== 1) {
            $this->error('Username wajib berupa huruf/angka tanpa spasi atau titik.');

            return self::FAILURE;
        }

        $password = (string) ($this->option('password') ?? '');

        if ($password === '' && $this->input->isInteractive()) {
            $password = (string) $this->secret('Password untuk '.$username);
        }

        if (mb_strlen($password) < 6) {
            $this->error('Password wajib diisi dan minimal 6 karakter.');

            return self::FAILURE;
        }

        $email = $this->option('email');
        $email = ($email === null || trim((string) $email) === '') ? null : trim((string) $email);

        if ($email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->error('Format email tidak valid: '.$email);

            return self::FAILURE;
        }

        $divisionInput = trim((string) ($this->option('division') ?: 'DIRUT'));
        $divisi = Divisi::query()
            ->where('kode_divisi', strtoupper($divisionInput))
            ->orWhere('id_divisi', $divisionInput)
            ->first();

        if (! $divisi) {
            $codes = Divisi::query()->orderBy('kode_divisi')->pluck('kode_divisi')->implode(', ');
            $this->error('Divisi "'.$divisionInput.'" tidak ditemukan. Kode yang tersedia: '.$codes);

            return self::FAILURE;
        }

        $existing = User2::with('role')->where('username', $username)->first();

        if ($existing) {
            if (! $existing->isSuperAdmin()) {
                $this->error('Username "'.$username.'" sudah dipakai akun non-superadmin.');

                return self::FAILURE;
            }

            if (! $this->option('force')) {
                $this->error('Akun superadmin "'.$username.'" sudah ada. Gunakan --force untuk memperbarui password.');

                return self::FAILURE;
            }

            $existing->update(['password' => Hash::make($password)]);
            $this->info('Password akun superadmin "'.$username.'" diperbarui.');

            return self::SUCCESS;
        }

        if ($email !== null && Karyawan::query()->where('email', $email)->exists()) {
            $this->error('Email "'.$email.'" sudah dipakai karyawan lain.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($username, $password, $email, $divisi): void {
            $role = $this->ensureSuperAdminRole();

            $karyawanId = $this->nextGeneratedId(Karyawan::class, 'id_karyawan', 'KR');
            $userId = $this->nextGeneratedId(User2::class, 'id_user', 'US');

            $karyawan = Karyawan::create([
                'id_karyawan' => $karyawanId,
                'nama' => $this->option('name') ?: $username,
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_masuk' => now()->toDateString(),
                'email' => $email,
                'jabatan' => 'Superadmin',
                'status_aktif' => 'Aktif',
                'divisi_id_divisi' => $divisi->id_divisi,
                'status_karyawan' => 'Tetap',
                'aktif' => true,
            ]);

            User2::create([
                'id_user' => $userId,
                'username' => $username,
                'password' => Hash::make($password),
                'role_id_role' => $role->id_role,
                'karyawan_id_karyawan' => $karyawan->id_karyawan,
            ]);

            $this->info('Akun superadmin dibuat:');
            $this->line('  username : '.$username);
            $this->line('  password : '.$password);
            $this->line('  karyawan : '.$karyawan->id_karyawan.' ('.$karyawan->nama.')');
            $this->line('  divisi   : '.$divisi->kode_divisi.' - '.$divisi->nama_divisi);
            $this->line('  role     : '.$role->nama_role.' ('.$role->id_role.')');
        });

        return self::SUCCESS;
    }

    private function ensureSuperAdminRole(): Role
    {
        $role = Role::query()->whereRaw('LOWER(nama_role) = ?', ['superadmin'])->first();

        if ($role) {
            return $role;
        }

        return Role::create([
            'id_role' => $this->nextGeneratedId(Role::class, 'id_role', 'RL'),
            'nama_role' => 'superadmin',
        ]);
    }

    /**
     * Cari ID berikutnya untuk prefix tertentu, mengabaikan ID lama yang
     * formatnya tidak standar (sama seperti HrdStaffController).
     */
    private function nextGeneratedId(string $modelClass, string $column, string $prefix, int $pad = 3): string
    {
        $max = 0;

        $modelClass::query()
            ->where($column, 'like', $prefix.'%')
            ->pluck($column)
            ->each(function ($id) use ($prefix, &$max): void {
                if (preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', (string) $id, $matches) === 1) {
                    $max = max($max, (int) $matches[1]);
                }
            });

        do {
            $max++;
            $candidate = $prefix.str_pad((string) $max, $pad, '0', STR_PAD_LEFT);
        } while ($modelClass::query()->where($column, $candidate)->exists());

        return $candidate;
    }
}
