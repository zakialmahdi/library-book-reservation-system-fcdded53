<?php

namespace App\Actions\Books;

use App\Models\Book;

class DeleteBookAction
{
    public function execute(Book $book): void
    {
        $book->delete();
    }
}

