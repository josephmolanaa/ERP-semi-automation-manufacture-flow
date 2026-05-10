<?php

namespace App\Support;

class FilamentAccess
{
    public static function allowed(string $permission): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasRole('admin') || $user->can($permission);
    }
}
