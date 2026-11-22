@extends('layouts.master')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Genre: {{ $genre->name }}</flux:heading>
            <flux:subheading>{{ $genre->books->count() }} titles catalogued in this genre</flux:subheading>
        </div>
        <a href="{{ route('genres.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">&larr; All Genres</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($genre->books as $book)
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-mono text-xs text-zinc-500">{{ $book->bookCode() }}</span>
                        <flux:badge :color="$book->availability ? 'emerald' : 'amber'" size="sm">
                            {{ $book->availability ? 'Available' : 'On Loan' }}
                        </flux:badge>
                    </div>
                    <a href="{{ route('books.show', $book->book_id) }}" class="font-bold text-base hover:text-indigo-600">
                        {{ $book->title }}
                    </a>
                    <p class="text-xs text-zinc-500 mt-1">By {{ $book->author }} ({{ $book->publication_year }})</p>
                </div>
            </div>
        @empty
            <p class="text-xs text-zinc-500">No books found in this genre.</p>
        @endforelse
    </div>
</div>
@endsection
