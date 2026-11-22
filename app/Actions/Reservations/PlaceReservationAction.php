<?php

namespace App\Actions\Reservations;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlaceReservationAction
{
    public function execute(User $user, Book $book): Reservation
    {
        return DB::transaction(function () use ($user, $book) {
            $existingReservation = Reservation::where('user_id', $user->user_id)
                ->where('book_id', $book->book_id)
                ->whereIn('status', ['pending', 'queued', 'ready'])
                ->exists();

            if ($existingReservation) {
                throw ValidationException::withMessages([
                    'book_id' => 'You already have an active reservation or queue entry for this book.',
                ]);
            }

            $existingLoan = Loan::where('user_id', $user->user_id)
                ->where('book_id', $book->book_id)
                ->whereIn('status', ['borrowed', 'overdue'])
                ->exists();

            if ($existingLoan) {
                throw ValidationException::withMessages([
                    'book_id' => 'You currently have this book checked out on loan.',
                ]);
            }

            // Stage 1 Section 2.0 & 8.2 #4:
            // If book is available, place reservation (pending/ready); if currently on loan / unavailable, join queue ('queued')
            $status = $book->availability ? 'pending' : 'queued';

            return Reservation::create([
                'user_id' => $user->user_id,
                'book_id' => $book->book_id,
                'reservation_date' => now(),
                'status' => $status,
            ]);
        });
    }
}
