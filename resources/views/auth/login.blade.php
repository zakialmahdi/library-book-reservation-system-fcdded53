@extends('layouts.master')

@section('title', 'Login - Tech Bookstore')

@section('content')
    <div class="max-w-md mx-auto">
        <h2 class="text-2xl font-bold mb-6 text-center">Login to Your Account</h2>

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 p-2 border text-sm @error('email') border-rose-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password"
                       id="password"
                       name="password"
                       class="mt-1 block w-full rounded-md border-gray-300 p-2 border text-sm @error('password') border-rose-500 @enderror">
                @error('password')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 font-medium text-sm shadow">
                Login
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            Don't have an account? <a href="{{ route('register') }}" class="text-indigo-600 hover:underline">Register here</a>
        </p>
    </div>
@endsection

