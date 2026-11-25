<?php

namespace App\Support;

use App\Models\User2;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Central helper for cutting off an account's existing access.
 *
 * Rotating session_version invalidates web sessions via EnsureAccountSecurity;
 * deleting Sanctum tokens and session rows makes the same invalidation apply
 * to API clients, which have no session_version to compare against.
 */
final class AccountSecurity
{
    public static function invalidate(User2 $user): void
    {
        $user->forceFill(['session_version' => (string) Str::uuid()])->save();

        $user->tokens()->delete();

        try {
            DB::table('sessions')->where('user_id', $user->id_user)->delete();
        } catch (\Throwable) {
            // Session table may be absent in some test/CLI environments.
        }
    }
}
