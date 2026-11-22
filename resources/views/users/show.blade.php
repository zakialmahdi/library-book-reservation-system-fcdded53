@extends('layouts.master')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-8 shadow-sm space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <flux:heading size="xl">{{ $profileUser->name }}</flux:heading>
                    <flux:badge color="indigo" size="sm">{{ $profileUser->memberCode() }}</flux:badge>
                    <flux:badge color="zinc" size="sm">{{ ucfirst($profileUser->role) }}</flux:badge>
                </div>
                <flux:subheading class="mt-1">{{ $profileUser->email }}</flux:subheading>
            </div>
            <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-indigo-600 hover:underline">&larr; Back to Dashboard</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 border-t border-zinc-100 dark:border-zinc-700 pt-5 text-sm">
            <div class="rounded-xl bg-zinc-50 dark:bg-zinc-900/50 p-4">
                <span class="text-xs text-zinc-400 block">Total Loans</span>
                <span class="text-xl font-bold">{{ $profileUser->loans->count() }}</span>
            </div>
            <div class="rounded-xl bg-zinc-50 dark:bg-zinc-900/50 p-4">
                <span class="text-xs text-zinc-400 block">Total Reservations</span>
                <span class="text-xl font-bold">{{ $profileUser->reservations->count() }}</span>
            </div>
            <div class="rounded-xl bg-zinc-50 dark:bg-zinc-900/50 p-4">
                <span class="text-xs text-zinc-400 block">Unpaid Fines</span>
                <span class="text-xl font-bold">RM {{ number_format((float) $profileUser->fines->where('status', 'unpaid')->sum('amount'), 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
