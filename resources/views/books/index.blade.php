@extends('layouts.master')

@section('title', 'All Books - Tech Bookstore')

@section('content')
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Book Catalog</h2>
        <a href="{{ route('books.create') }}" class="text-sm font-medium bg-indigo-600 text-white px-3 py-1.5 rounded hover:bg-indigo-700">
            + Add New Book
        </a>
    </div>

    <table class="w-full text-left border-collapse border border-gray-200">
        <thead>
            <tr class="bg-gray-200 text-gray-700">
                <th class="p-2 border">#</th>
                <th class="p-2 border">Title</th>
                <th class="p-2 border">Category</th>
                <th class="p-2 border">Author</th>
                <th class="p-2 border">Price</th>
                <th class="p-2 border">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($books as $book)
                <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                    <td class="p-2 border text-sm">{{ $loop->iteration }}</td>
                    <td class="p-2 border font-medium">{{ $book->title }}</td>
                    <td class="p-2 border">
                        <span class="text-xs bg-gray-200 text-gray-800 px-2 py-1 rounded">
                            {{ $book->category->name }}
                        </span>
                    </td>
                    <td class="p-2 border text-sm text-gray-600">{{ $book->author }}</td>
                    <td class="p-2 border font-semibold">${{ number_format($book->price, 2) }}</td>
                    <td class="p-2 border space-x-2">
                        <!-- SHOW -->
                        <a href="{{ route('books.show', $book->id) }}" class="text-blue-600 hover:underline text-sm">
                            View
                        </a>

                        <!-- EDIT -->
                        <a href="{{ route('books.edit', $book->id) }}" class="text-amber-600 hover:underline text-sm">
                            Edit
                        </a>

                        <!-- DESTROY -->
                        <form action="{{ route('books.destroy', $book->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this book?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-600 hover:underline text-sm border-none bg-transparent cursor-pointer p-0">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-4 text-center text-gray-500">No books found in database.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection


