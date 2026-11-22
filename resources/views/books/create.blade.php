@extends('layouts.master')

@section('title', 'Add New Book - Tech Bookstore')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Add New Book</h2>
        <a href="{{ route('books.index') }}" class="text-sm text-gray-500 hover:underline">← Back to Catalog</a>
    </div>

    <form action="{{ route('books.store') }}" method="POST" class="space-y-4">
        @csrf

        <!-- Title Field -->
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Book Title</label>
            <input type="text"
                   id="title"
                   name="title"
                   value="{{ old('title') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 p-2 border shadow-sm text-sm @error('title') border-rose-500 @enderror">
            @error('title')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Category Dropdown -->
        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
            <select id="category_id"
                    name="category_id"
                    class="mt-1 block w-full rounded-md border-gray-300 p-2 border shadow-sm text-sm @error('category_id') border-rose-500 @enderror">
                <option value="">-- Select Category --</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Author Field -->
        <div>
            <label for="author" class="block text-sm font-medium text-gray-700">Author</label>
            <input type="text"
                   id="author"
                   name="author"
                   value="{{ old('author') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 p-2 border shadow-sm text-sm @error('author') border-rose-500 @enderror">
            @error('author')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Price Field -->
        <div>
            <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
            <input type="number"
                   step="0.01"
                   id="price"
                   name="price"
                   value="{{ old('price') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 p-2 border shadow-sm text-sm @error('price') border-rose-500 @enderror">
            @error('price')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Is Featured Checkbox -->
        <div class="flex items-center space-x-2">
            <input type="checkbox"
                   id="is_featured"
                   name="is_featured"
                   value="1"
                   @checked(old('is_featured'))
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <label for="is_featured" class="text-sm font-medium text-gray-700">Mark as Featured Book</label>
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium text-sm shadow">
                Save Book
            </button>
        </div>
    </form>
@endsection


