<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class CalendarNote extends Model
{
    use HasUuids;

    protected $table = 'calendar_notes';

    /**
     * Nilai default pengingat (hari sebelum tanggal note) untuk note baru.
     */
    public const REMINDER_DEFAULT_DAYS = 1;

    /**
     * Batas maksimum hari pengingat yang boleh dipilih user.
     * 60 minggu x 7 hari = 420 hari.
     */
    public const REMINDER_MAX_DAYS = 420;

    protected $fillable = [
        'user_id_user',
        'tanggal',
        'judul',
        'detail',
        'warna',
        'reminder_days_before',
        'reminder_email_sent_at',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'reminder_days_before' => 'integer',
        'reminder_email_sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Tanggal atau offset pengingat berubah berarti email harus bisa
        // dikirim ulang untuk jadwal yang baru.
        static::updated(function (CalendarNote $note): void {
            if ($note->wasChanged(['tanggal', 'reminder_days_before'])) {
                $note->forceFill(['reminder_email_sent_at' => null])->saveQuietly();
            }
        });
    }

    /**
     * Warna yang tersedia untuk note.
     * Key = nilai di DB, Value = label display
     */
    public const WARNA_OPTIONS = [
        'slate'  => ['bg' => '#F1F5F9', 'border' => '#94A3B8', 'text' => '#475569', 'dot' => '#64748B'],
        'blue'   => ['bg' => '#DBEAFE', 'border' => '#3B82F6', 'text' => '#1D4ED8', 'dot' => '#3B82F6'],
        'green'  => ['bg' => '#D1FAE5', 'border' => '#10B981', 'text' => '#065F46', 'dot' => '#10B981'],
        'amber'  => ['bg' => '#FEF3C7', 'border' => '#F59E0B', 'text' => '#92400E', 'dot' => '#F59E0B'],
        'red'    => ['bg' => '#FEE2E2', 'border' => '#EF4444', 'text' => '#991B1B', 'dot' => '#EF4444'],
        'purple' => ['bg' => '#EDE9FE', 'border' => '#8B5CF6', 'text' => '#5B21B6', 'dot' => '#8B5CF6'],
    ];

    /**
     * Tanggal saat pengingat note ini seharusnya muncul.
     * Null berarti note tidak memiliki pengingat.
     */
    public function reminderDate(): ?Carbon
    {
        if ($this->reminder_days_before === null) {
            return null;
        }

        return $this->tanggal->copy()->subDays($this->reminder_days_before)->startOfDay();
    }

    /**
     * Apakah note ini harus diingatkan pada tanggal tertentu.
     */
    public function remindsOn(CarbonInterface $date): bool
    {
        return $this->reminderDate()?->isSameDay($date) ?? false;
    }

    /**
     * Label pengingat untuk ditampilkan di UI.
     */
    public function reminderLabel(): ?string
    {
        if ($this->reminder_days_before === null) {
            return null;
        }

        return match ($this->reminder_days_before) {
            0 => 'Hari H',
            1 => '1 hari sebelum',
            default => $this->reminder_days_before . ' hari sebelum',
        };
    }

    /**
     * Pesan pengingat yang siap ditampilkan pada pusat notifikasi/dashboard.
     */
    public function reminderMessage(): string
    {
        $tanggalLabel = $this->tanggal->locale('id')->isoFormat('dddd, D MMMM Y');

        $when = match ($this->reminder_days_before) {
            0 => 'hari ini',
            1 => 'besok',
            null => 'segera',
            default => $this->reminder_days_before . ' hari lagi',
        };

        return "Catatan \"{$this->judul}\" dijadwalkan {$when}, {$tanggalLabel}.";
    }

    /**
     * Ambil semua note milik user yang memang harus diingatkan pada tanggal ini.
     *
     * Query dibatasi ke rentang tanggal yang mungkin (hari ini s/d maksimum
     * hari pengingat), lalu difilter di PHP agar tetap kompatibel dengan
     * PostgreSQL (produksi) dan SQLite (testing).
     */
    public static function remindersFor(string $userId, ?CarbonInterface $date = null): Collection
    {
        $date = $date ? Carbon::parse($date) : now();

        return static::query()
            ->where('user_id_user', $userId)
            ->whereNotNull('reminder_days_before')
            ->whereDate('tanggal', '>=', $date->toDateString())
            ->whereDate('tanggal', '<=', $date->copy()->addDays(self::REMINDER_MAX_DAYS)->toDateString())
            ->orderBy('tanggal')
            ->get()
            ->filter(fn (self $note) => $note->remindsOn($date))
            ->values();
    }

    /**
     * Batasi query ke note yang tanggal pengingatnya mungkin jatuh pada
     * tanggal acuan. Filter hari yang tepat tetap dilakukan lewat remindsOn()
     * agar kompatibel dengan PostgreSQL (produksi) dan SQLite (testing).
     */
    public function scopeDueForEmailReminder(Builder $query, ?CarbonInterface $date = null): Builder
    {
        $date = $date ? Carbon::parse($date)->startOfDay() : now()->startOfDay();

        return $query
            ->whereNotNull('reminder_days_before')
            ->whereDate('tanggal', '>=', $date->toDateString())
            ->whereDate('tanggal', '<=', $date->copy()->addDays(self::REMINDER_MAX_DAYS)->toDateString());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User2::class, 'user_id_user', 'id_user');
    }
}
