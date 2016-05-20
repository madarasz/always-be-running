<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class EntryPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function before($user, $ability) {
        if ($user->admin) {
            return true;
        }
    }

    public function own(User $user, Entry $entry) {
        return $user->id === $entry->user;
    }
}
