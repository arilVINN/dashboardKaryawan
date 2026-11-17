<?php

namespace App\Policies;

use App\Models\ContentEvent;
use App\Models\User2;
use App\Support\ContentCalendarAccess;

class ContentEventPolicy
{
    public function viewAny(User2 $user): bool
    {
        return ContentCalendarAccess::allows($user);
    }

    public function view(User2 $user, ContentEvent $event): bool
    {
        return ContentCalendarAccess::allows($user);
    }

    public function create(User2 $user): bool
    {
        return ContentCalendarAccess::canManage($user);
    }

    /**
     * In Publikasi Buku only the division head and Markom may manage events;
     * other employees can view and comment. Other divisions keep full access.
     */
    public function update(User2 $user, ContentEvent $event): bool
    {
        return ContentCalendarAccess::canManage($user);
    }

    public function delete(User2 $user, ContentEvent $event): bool
    {
        return $this->update($user, $event);
    }
}
