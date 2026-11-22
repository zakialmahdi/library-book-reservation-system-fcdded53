<?php

namespace App\Actions\Books;

use App\Models\Book;

class ToggleBookAvailabilityAction
{
    public function execute(Book $book): Book
    {
        $book->update([
            'availability' => ! $book->availability,
        ]);

        return $book->refresh();
    }
}
