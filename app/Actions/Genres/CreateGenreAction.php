<?php

namespace App\Actions\Genres;

use App\Models\Genre;

class CreateGenreAction
{
    public function execute(array $validated): Genre
    {
        return Genre::create([
            'name' => $validated['name'],
        ]);
    }
}
