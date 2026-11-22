<?php

namespace App\Http\Controllers;

use App\Actions\Genres\CreateGenreAction;
use App\Actions\Genres\DeleteGenreAction;
use App\Actions\Genres\UpdateGenreAction;
use App\Http\Requests\Genres\GenreRequest;
use App\Models\Genre;
use Illuminate\Support\Facades\Gate;

class GenreController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Genre::class);

        return view('genres.index');
    }

    public function create()
    {
        Gate::authorize('create', Genre::class);

        return view('genres.index', ['openCreate' => true]);
    }

    public function store(GenreRequest $request, CreateGenreAction $action)
    {
        Gate::authorize('create', Genre::class);

        $action->execute($request->validated());

        return redirect()->route('genres.index')
            ->with('success', 'Genre created successfully.');
    }

    public function show(Genre $genre)
    {
        Gate::authorize('view', $genre);

        $genre->load('books');

        return view('genres.show', ['genre' => $genre]);
    }

    public function edit(Genre $genre)
    {
        Gate::authorize('update', $genre);

        return view('genres.index', ['editGenreId' => $genre->genre_id]);
    }

    public function update(GenreRequest $request, Genre $genre, UpdateGenreAction $action)
    {
        Gate::authorize('update', $genre);

        $action->execute($genre, $request->validated());

        return redirect()->route('genres.index')
            ->with('success', 'Genre updated successfully.');
    }

    public function destroy(Genre $genre, DeleteGenreAction $action)
    {
        Gate::authorize('delete', $genre);

        $action->execute($genre);

        return redirect()->route('genres.index')
            ->with('success', 'Genre deleted successfully.');
    }
}
