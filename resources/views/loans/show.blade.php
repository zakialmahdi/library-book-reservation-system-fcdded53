@extends('layouts.master')

@section('content')
<div class="max-w-2xl mx-auto bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-8 shadow-sm space-y-4">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Loan Record #LN-{{ $loan->loan_id }}</flux:heading>
        <a href="{{ route('loans.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">&larr; All Loans</a>
    </div>

    <div class="grid grid-cols-2 gap-4 text-sm pt-2">
        <div>
            <span class="text-xs text-zinc-400 block">Borrower</span>
            <span class="font-semibold">{{ $loan->user?->name }} ({{ $loan->user?->memberCode() }})</span>
        </div>
        <div>
            <span class="text-xs text-zinc-400 block">Book</span>
            <span class="font-semibold">{{ $loan->book?->title }} ({{ $loan->book?->bookCode() }})</span>
        </div>
        <div>
            <span class="text-xs text-zinc-400 block">Borrowed Date</span>
            <span>{{ $loan->borrowed_date?->format('d M Y') }}</span>
        </div>
        <div>
            <span class="text-xs text-zinc-400 block">Due Date</span>
            <span>{{ $loan->due_date?->format('d M Y') }}</span>
        </div>
        <div>
            <span class="text-xs text-zinc-400 block">Status</span>
            <flux:badge color="indigo" size="sm">{{ strtoupper($loan->status) }}</flux:badge>
        </div>
        <div>
            <span class="text-xs text-zinc-400 block">Associated Fine</span>
            <span>{{ $loan->fine ? 'RM '.number_format((float) $loan->fine->amount, 2).' ('.strtoupper($loan->fine->status).')' : 'None (RM 0.00)' }}</span>
        </div>
    </div>
</div>
@endsection
