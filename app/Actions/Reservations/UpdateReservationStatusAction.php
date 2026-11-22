<?php

namespace App\Actions\Reservations;

use App\Models\Reservation;

class UpdateReservationStatusAction
{
    public function execute(Reservation $reservation, string $status): Reservation
    {
        $reservation->update([
            'status' => strtolower(trim($status)),
        ]);

        return $reservation->refresh();
    }
}
