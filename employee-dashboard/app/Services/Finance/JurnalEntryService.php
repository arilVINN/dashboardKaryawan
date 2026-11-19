<?php

namespace App\Services\Finance;

use App\Models\AuditLog;
use App\Models\CoaAkun;
use App\Models\JurnalEntry;
use App\Models\JurnalLine;
use App\Models\KPemasukan;
use App\Models\KPengeluaran;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Satu-satunya tempat pembuatan jurnal umum.
 *
 * Semua baris disimpan dalam rupiah bulat (integer), satu sisi (debit ATAU
 * kredit) per baris, dan total debit harus sama dengan total kredit.
 * Pemanggil bertugas membungkus pemanggilan di dalam DB::transaction() yang
 * sama dengan transaksi sumber, sehingga jurnal tidak pernah ada tanpa
 * sumbernya (dan sebaliknya).
 */
class JurnalEntryService
{
    public const KODE_KAS = '1101';

    public const KODE_PENDAPATAN = '4101';

    public const KODE_PENDAPATAN_LAIN = '4102';

    public const KODE_BEBAN = '5102';

    /**
     * Jurnal dari kas masuk: Debit Kas / Kredit Pendapatan.
     */
    public function createFromPemasukan(KPemasukan $pemasukan): JurnalEntry
    {
        $existing = $this->existingForSource(KPemasukan::class, (string) $pemasukan->id_kp);
        if ($existing) {
            return $existing;
        }

        $kategori = strtolower((string) $pemasukan->kategori_pemasukan);
        $kodeKredit = str_contains($kategori, 'lain')
            ? self::KODE_PENDAPATAN_LAIN
            : self::KODE_PENDAPATAN;

        $nominal = $this->rupiah($pemasukan->nominal);

        return $this->create([
            'entry_date' => $pemasukan->tanggal_masuk->toDateString(),
            'ref_code' => 'DMIN' . str_pad($this->numericPart((string) $pemasukan->id_kp), 3, '0', STR_PAD_LEFT),
            'source_type' => KPemasukan::class,
            'source_id' => (string) $pemasukan->id_kp,
            'keterangan' => $pemasukan->keterangan ?: $pemasukan->kategori_pemasukan,
            'created_by' => $pemasukan->user_id_user,
            'lines' => [
                ['account_id' => $this->accountId(self::KODE_KAS), 'debit' => $nominal, 'credit' => 0],
                ['account_id' => $this->accountId($kodeKredit), 'debit' => 0, 'credit' => $nominal],
            ],
        ]);
    }

    /**
     * Jurnal dari kas keluar / pencairan dana: Debit Beban / Kredit Kas.
     */
    public function createFromPengeluaran(KPengeluaran $pengeluaran): JurnalEntry
    {
        $existing = $this->existingForSource(KPengeluaran::class, (string) $pengeluaran->id_kpengeluaran);
        if ($existing) {
            return $existing;
        }

        $nominal = $this->rupiah($pengeluaran->nominal);

        return $this->create([
            'entry_date' => $pengeluaran->tanggal_cair->toDateString(),
            'ref_code' => 'DMOUT' . str_pad($this->numericPart((string) $pengeluaran->id_kpengeluaran), 2, '0', STR_PAD_LEFT),
            'source_type' => KPengeluaran::class,
            'source_id' => (string) $pengeluaran->id_kpengeluaran,
            'keterangan' => $pengeluaran->keterangan ?: 'Pengeluaran kas',
            'created_by' => $pengeluaran->user_id_user,
            'lines' => [
                ['account_id' => $this->accountId(self::KODE_BEBAN), 'debit' => $nominal, 'credit' => 0],
                ['account_id' => $this->accountId(self::KODE_KAS), 'debit' => 0, 'credit' => $nominal],
            ],
        ]);
    }

    /**
     * Jurnal manual dibuat dari modal Jurnal Umum (sub-company wajib dipilih).
     */
    public function createManual(array $payload, ?string $createdBy): JurnalEntry
    {
        return $this->create(array_merge($payload, [
            'ref_code' => $payload['ref_code'] ?? $this->nextRef('JMU', 4),
            'created_by' => $createdBy,
            'audit_aksi' => 'jurnal_manual_dibuat',
        ]));
    }

