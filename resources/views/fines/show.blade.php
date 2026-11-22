@extends('layouts.master')

@section('content')
<div class="max-w-2xl mx-auto bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-8 shadow-sm space-y-4">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Fine Statement #FN-{{ $fine->fine_id }}</flux:heading>
        <a href="{{ route('fines.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">&larr; All Fines</a>
    </div>

    <div class="grid grid-cols-2 gap-4 text-sm pt-2">
        <div>
            <span class="text-xs text-zinc-400 block">Member</span>
            <span class="font-semibold">{{ $fine->loan?->user?->name }} ({{ $fine->loan?->user?->memberCode() }})</span>
        </div>
        <div>
            <span class="text-xs text-zinc-400 block">Associated Loan &amp; Book</span>
            <span class="font-semibold">#LN-{{ $fine->loan_id }} — {{ $fine->loan?->book?->title }}</span>
        </div>
        <div>
            <span class="text-xs text-zinc-400 block">Fine Amount</span>
            <span class="text-lg font-bold">RM {{ number_format((float) $fine->amount, 2) }}</span>
        </div>
        <div>
            <span class="text-xs text-zinc-400 block">Payment Status</span>
            <flux:badge color="amber" size="sm">{{ strtoupper($fine->status) }}</flux:badge>
        </div>
    </div>
</div>
@endsection
