@extends('layouts.master')

@section('content')
<div class="max-w-2xl mx-auto bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-8 shadow-sm space-y-4">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Reservation #RES-{{ $reservation->reservation_id }}</flux:heading>
        <a href="{{ route('reservations.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">&larr; All Reservations</a>
    </div>

    <div class="grid grid-cols-2 gap-4 text-sm pt-2">
        <div>
            <span class="text-xs text-zinc-400 block">Member</span>
            <span class="font-semibold">{{ $reservation->user?->name }} ({{ $reservation->user?->memberCode() }})</span>
        </div>
        <div>
            <span class="text-xs text-zinc-400 block">Book</span>
            <span class="font-semibold">{{ $reservation->book?->title }} ({{ $reservation->book?->bookCode() }})</span>
        </div>
        <div>
            <span class="text-xs text-zinc-400 block">Reserved At</span>
            <span>{{ $reservation->reservation_date?->format('d M Y, h:i A') }}</span>
        </div>
        <div>
            <span class="text-xs text-zinc-400 block">Current Status</span>
            <flux:badge color="indigo" size="sm">{{ strtoupper($reservation->status) }}</flux:badge>
        </div>
    </div>
</div>
@endsection
