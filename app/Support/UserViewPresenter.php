<?php

namespace App\Support;

class UserViewPresenter
{
    public static function displayName($user = null, $fallbackUserId = null, $fallbackImportName = null)
    {
        if ($user) {
            return $user->displayUsername();
        }

        $normalizedFallbackImportName = is_string($fallbackImportName)
            ? (function_exists('mb_trim') ? mb_trim($fallbackImportName) : trim($fallbackImportName))
            : $fallbackImportName;

        if (!is_null($normalizedFallbackImportName) && strlen((string) $normalizedFallbackImportName) > 0) {
            return $normalizedFallbackImportName;
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
