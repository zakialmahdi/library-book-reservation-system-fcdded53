<?php

namespace App\Actions\Genres;

use App\Models\Genre;

class UpdateGenreAction
{
    public function execute(Genre $genre, array $validated): Genre
    {
        $genre->update([
            'name' => $validated['name'],
        ]);

        return $genre->refresh();
    }
}
