@extends('layouts.master')

@section('content')
<div class="space-y-10">
    <!-- Hero Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900 via-indigo-800 to-slate-900 text-white p-8 sm:p-12 shadow-lg">
        <div class="max-w-3xl space-y-4">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1 text-xs font-semibold text-indigo-200">
                <span>Library of GMI</span>
            </div>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                Digital Library Book Reservation &amp; Circulation System
            </h1>
            <p class="text-sm sm:text-base text-indigo-100/90 leading-relaxed max-w-2xl">
                Search real-time book availability, reserve titles online, join automated priority queues for borrowed items, and manage library circulation seamlessly.
            </p>
            <div class="pt-3 flex flex-wrap items-center gap-3">
                <a href="{{ route('books.index') }}" class="px-5 py-2.5 rounded-xl bg-white text-indigo-900 font-bold text-sm shadow-sm hover:bg-indigo-50 transition">
                    Explore Book Catalogue
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-semibold text-sm transition">
                        Go to My Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-semibold text-sm transition">
                        Member / Staff Sign In
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Embedded Livewire Reactive Book Catalogue -->
    <livewire:books.book-catalog />
</div>
@endsection
