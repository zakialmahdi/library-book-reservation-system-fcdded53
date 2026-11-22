<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Library of GMI — Library Book Reservation & Loan System' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
    @livewireStyles
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-900 dark:text-zinc-100 flex flex-col">
    <!-- Top Flux UI Header Navigation -->
    <flux:header container class="bg-white/95 dark:bg-zinc-900/95 backdrop-blur border-b border-zinc-200 dark:border-zinc-800 sticky top-0 z-40">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-bold text-lg tracking-tight text-indigo-600 dark:text-indigo-400 mr-6">
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-600 text-white shadow-sm">
                <flux:icon.book-open class="size-5" />
            </span>
            <span>Library of GMI</span>
            @auth
                @if(auth()->user()->isAdmin())
                    <flux:badge size="sm" color="amber" class="ml-1">Admin Portal</flux:badge>
                @elseif(auth()->user()->isStaff())
                    <flux:badge size="sm" color="emerald" class="ml-1">Staff Portal</flux:badge>
                @else
                    <flux:badge size="sm" color="indigo" class="ml-1">Member</flux:badge>
                @endif
            @endauth
        </a>

        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item href="{{ route('books.index') }}" :current="request()->routeIs('books.index') || request()->routeIs('books.show')">
                Catalogue
            </flux:navbar.item>
            <flux:navbar.item href="{{ route('genres.index') }}" :current="request()->routeIs('genres.*')">
                Genres
            </flux:navbar.item>

            @auth
                <flux:navbar.item href="{{ route('dashboard') }}" :current="request()->routeIs('dashboard')">
                    Dashboard
                </flux:navbar.item>
                <flux:navbar.item href="{{ route('reservations.index') }}" :current="request()->routeIs('reservations.*')">
                    Reservations
                </flux:navbar.item>
                <flux:navbar.item href="{{ route('loans.index') }}" :current="request()->routeIs('loans.*')">
                    {{ auth()->user()->isStaffOrAdmin() ? 'Circulation Loans' : 'My Loans' }}
                </flux:navbar.item>
                <flux:navbar.item href="{{ route('fines.index') }}" :current="request()->routeIs('fines.*')">
                    Fines
                </flux:navbar.item>

                @if(auth()->user()->isStaffOrAdmin())
                    <flux:navbar.item href="{{ route('admin.books') }}" :current="request()->routeIs('admin.books')">
                        Inventory CRUD
                    </flux:navbar.item>
                @endif

                @if(auth()->user()->isAdmin())
                    <flux:navbar.item href="{{ route('users.index') }}" :current="request()->routeIs('users.*')">
                        Users & Roles
                    </flux:navbar.item>
                @endif
            @endauth
        </flux:navbar>

        <flux:spacer />

        <div class="flex items-center gap-3">
            @auth
                <div class="hidden sm:flex flex-col items-end text-xs">
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ auth()->user()->name }}</span>
                    <span class="text-zinc-500">{{ auth()->user()->memberCode() }} &bull; {{ ucfirst(auth()->user()->role) }}</span>
                </div>

                <flux:button href="{{ route('users.show', auth()->user()->user_id) }}" variant="subtle" size="sm">
                    Profile
                </flux:button>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <flux:button type="submit" variant="ghost" size="sm" icon="arrow-right-start-on-rectangle">
                        Logout
                    </flux:button>
                </form>
            @else
                <flux:button href="{{ route('login') }}" variant="ghost" size="sm">
                    Log in
                </flux:button>
                <flux:button href="{{ route('register') }}" variant="primary" size="sm">
                    Register
                </flux:button>
            @endauth
        </div>
    </flux:header>

    <!-- Global Session Flash Banner with Alpine.js Auto-Dismiss & Transitions -->
    @if (session('success') || session('status') || session('error'))
        <div
            x-data="{ showBanner: true }"
            x-show="showBanner"
            x-transition.opacity.duration.300ms
            class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-4"
        >
            @if (session('error'))
                <div class="flex items-center justify-between gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/60 dark:bg-red-950/60 dark:text-red-200">
                    <div class="flex items-center gap-2">
                        <flux:icon.exclamation-triangle class="size-5 text-red-600 shrink-0" />
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" x-on:click="showBanner = false" class="text-red-600 hover:text-red-800 font-semibold text-xs">Dismiss</button>
                </div>
            @else
                <div class="flex items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/60 dark:text-emerald-200">
                    <div class="flex items-center gap-2">
                        <flux:icon.check-circle class="size-5 text-emerald-600 shrink-0" />
                        <span>{{ session('success') ?? session('status') }}</span>
                    </div>
                    <button type="button" x-on:click="showBanner = false" class="text-emerald-600 hover:text-emerald-800 font-semibold text-xs">Dismiss</button>
                </div>
            @endif
        </div>
    @endif

    <!-- Main Page Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-zinc-500">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-zinc-700 dark:text-zinc-300">Library of GMI</span>
                <span>&bull; SPG 0553 Advanced Web Programming (GMI)</span>
            </div>
            <div>
                Powered by Laravel, Livewire, Flux UI, Fortify, Alpine.js &amp; Pest PHP
            </div>
        </div>
    </footer>

    @fluxScripts
    @livewireScripts
</body>
</html>
