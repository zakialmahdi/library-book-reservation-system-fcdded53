<?php

namespace App\Actions\Loans;

use App\Models\Fine;
use App\Models\Loan;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReturnLoanAction
{
    public const DAILY_FINE_RATE = 1.00;

    public function execute(Loan $loan, ?string $returnDate = null, float $dailyRate = self::DAILY_FINE_RATE): Loan
    {
        return DB::transaction(function () use ($loan, $returnDate, $dailyRate) {
            $resolvedReturnDate = $returnDate ? Carbon::parse($returnDate)->startOfDay() : Carbon::today();
            $dueDate = Carbon::parse($loan->due_date)->startOfDay();

            $loan->update([
                'status' => 'returned',
                'returned_date' => $resolvedReturnDate->toDateString(),
            ]);

            if ($loan->book) {
                $loan->book->update(['availability' => true]);
            }

            // Automatically generate overdue fine if returned past due_date (Stage 1 Section 8.1 #3)
            if ($resolvedReturnDate->gt($dueDate)) {
                $overdueDays = (int) $dueDate->diffInDays($resolvedReturnDate);
                $fineAmount = round($overdueDays * $dailyRate, 2);

                Fine::updateOrCreate(
                    ['loan_id' => $loan->loan_id],
                    [
                        'amount' => $fineAmount,
                        'status' => 'unpaid',
                    ]
                );
            }

            // Promote next queued or pending reservation to 'ready' (Ready for Pickup)
            $nextReservation = Reservation::where('book_id', $loan->book_id)
                ->whereIn('status', ['queued', 'pending'])
                ->orderBy('reservation_date', 'asc')
                ->first();

            if ($nextReservation) {
                $nextReservation->update(['status' => 'ready']);
            }

            return $loan->refresh()->load(['book', 'user', 'fine']);
        });
    }
}
