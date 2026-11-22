@extends('layouts.master')

@section('content')
<div class="max-w-md mx-auto mt-6">
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-sm p-8">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white mb-3 shadow-sm">
                <flux:icon.book-open class="size-6" />
            </div>
            <flux:heading size="xl">Sign in to Library of GMI</flux:heading>
            <flux:subheading>Access library reservations, loans, and role portals</flux:subheading>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-3.5 text-xs text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <flux:field>
                <flux:label>Email Address</flux:label>
                <flux:input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="member@booknest.test" />
                <flux:error name="email" />
            </flux:field>

            <flux:field>
                <div class="flex items-center justify-between">
                    <flux:label>Password</flux:label>
                    <a href="{{ route('password.request') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">
                        Forgot password?
                    </a>
                </div>
                <flux:input type="password" name="password" required placeholder="••••••••" />
                <flux:error name="password" />
            </flux:field>

            <div class="flex items-center justify-between text-xs">
                <label class="inline-flex items-center gap-2 text-zinc-600 dark:text-zinc-400 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                    <span>Remember session</span>
                </label>
            </div>

            <flux:button type="submit" variant="primary" class="w-full">
                Sign In
            </flux:button>
        </form>

        <p class="mt-6 text-center text-xs text-zinc-500">
            Don't have a library member account?
            <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">Register now</a>
        </p>
    </div>
</div>
@endsection
