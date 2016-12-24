<?php

namespace App\Policies;

use App\User;
use App\Video;
use Illuminate\Auth\Access\HandlesAuthorization;

class VideoPolicy
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

    public function delete(User $user, Video $video) {
        return $user->admin || $user->id == $video->user_id || $user->id == $video->tournament->creator;
    }
}
