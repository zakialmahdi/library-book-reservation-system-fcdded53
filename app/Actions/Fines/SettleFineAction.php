<?php

namespace App\Actions\Fines;

use App\Models\Fine;
use Illuminate\Validation\ValidationException;

class SettleFineAction
{
    public function execute(Fine $fine, string $status = 'paid'): Fine
    {
        $normalized = strtolower(trim($status));
        if (! in_array($normalized, ['paid', 'waived', 'unpaid'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Invalid fine payment status.',
            ]);
        }

        $fine->update([
            'status' => $normalized,
        ]);

        return $fine->refresh();
    }
}
