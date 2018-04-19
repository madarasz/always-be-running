<?php

namespace App\Policies;

use App\TournamentGroup;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TournamentGroupPolicy
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

    public function own(User $user, TournamentGroup $group) {
        return $user->admin || $user->id == $group->creator;
    }

    public function admin(User $user) {
        return $user->admin;
    }

}
