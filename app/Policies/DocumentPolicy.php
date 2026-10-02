<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'member'], true);
    }

    public function view(User $user, Document $document): bool
    {
        return $user->role === 'admin' || $document->status === Document::STATUS_PUBLISHED;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Document $document): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->role === 'admin';
    }
}
