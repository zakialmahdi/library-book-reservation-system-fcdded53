@extends('layouts.master')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('books.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">&larr; Back to Catalogue</a>
        @auth
            @if(auth()->user()->isStaffOrAdmin())
                <div class="flex items-center gap-2">
                    <flux:button href="{{ route('books.edit', $book->book_id) }}" variant="subtle" size="sm">Edit Book</flux:button>
                    <flux:button href="{{ route('admin.books') }}" variant="ghost" size="sm">Inventory Portal</flux:button>
                </div>
            @endif
        @endauth
    </div>

    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-8 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="font-mono text-xs font-semibold bg-zinc-100 dark:bg-zinc-700 px-2.5 py-1 rounded-md">{{ $book->bookCode() }}</span>
                    <flux:badge color="indigo" size="sm">{{ $book->genre?->name ?? 'General' }}</flux:badge>
                    <flux:badge color="zinc" size="sm">{{ $book->book_type }}</flux:badge>
                </div>
                <flux:heading size="xl">{{ $book->title }}</flux:heading>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                    By <strong>{{ $book->author }}</strong> &bull; Published by {{ $book->publisher }} ({{ $book->publication_year }}) &bull; {{ $book->page_count }} pages
                </p>
            </div>

            <div>
                @if ($book->availability)
                    <flux:badge color="emerald">Available Now</flux:badge>
                @else
                    <flux:badge color="amber">Currently Checked Out</flux:badge>
                @endif
            </div>
        </div>

        <div class="border-t border-zinc-100 dark:border-zinc-700 pt-4">
            <h4 class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-2">Book Description</h4>
            <p class="text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed">{{ $book->description }}</p>
        </div>

        <div class="border-t border-zinc-100 dark:border-zinc-700 pt-5 flex flex-wrap items-center justify-between gap-4">
            <div class="text-xs text-zinc-500">
                Active Reservation Queue: <strong>{{ $book->reservations->whereIn('status', ['pending', 'queued', 'ready'])->count() }}</strong>
            </div>

            @auth
                <form method="POST" action="{{ route('reservations.store') }}">
                    @csrf
                    <input type="hidden" name="book_id" value="{{ $book->book_id }}">
                    <flux:button type="submit" variant="primary" size="sm">
                        {{ $book->availability ? 'Reserve This Book' : 'Join Reservation Queue' }}
                    </flux:button>
                </form>
            @else
                <flux:button href="{{ route('login') }}" variant="primary" size="sm">
                    Sign In to Reserve
                </flux:button>
            @endauth
        </div>
    </div>
</div>
@endsection
