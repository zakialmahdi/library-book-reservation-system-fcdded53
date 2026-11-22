<?php

namespace App\Actions\Fines;

use App\Models\Fine;

class DeleteFineAction
{
    public function execute(Fine $fine): bool
    {
        return (bool) $fine->delete();
    }
}
