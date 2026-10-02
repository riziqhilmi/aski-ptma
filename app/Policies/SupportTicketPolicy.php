<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function view(User $user, SupportTicket $supportTicket): bool
    {
        return $user->role === 'admin' || $supportTicket->user_id === $user->id;
    }

    public function update(User $user, SupportTicket $supportTicket): bool
    {
        return $user->role === 'admin';
    }
}
