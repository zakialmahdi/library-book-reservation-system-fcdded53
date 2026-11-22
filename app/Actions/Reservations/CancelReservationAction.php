<?php

namespace App\Actions\Reservations;

use App\Models\Reservation;

class CancelReservationAction
{
    public function execute(Reservation $reservation): Reservation
    {
        $reservation->update([
            'status' => 'cancelled',
        ]);

        return $reservation->refresh();
    }
}
