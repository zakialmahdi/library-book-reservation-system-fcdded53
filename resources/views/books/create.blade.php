@extends('layouts.master')

@section('content')
<div class="max-w-2xl mx-auto bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-8 shadow-sm space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Add New Book to Catalogue</flux:heading>
        <a href="{{ route('books.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">&larr; Back</a>
    </div>

    <form method="POST" action="{{ route('books.store') }}" class="space-y-4">
        @csrf

        <flux:field>
            <flux:label>Book Title</flux:label>
            <flux:input type="text" name="title" value="{{ old('title') }}" required />
            <flux:error name="title" />
        </flux:field>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Author</flux:label>
                <flux:input type="text" name="author" value="{{ old('author') }}" required />
                <flux:error name="author" />
            </flux:field>

            <flux:field>
                <flux:label>Publisher</flux:label>
                <flux:input type="text" name="publisher" value="{{ old('publisher') }}" required />
                <flux:error name="publisher" />
            </flux:field>

            <flux:field>
                <flux:label>Genre</flux:label>
                <select name="genre_id" required class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm">
                    @foreach ($genres as $genre)
                        <option value="{{ $genre->genre_id }}" @selected(old('genre_id') == $genre->genre_id)>{{ $genre->name }}</option>
                    @endforeach
                </select>
                <flux:error name="genre_id" />
            </flux:field>

            <flux:field>
                <flux:label>Book Type</flux:label>
                <flux:input type="text" name="book_type" value="{{ old('book_type', 'Hardcover') }}" required />
                <flux:error name="book_type" />
            </flux:field>

            <flux:field>
                <flux:label>Publication Year</flux:label>
                <flux:input type="number" name="publication_year" value="{{ old('publication_year', date('Y')) }}" required />
                <flux:error name="publication_year" />
            </flux:field>

            <flux:field>
                <flux:label>Page Count</flux:label>
                <flux:input type="number" name="page_count" value="{{ old('page_count', 250) }}" required />
                <flux:error name="page_count" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>Description</flux:label>
            <flux:textarea name="description" rows="4" required>{{ old('description') }}</flux:textarea>
            <flux:error name="description" />
        </flux:field>

        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="availability" value="1" checked class="rounded border-zinc-300 text-indigo-600">
            <span>Available for Loan</span>
        </label>

        <div class="pt-3 flex justify-end gap-2">
            <flux:button type="submit" variant="primary">Save Book</flux:button>
        </div>
    </form>
</div>
@endsection
