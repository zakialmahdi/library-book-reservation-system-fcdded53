<?php

namespace App\Actions\Loans;

use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class RenewLoanAction
{
    public function execute(Loan $loan, int $daysToExtend = 7): Loan
    {
        if ($loan->status !== 'borrowed') {
            throw ValidationException::withMessages([
                'loan' => 'Only active non-overdue loans can be renewed.',
            ]);
        }

        $currentDueDate = Carbon::parse($loan->due_date)->startOfDay();
        if ($currentDueDate->lt(Carbon::today())) {
            throw ValidationException::withMessages([
                'loan' => 'Overdue loans cannot be self-renewed. Please return the book or contact library staff.',
            ]);
        }

        $loan->update([
            'due_date' => $currentDueDate->addDays($daysToExtend)->toDateString(),
        ]);

        return $loan->refresh();
    }
}
