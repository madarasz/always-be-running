<?php

namespace App\Support;

class UserViewPresenter
{
    public static function displayName($user = null, $fallbackUserId = null, $fallbackImportName = null)
    {
        if ($user) {
            return $user->displayUsername();
        }

        if (!is_null($fallbackImportName) && strlen(trim($fallbackImportName)) > 0) {
            return $fallbackImportName;
        }

        if (!is_null($fallbackUserId) && strval($fallbackUserId) !== '' && intval($fallbackUserId) > 0) {
            return 'unknown user #'.$fallbackUserId;
        }

        return 'unknown user';
    }

    public static function linkClass($user = null, $default = '')
    {
        if ($user) {
            return $user->linkClass();
        }

        return $default;
    }

    public static function profileUrl($user = null)
    {
        if ($user && !is_null($user->id)) {
            return '/profile/'.$user->id;
        }

        return null;
    }
}
