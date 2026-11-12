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
     * Every employee with calendar access may edit any event. The calendar is
     * shared between HR and Marketing Communication, so creators and viewers
     * have the same editing rights.
     */
    public function update(User2 $user, ContentEvent $event): bool
    {
        return ContentCalendarAccess::allows($user);
    }

    public function delete(User2 $user, ContentEvent $event): bool
    {
        return $this->update($user, $event);
    }
}
