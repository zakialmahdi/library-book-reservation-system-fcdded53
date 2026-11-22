<?php

namespace App\Actions\Loans;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IssueLoanAction
{
    public function execute(array $validated): Loan
    {
        return DB::transaction(function () use ($validated) {
            /** @var Book $book */
            $book = Book::lockForUpdate()->findOrFail($validated['book_id']);

            $activeLoanExists = Loan::where('book_id', $book->book_id)
                ->whereIn('status', ['borrowed', 'overdue'])
                ->exists();

            if (! $book->availability || $activeLoanExists) {
                throw ValidationException::withMessages([
                    'book_id' => 'This book is currently checked out and unavailable for loan.',
                ]);
            }

            $loan = Loan::create([
                'user_id' => $validated['user_id'],
                'book_id' => $book->book_id,
                'borrowed_date' => $validated['borrowed_date'] ?? now()->toDateString(),
                'due_date' => $validated['due_date'] ?? now()->addDays(14)->toDateString(),
                'returned_date' => null,
                'status' => $validated['status'] ?? 'borrowed',
            ]);

            $book->update(['availability' => false]);

            // Fulfill any pending/queued/ready reservation by this member for this book
            Reservation::where('user_id', $validated['user_id'])
                ->where('book_id', $book->book_id)
                ->whereIn('status', ['pending', 'queued', 'ready'])
                ->update(['status' => 'fulfilled']);

            return $loan;
        });
    }
}