    /**
     * Pembatalan tidak menghapus/mengubah jurnal lama — dibuat jurnal balik
     * dengan debit/kredit tertukar dan Ref baru.
     */
    public function reverse(JurnalEntry $entry, string $alasan, ?string $createdBy): JurnalEntry
    {
        $lines = $entry->lines->map(fn (JurnalLine $line) => [
            'account_id' => $line->account_id,
            'debit' => $line->credit,
            'credit' => $line->debit,
        ])->all();

        return $this->create([
            'entry_date' => now()->toDateString(),
            'ref_code' => $this->nextRef('RV', 4),
            'source_type' => JurnalEntry::class,
            'source_id' => (string) $entry->id,
            'jurnal_divisi_id' => $entry->jurnal_divisi_id,
            'keterangan' => 'Pembatalan jurnal ' . $entry->ref_code . '. Alasan: ' . trim($alasan),
            'created_by' => $createdBy,
            'lines' => $lines,
            'audit_aksi' => 'jurnal_umum_dibatalkan',
        ]);
    }

    /**
     * Buat entry + baris + audit log. Validasi ketat sebelum commit.
     *
     * @throws InvalidArgumentException
     */
    public function create(array $payload): JurnalEntry
    {
        $lines = $payload['lines'] ?? [];
        if (count($lines) < 2) {
            $this->fail('Jurnal minimal harus memiliki dua baris.');
        }

        $totalDebit = 0;
        $totalCredit = 0;
        $normalized = [];

        foreach ($lines as $index => $line) {
            $debit = $this->rupiah($line['debit'] ?? 0);
            $credit = $this->rupiah($line['credit'] ?? 0);

            if ($debit < 0 || $credit < 0) {
                $this->fail('Nominal debit/kredit tidak boleh negatif.');
            }
            if ($debit > 0 && $credit > 0) {
                $this->fail('Satu baris hanya boleh berisi debit atau kredit, tidak keduanya.');
            }
            if ($debit === 0 && $credit === 0) {
                $this->fail('Baris ke-' . ($index + 1) . ' belum memiliki nominal.');
            }

            $account = CoaAkun::find($line['account_id'] ?? null);
            if (! $account) {
                $this->fail('Akun pada baris ke-' . ($index + 1) . ' tidak ditemukan.');
            }
            if (! $account->aktif) {
                $this->fail('Akun ' . $account->kode . ' tidak aktif.');
            }

            $totalDebit += $debit;
            $totalCredit += $credit;
            $normalized[] = [
                'account_id' => $account->id,
                'debit' => $debit,
                'credit' => $credit,
                'sort_order' => $index,
            ];
        }

        if ($totalDebit <= 0 || $totalDebit !== $totalCredit) {
            $this->fail(
                'Total debit (' . $totalDebit . ') dan kredit (' . $totalCredit . ') harus sama dan lebih dari nol.'
            );
        }

        $entry = JurnalEntry::create([
            'entry_date' => $payload['entry_date'],
            'ref_code' => $payload['ref_code'],
            'source_type' => $payload['source_type'] ?? null,
            'source_id' => $payload['source_id'] ?? null,
            'jurnal_divisi_id' => $payload['jurnal_divisi_id'] ?? null,
            'keterangan' => $payload['keterangan'] ?? null,
            'created_by' => $payload['created_by'] ?? null,
        ]);

        foreach ($normalized as $line) {
            $entry->lines()->create($line);
        }

        AuditLog::catat(
            $payload['created_by'] ?? null,
            $payload['audit_aksi'] ?? 'jurnal_umum_dibuat',
            'jurnal_entry',
            (string) $entry->id,
            [
                'ref_code' => $entry->ref_code,
                'entry_date' => $entry->entry_date->toDateString(),
                'total_debit' => $totalDebit,
                'total_kredit' => $totalCredit,
                'source_type' => $entry->source_type,
                'source_id' => $entry->source_id,
            ]
        );

        return $entry->load('lines.account');
    }

    private function existingForSource(string $type, string $id): ?JurnalEntry
    {
        return JurnalEntry::where('source_type', $type)
            ->where('source_id', $id)
            ->first();
    }

    private function accountId(string $kode): int
    {
        $akun = CoaAkun::where('kode', $kode)->where('aktif', true)->first();

        if (! $akun) {
            $this->fail('Akun COA dengan kode ' . $kode . ' tidak ditemukan atau tidak aktif.');
        }

        return $akun->id;
    }

    private function rupiah(mixed $value): int
    {
        return (int) round((float) ($value ?? 0));
    }

    private function numericPart(string $value): int
    {
        return (int) preg_replace('/\D/', '', $value);
    }

    private function nextRef(string $prefix, int $length): string
    {
        $last = JurnalEntry::where('ref_code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('ref_code');

        $number = $last ? ((int) preg_replace('/\D/', '', $last) + 1) : 1;

        return $prefix . str_pad((string) $number, $length, '0', STR_PAD_LEFT);
    }

    private function fail(string $message): never
    {
        Log::error('Jurnal Umum: ' . $message);
        throw new InvalidArgumentException($message);
    }
}
