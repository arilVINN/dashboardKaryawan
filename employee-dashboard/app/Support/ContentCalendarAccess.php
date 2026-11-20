<?php

namespace App\Support;

use App\Models\Divisi;
use App\Models\User2;

class ContentCalendarAccess
{
    /** Divisi dengan aturan CRUD khusus (kode / nama). */
    private const RESTRICTED_KODE = 'PB';
    private const RESTRICTED_NAMA = 'publikasi buku';

    /** @var array<string, bool> */
    private static array $cache = [];

    /**
     * The content calendar is limited to the configured divisions.
     */
    public static function allows(?User2 $user): bool
    {
        if (! $user) {
            return false;
        }

        // Direktur/Direksi has company-wide access, including the shared
        // content calendar.
        if ($user->isDirektur()) {
            return true;
        }

        $divisionId = $user->karyawan?->divisi_id_divisi;

        if (! $divisionId) {
            return false;
        }

        $divisionId = (string) $divisionId;

        if (array_key_exists($divisionId, self::$cache)) {
            return self::$cache[$divisionId];
        }

        $configuredIds = array_map('strval', config('content_calendar.divisions.ids', []));

        if (in_array($divisionId, $configuredIds, true)) {
            return self::$cache[$divisionId] = true;
        }

        $division = Divisi::query()->find($divisionId, ['nama_divisi', 'kode_divisi']);

        if ($division === null) {
            return self::$cache[$divisionId] = false;
        }

        $configuredCodes = array_map(
            fn (string $value) => strtoupper(trim($value)),
            config('content_calendar.divisions.codes', [])
        );

        if (in_array(strtoupper(trim((string) $division->kode_divisi)), $configuredCodes, true)) {
            return self::$cache[$divisionId] = true;
        }

        $configuredNames = array_map(
            fn (string $value) => mb_strtolower(trim($value)),
            config('content_calendar.divisions.names', [])
        );

        return self::$cache[$divisionId] = in_array(mb_strtolower(trim((string) $division->nama_divisi)), $configuredNames, true);
    }

    /**
     * Whether the user may create/edit/delete content-calendar events.
     *
     * In Publikasi Buku only the division head (kadiv) and Markom may manage
     * events; other employees may view the calendar and add comments.
     * Other divisions (including HR) keep full access as before.
     */
    public static function canManage(?User2 $user): bool
    {
        if (! self::allows($user)) {
            return false;
        }

        if ($user->isDirektur()) {
            return true;
        }

        $division = $user->karyawan?->divisi;

        if ($division === null) {
            return false;
        }

        if (self::isRestrictedDivision($division)) {
            return (bool) $user->karyawan?->isKadiv()
                || (bool) $user->karyawan?->isMarkom();
        }

        return true;
    }

    /**
     * Only HR and Markom may add new jenis konten / konten pillar options.
     * Everyone who can open the calendar can still use the existing options.
     */
    public static function canManageOptions(?User2 $user): bool
    {
        if (! self::allows($user)) {
            return false;
        }

        if ($user->isDirektur()) {
            return true;
        }

        $karyawan = $user->karyawan;

        if ($karyawan === null) {
            return false;
        }

        $code = strtoupper(trim((string) $karyawan->divisi?->kode_divisi));

        return $code === 'HR'
            || $code === 'MARKOM'
            || $karyawan->isMarkom();
    }

    private static function isRestrictedDivision(Divisi $division): bool
    {
        return strtoupper(trim((string) $division->kode_divisi)) === self::RESTRICTED_KODE
            || mb_strtolower(trim((string) $division->nama_divisi)) === self::RESTRICTED_NAMA;
    }

    /**
     * Clear the per-request cache (used by tests after changing fixtures).
     */
    public static function flush(): void
    {
        self::$cache = [];
    }
}
