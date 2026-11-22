<?php

namespace App\Actions\Books;

use App\Models\Book;

class UpdateBookAction
{
    public function execute(Book $book, array $data, bool $hasFeatured = false): Book
    {
        $data['is_featured'] = $hasFeatured;

        $book->update($data);

        return $book;
    }
}

