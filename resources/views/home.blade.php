@extends('layouts.master')

@section('title', 'Home - Tech Bookstore')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Featured Titles</h2>

    <ul class="space-y-3">
        @forelse ($featuredBooks as $book)
            <li class="p-4 bg-indigo-50 border border-indigo-200 rounded flex justify-between items-center">
                <div>
                    <span class="text-xs font-semibold bg-indigo-200 text-indigo-800 px-2 py-0.5 rounded">
                        {{ $book->category->name }}
                    </span>
                    <h3 class="font-bold text-lg text-gray-900 mt-1">{{ $book->title }}</h3>
                    <p class="text-sm text-gray-600">By {{ $book->author }}</p>
                </div>
                <a href="{{ route('books.show', $book->id) }}" class="text-indigo-600 font-medium hover:underline">
                    View →
                </a>
            </li>
        @empty
            <p class="text-gray-500">No featured books found right now.</p>
        @endforelse
    </ul>
@endsection
