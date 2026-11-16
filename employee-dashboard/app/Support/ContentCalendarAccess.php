<?php

namespace App\Support;

use App\Models\Divisi;
use App\Models\User2;

class ContentCalendarAccess
{
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

        $name = Divisi::query()->whereKey($divisionId)->value('nama_divisi');

        if ($name === null) {
            return self::$cache[$divisionId] = false;
        }

        $configuredNames = array_map(
            fn (string $value) => mb_strtolower(trim($value)),
            config('content_calendar.divisions.names', [])
        );

        return self::$cache[$divisionId] = in_array(mb_strtolower(trim($name)), $configuredNames, true);
    }

    /**
     * Clear the per-request cache (used by tests after changing fixtures).
     */
    public static function flush(): void
    {
        self::$cache = [];
    }
}
