<?php

namespace App\Policies;

use App\Models\PlagiarismCheck;
use App\Models\User;

class PlagiarismCheckPolicy
{
    public function view(User $user, PlagiarismCheck $check): bool
    {
        return $user->id === $check->user_id || $user->isAdmin();
    }

    public function update(User $user, PlagiarismCheck $check): bool
    {
        return $user->isAdmin();
    }
}
