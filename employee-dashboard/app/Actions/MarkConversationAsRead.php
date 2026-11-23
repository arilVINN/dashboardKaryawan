<?php

namespace App\Actions;

use App\Models\Pesan;
use App\Models\User2;
use Illuminate\Support\Facades\Schema;

class MarkConversationAsRead
{
    /**
     * Mark the messages of a conversation that were addressed to the current
     * user as read. Access to the conversation is checked by the page/endpoint
     * that calls this action; nothing else in the data is changed.
     */
    public static function handle(User2 $user, string $idPesan): void
    {
        $pesan = Pesan::query()->with('balasanDari')->find($idPesan);

        if (! $pesan) {
            return;
        }

        if (! Schema::hasColumn('pesans', 'status')) {
            return;
        }

        $root = $pesan->balasanDari ?? $pesan;
        $ids = $root->balasan()->pluck('id_pesan')->prepend($root->id_pesan);

        Pesan::query()
            ->whereIn('id_pesan', $ids)
            ->where('penerima_id_user', $user->id_user)
            ->where('status', 'belum_dibaca')
            ->update(['status' => 'dibaca']);
    }
}
