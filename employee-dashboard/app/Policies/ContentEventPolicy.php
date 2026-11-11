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
        return ContentCalendarAccess::allows($user);
    }

    /**
     * Only the employee who created the event may edit it. Kadiv and HRD can
     * view and comment, but not change the event.
     */
    public function update(User2 $user, ContentEvent $event): bool
    {
        return ContentCalendarAccess::allows($user)
            && (string) $event->created_by === (string) $user->id_user;
    }

    public function delete(User2 $user, ContentEvent $event): bool
    {
        return $this->update($user, $event);
    }
}
