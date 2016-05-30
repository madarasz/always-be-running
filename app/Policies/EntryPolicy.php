<?php

namespace App\Policies;

use App\Entry;
use App\User;
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

    public function unclaim(User $user, Entry $entry) {
        $tournament = $entry->tournament();
        return $user->id === $entry->user || $user->id === $tournament->creator;
    }
}
