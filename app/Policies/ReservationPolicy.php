<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Reservation $reservation): bool
    {
        return $user->isStaffOrAdmin() || (int) $reservation->user_id === (int) $user->user_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['member', 'staff', 'admin'], true);
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return $user->isStaffOrAdmin() || (int) $reservation->user_id === (int) $user->user_id;
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $user->isStaffOrAdmin() || (int) $reservation->user_id === (int) $user->user_id;
    }
}
