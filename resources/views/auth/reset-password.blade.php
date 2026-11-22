@extends('layouts.master')

@section('content')
<div class="max-w-md mx-auto mt-8">
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-sm p-8">
        <flux:heading size="xl">Choose a New Password</flux:heading>
        <flux:subheading class="mt-1">Complete your Fortify password reset below.</flux:subheading>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4 mt-6">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <flux:field>
                <flux:label>Email Address</flux:label>
                <flux:input type="email" name="email" value="{{ old('email', $request->email) }}" required />
                <flux:error name="email" />
            </flux:field>

            <flux:field>
                <flux:label>New Password</flux:label>
                <flux:input type="password" name="password" required />
                <flux:error name="password" />
            </flux:field>

            <flux:field>
                <flux:label>Confirm Password</flux:label>
                <flux:input type="password" name="password_confirmation" required />
            </flux:field>

            <flux:button type="submit" variant="primary" class="w-full">
                Reset Password
            </flux:button>
        </form>
    </div>
</div>
@endsection
