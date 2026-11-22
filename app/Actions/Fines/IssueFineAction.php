<?php

namespace App\Actions\Fines;

use App\Models\Fine;

class IssueFineAction
{
    public function execute(array $validated): Fine
    {
        return Fine::updateOrCreate(
            ['loan_id' => $validated['loan_id']],
            [
                'amount' => $validated['amount'],
                'status' => $validated['status'] ?? 'unpaid',
            ]
        );
    }
}
