<?php

namespace App\Policies;

use App\User;
use App\VideoTag;
use Illuminate\Auth\Access\HandlesAuthorization;

class VideoTagPolicy
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

    // can be deleted by admin, creator of videotag, creator of video, tournament creator, user in tag
    public function delete(User $user, VideoTag $videotag) {
        return $user->admin || $user->id == $videotag->tagged_by_user_id ||
            $user->id == $videotag->video->user_id || $user->id == $videotag->video->tournament->creator ||
            $user->id == $videotag->user_id;
    }
}
