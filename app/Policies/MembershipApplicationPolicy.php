<?php

namespace App\Policies;

use App\Models\MembershipApplication;
use App\Models\User;

class MembershipApplicationPolicy
{
    public function view(User $user, MembershipApplication $application): bool { return $user->role === 'admin' || $user->id === $application->user_id; }
    public function update(User $user, MembershipApplication $application): bool { return $user->role === 'admin'; }
}
