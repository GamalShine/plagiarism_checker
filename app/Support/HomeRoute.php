<?php

namespace App\Support;

use App\Models\User;

class HomeRoute
{
    public static function for(?User $user = null): string
    {
        $user ??= auth()->user();

        if ($user?->isAdmin()) {
            return route('admin.dashboard', absolute: false);
        }

        return route('user.dashboard', absolute: false);
    }
}
