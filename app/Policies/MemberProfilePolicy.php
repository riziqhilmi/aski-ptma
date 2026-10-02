<?php

namespace App\Policies;

use App\Models\MemberProfile;
use App\Models\User;

class MemberProfilePolicy
{
    public function view(User $user, MemberProfile $profile): bool
    {
        return $user->role === 'admin' || $user->id === $profile->user_id;
    }

    public function update(User $user, MemberProfile $profile): bool
    {
        return $user->role === 'member' && $user->id === $profile->user_id;
    }
}
