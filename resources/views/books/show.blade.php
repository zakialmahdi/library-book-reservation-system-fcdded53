@extends('layouts.master')

@section('title', $book->title . ' - Tech Bookstore')

@section('content')
    <a href="{{ route('books.index') }}" class="text-sm text-gray-500 hover:underline">← Back to Catalog</a>

    <div class="mt-4">
        <span class="text-xs font-semibold bg-indigo-100 text-indigo-800 px-2.5 py-1 rounded">
            Category: {{ $book->category->name }}
        </span>

        <h2 class="text-3xl font-bold text-gray-800 mt-2">{{ $book->title }}</h2>
        <p class="text-lg text-gray-600 mt-1">Written by {{ $book->author }}</p>

        <div class="mt-4 inline-block bg-green-100 text-green-800 font-bold px-3 py-1 rounded">
            Price: ${{ number_format($book->price, 2) }}
        </div>

        @if ($book->is_featured)
            <span class="ml-2 inline-block bg-purple-100 text-purple-800 font-semibold px-3 py-1 rounded">
                ⭐ Featured Item
            </span>
        @endif
    </div>
@endsection
