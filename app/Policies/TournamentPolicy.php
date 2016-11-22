<?php

namespace App\Policies;

use App\Tournament;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TournamentPolicy
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

    public function logged_in(User $user) {
        return !is_null($user);
    }

    public function own(User $user, Tournament $tournament) {
        return $user->admin || $user->id == $tournament->creator;
    }

    public function purge(User $user, Tournament $tournament) {
        return $user->id == 1276 || $user->id == $tournament->creator || ($user->admin && $tournament->incomplete);
    }

    public function admin(User $user) {
        return $user->admin;
    }

}
