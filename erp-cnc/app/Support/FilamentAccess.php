<?php

namespace App\Support;

use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class FilamentAccess
{
    public static function allowed(string $permission): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        try {
            return $user->can($permission);
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }
}
