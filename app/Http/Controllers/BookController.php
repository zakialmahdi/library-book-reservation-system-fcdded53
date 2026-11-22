<?php

namespace App\Http\Controllers;

use App\Actions\Books\CreateBookAction;
use App\Actions\Books\DeleteBookAction;
use App\Actions\Books\UpdateBookAction;
use App\Http\Requests\Books\BookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Support\Facades\Gate;

class BookController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Book::class);

        return view('books.index');
    }

    public function create()
    {
        Gate::authorize('create', Book::class);

        $genres = Genre::orderBy('name')->get();

        return view('books.create', ['genres' => $genres]);
    }

    public function store(BookRequest $request, CreateBookAction $action)
    {
        Gate::authorize('create', Book::class);

        $availability = $request->has('availability')
            ? filter_var($request->input('availability'), FILTER_VALIDATE_BOOLEAN)
            : true;

        $action->execute($request->validated(), $availability);

        return redirect()->route('books.index')
            ->with('success', 'Book added to library catalogue successfully.');
    }

    public function show(Book $book)
    {
        Gate::authorize('view', $book);

        $book->load(['genre', 'reservations.user', 'loans.user']);

        return view('books.show', ['book' => $book]);
    }

    public function edit(Book $book)
    {
        Gate::authorize('update', $book);

        $genres = Genre::orderBy('name')->get();

        return view('books.edit', ['book' => $book, 'genres' => $genres]);
    }

    public function update(BookRequest $request, Book $book, UpdateBookAction $action)
    {
        Gate::authorize('update', $book);

        $availability = $request->has('availability')
            ? filter_var($request->input('availability'), FILTER_VALIDATE_BOOLEAN)
            : $book->availability;

        $action->execute($book, $request->validated(), $availability);

        return redirect()->route('books.index')
            ->with('success', 'Book details updated successfully.');
    }

    public function destroy(Book $book, DeleteBookAction $action)
    {
        Gate::authorize('delete', $book);

        $action->execute($book);

        return redirect()->route('books.index')
            ->with('success', 'Book removed from catalogue.');
    }
}
