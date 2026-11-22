<?php

namespace App\Policies;

use App\Models\Fine;
use App\Models\User;

class FinePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Fine $fine): bool
    {
        $fine->loadMissing('loan');

        return $user->isStaffOrAdmin() || (int) ($fine->loan?->user_id) === (int) $user->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isStaffOrAdmin();
    }

    public function update(User $user, Fine $fine): bool
    {
        return $user->isStaffOrAdmin();
    }

    public function delete(User $user, Fine $fine): bool
    {
        return $user->isAdmin();
    }
}
