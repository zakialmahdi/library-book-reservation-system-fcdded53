<?php

namespace App\Actions\Genres;

use App\Models\Genre;

class DeleteGenreAction
{
    public function execute(Genre $genre): bool
    {
        return (bool) $genre->delete();
    }
}
