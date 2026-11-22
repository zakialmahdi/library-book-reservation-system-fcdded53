<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Loan $loan): bool
    {
        return $user->isStaffOrAdmin() || (int) $loan->user_id === (int) $user->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isStaffOrAdmin();
    }

    public function update(User $user, Loan $loan): bool
    {
        return $user->isStaffOrAdmin();
    }

    public function renew(User $user, Loan $loan): bool
    {
        if ($loan->status !== 'borrowed') {
            return false;
        }

        return $user->isStaffOrAdmin() || (int) $loan->user_id === (int) $user->user_id;
    }

    public function delete(User $user, Loan $loan): bool
    {
        return $user->isAdmin();
    }
}
