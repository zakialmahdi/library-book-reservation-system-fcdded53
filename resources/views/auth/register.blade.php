@extends('layouts.master')

@section('content')
<div class="max-w-md mx-auto mt-6">
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-sm p-8">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white mb-3 shadow-sm">
                <flux:icon.user-plus class="size-6" />
            </div>
            <flux:heading size="xl">Create Member Account</flux:heading>
            <flux:subheading>Join Library of GMI to reserve and borrow library books</flux:subheading>
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

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <flux:field>
                <flux:label>Full Name</flux:label>
                <flux:input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Ahmad bin Ali" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Email Address</flux:label>
                <flux:input type="email" name="email" value="{{ old('email') }}" required placeholder="ahmad@student.gmi.edu.my" />
                <flux:error name="email" />
            </flux:field>

            <flux:field>
                <flux:label>Password</flux:label>
                <flux:input type="password" name="password" required placeholder="Minimum 8 characters" />
                <flux:error name="password" />
            </flux:field>

            <flux:field>
                <flux:label>Confirm Password</flux:label>
                <flux:input type="password" name="password_confirmation" required placeholder="Re-enter password" />
            </flux:field>

            <flux:button type="submit" variant="primary" class="w-full mt-2">
                Complete Registration
            </flux:button>
        </form>

        <p class="mt-6 text-center text-xs text-zinc-500">
            Already registered?
            <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">Sign in here</a>
        </p>
    </div>
</div>
@endsection
