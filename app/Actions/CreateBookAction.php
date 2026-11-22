<?php

namespace App\Actions\Books;

use App\Models\Book;

class CreateBookAction
{
    public function execute(array $data, bool $hasFeatured = false): Book
    {
        $data['is_featured'] = $hasFeatured;

        return Book::create($data);
    }
}

