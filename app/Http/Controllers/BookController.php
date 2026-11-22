<?php

namespace App\Http\Controllers;

use App\Actions\Books\CreateBookAction;
use App\Actions\Books\DeleteBookAction;
use App\Actions\Books\UpdateBookAction;
use App\Http\Requests\Books\BookRequest;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Facades\Gate;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('category')->get();

        return view('books.index', ['books' => $books]);
    }

    public function create()
    {
        Gate::authorize('manage-books');

        $categories = Category::all();

        return view('books.create', ['categories' => $categories]);
    }

    public function store(BookRequest $request, CreateBookAction $action)
    {
        $action->execute($request->validated(), $request->has('is_featured'));

        return redirect()->route('books.index')
            ->with('success', 'Book created successfully!');
    }

    public function show(string $id)
    {
        $book = Book::with('category')->findOrFail($id);

        return view('books.show', ['book' => $book]);
    }

    public function edit(string $id)
    {
        Gate::authorize('manage-books');

        $book = Book::findOrFail($id);
        $categories = Category::all();

        return view('books.edit', ['book' => $book, 'categories' => $categories]);
    }

    public function update(BookRequest $request, string $id, UpdateBookAction $action)
    {
        $book = Book::findOrFail($id);

        $action->execute($book, $request->validated(), $request->has('is_featured'));

        return redirect()->route('books.index')
            ->with('success', 'Book updated successfully!');
    }

    public function destroy(string $id, DeleteBookAction $action)
    {
        Gate::authorize('manage-books');

        $book = Book::findOrFail($id);
        $action->execute($book);

        return redirect()->route('books.index')
            ->with('success', 'Book deleted successfully!');
    }
}

