<?php

namespace App\Support;

use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class FilamentAccess
{
    /** @var array<int, bool> */
    private static array $adminCache = [];

    /** @var array<int, array<string, bool>> */
    private static array $permissionCache = [];

    public static function allowed(string $permission): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $userId = (int) $user->getKey();

        self::$adminCache[$userId] ??= $user->hasRole('admin');

        if (self::$adminCache[$userId]) {
            return true;
        }

        if (array_key_exists($permission, self::$permissionCache[$userId] ?? [])) {
            return self::$permissionCache[$userId][$permission];
        }

        try {
            return self::$permissionCache[$userId][$permission] = $user->can($permission);
        } catch (PermissionDoesNotExist) {
            return self::$permissionCache[$userId][$permission] = false;
        }
    }
}
