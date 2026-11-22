<?php

namespace App\Actions\Books;

use App\Models\Book;

class CreateBookAction
{
    public function execute(array $validated, ?bool $availability = null): Book
    {
        if ($availability !== null) {
            $validated['availability'] = $availability;
        } elseif (! array_key_exists('availability', $validated)) {
            $validated['availability'] = true;
        }

        return Book::create($validated);
    }
}
