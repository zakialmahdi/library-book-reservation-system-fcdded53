<?php

namespace App\Actions\Loans;

use App\Models\Loan;
use Illuminate\Support\Facades\DB;

class DeleteLoanAction
{
    public function execute(Loan $loan): bool
    {
        return DB::transaction(function () use ($loan) {
            if (in_array($loan->status, ['borrowed', 'overdue'], true) && $loan->book) {
                $loan->book->update(['availability' => true]);
            }

            return (bool) $loan->delete();
        });
    }
}
