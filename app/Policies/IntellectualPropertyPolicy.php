<?php

namespace App\Policies;

use App\Models\IntellectualProperty;
use App\Models\User;

class IntellectualPropertyPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'member'], true);
    }

    public function view(User $user, IntellectualProperty $intellectualProperty): bool
    {
        return $user->role === 'admin' || $intellectualProperty->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'member';
    }

    public function update(User $user, IntellectualProperty $intellectualProperty): bool
    {
        return $user->role === 'member' && $intellectualProperty->user_id === $user->id;
    }

    public function delete(User $user, IntellectualProperty $intellectualProperty): bool
    {
        return $user->role === 'member' && $intellectualProperty->user_id === $user->id;
    }
}
