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

    public function before($user, $ability) {
        if ($user->admin) {
            return true;
        }
    }

    public function update(User $user, Tournament $tournament) {
        return $user->id === $tournament->creator;
    }

    public function store(User $user) {
        return !is_null($user);
    }

    public function list_my(User $user) {
        return !is_null($user);
    }

    public function destroy(User $user, Tournament $tournament) {
        return $user->id === $tournament->creator;
    }

    public function admin() {
        // just for admin, handled in before call
        return false;
    }

}
