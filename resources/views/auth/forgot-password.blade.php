@extends('layouts.master')

@section('content')
<div class="max-w-md mx-auto mt-8">
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-sm p-8">
        <flux:heading size="xl">Reset Your Password</flux:heading>
        <flux:subheading class="mt-1">Enter your registered library email to receive a password reset link.</flux:subheading>

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4 mt-6">
            @csrf

            <flux:field>
                <flux:label>Email Address</flux:label>
                <flux:input type="email" name="email" value="{{ old('email') }}" required autofocus />
                <flux:error name="email" />
            </flux:field>

            <flux:button type="submit" variant="primary" class="w-full">
                Send Password Reset Link
            </flux:button>
        </form>

        <div class="mt-5 text-center">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">
                &larr; Back to login
            </a>
        </div>
    </div>
</div>
@endsection
