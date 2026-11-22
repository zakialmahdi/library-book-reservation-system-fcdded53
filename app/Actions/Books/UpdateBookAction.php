<?php

namespace App\Actions\Books;

use App\Models\Book;

class UpdateBookAction
{
    public function execute(Book $book, array $validated, ?bool $availability = null): Book
    {
        if ($availability !== null) {
            $validated['availability'] = $availability;
        }

        $book->update($validated);

        return $book->refresh();
    }
}
