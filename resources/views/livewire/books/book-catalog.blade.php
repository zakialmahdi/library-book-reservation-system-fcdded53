<div
    x-data="{
        previewOpen: false,
        confirmOpen: false,
        selectedBook: { id: null, code: '', title: '', author: '', publisher: '', year: '', genre: '', type: '', pages: '', description: '', available: true, queueCount: 0 },
        openPreview(book) {
            this.selectedBook = book;
            this.previewOpen = true;
        },
        openConfirm(book) {
            this.selectedBook = book;
            this.confirmOpen = true;
        }
    }"
    class="space-y-6"
>
    <!-- Header Banner & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl">Library of GMI Catalogue</flux:heading>
            </div>
            <flux:subheading class="mt-1">
                Search and filter books by title, author, genre, or availability status in real time.
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            @auth
                @if(auth()->user()->isStaffOrAdmin())
                    <flux:button href="{{ route('admin.books') }}" variant="primary" size="sm" icon="plus">
                        Manage Inventory
                    </flux:button>
                @endif
                <flux:button href="{{ route('reservations.index') }}" variant="subtle" size="sm">
                    My Reservations
                </flux:button>
            @else
                <flux:button href="{{ route('login') }}" variant="primary" size="sm">
                    Sign in to Reserve
                </flux:button>
            @endauth
        </div>
    </div>

    <!-- Livewire Flash Feedback Alert -->
    @if (session()->has('status') || session()->has('error'))
        <div
            x-data="{ open: true }"
            x-show="open"
            x-transition
            class="rounded-xl border px-4 py-3 text-sm flex items-center justify-between {{ session()->has('error') ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}"
        >
            <span>{{ session('error') ?? session('status') }}</span>
            <button type="button" x-on:click="open = false" class="text-xs font-semibold underline ml-4">Close</button>
        </div>
    @endif

    <!-- Search & Multi-Facet Filter Bar (matches Stage 1 Wireframe 2) -->
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-5 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-5">
                <flux:field>
                    <flux:label>Search Books (Title, Author, Publisher)</flux:label>
                    <flux:input
                        type="text"
                        wire:model.live.debounce.250ms="search"
                        placeholder="Search by book title, author, or publisher..."
                        icon="magnifying-glass"
                    />
                </flux:field>
            </div>

            <div class="md:col-span-3">
                <flux:field>
                    <flux:label>Filter by Genre</flux:label>
                    <select
                        wire:model.live="genreFilter"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                    >
                        <option value="">All Genres</option>
                        @foreach ($genres as $genre)
                            <option value="{{ $genre->genre_id }}">{{ $genre->name }}</option>
                        @endforeach
                    </select>
                </flux:field>
            </div>

            <div class="md:col-span-2">
                <flux:field>
                    <flux:label>Availability</flux:label>
                    <select
                        wire:model.live="availabilityFilter"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                    >
                        <option value="">All Statuses</option>
                        <option value="available">Available Now</option>
                        <option value="borrowed">Currently On Loan</option>
                    </select>
                </flux:field>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <flux:button wire:click="resetFilters" variant="ghost" size="sm" class="w-full">
                    Reset Filters
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Catalogue Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($books as $book)
            @php
                $bookPayload = [
                    'id' => $book->book_id,
                    'code' => $book->bookCode(),
                    'title' => $book->title,
                    'author' => $book->author,
                    'publisher' => $book->publisher,
                    'year' => $book->publication_year,
                    'genre' => $book->genre?->name ?? 'Uncategorized',
                    'type' => $book->book_type,
                    'pages' => $book->page_count,
                    'description' => $book->description,
                    'available' => (bool) $book->availability,
                    'queueCount' => (int) ($book->active_queue_count ?? 0),
                ];
            @endphp
            <div
                wire:key="catalog-book-{{ $book->book_id }}"
                class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm flex flex-col justify-between hover:border-indigo-300 dark:hover:border-indigo-600 transition"
            >
                <div>
                    <!-- Top Meta Row -->
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="text-xs font-mono font-semibold text-zinc-500 bg-zinc-100 dark:bg-zinc-700 px-2.5 py-1 rounded-md">
                            {{ $book->bookCode() }}
                        </span>

                        @if ($book->availability)
                            <flux:badge color="emerald" size="sm">Stats: Available</flux:badge>
                        @else
                            <flux:badge color="amber" size="sm">On Loan (Queue Open)</flux:badge>
                        @endif
                    </div>

                    <!-- Book Title & Author -->
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 leading-snug">
                        <a href="{{ route('books.show', $book->book_id) }}" class="hover:text-indigo-600">
                            {{ $book->title }}
                        </a>
                    </h3>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400 mt-1">
                        Author: {{ $book->author }}
                    </p>

                    <div class="flex flex-wrap items-center gap-2 mt-3">
                        <flux:badge color="indigo" size="sm">{{ $book->genre?->name ?? 'General' }}</flux:badge>
                        <flux:badge color="zinc" size="sm">{{ $book->book_type }}</flux:badge>
                        <span class="text-xs text-zinc-500">{{ $book->publication_year }} &bull; {{ $book->page_count }} pp.</span>
                    </div>

                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-3 line-clamp-3 leading-relaxed">
                        {{ $book->description }}
                    </p>
                </div>

                <!-- Card Footer Actions -->
                <div class="mt-5 pt-4 border-t border-zinc-100 dark:border-zinc-700/70 flex items-center justify-between gap-2">
                    <button
                        type="button"
                        x-on:click="openPreview(@js($bookPayload))"
                        class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline"
                    >
                        Quick Preview
                    </button>

                    <div class="flex items-center gap-2">
                        <a
                            href="{{ route('books.show', $book->book_id) }}"
                            class="px-3 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-xs font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700"
                        >
                            Details
                        </a>

                        @auth
                            <button
                                type="button"
                                x-on:click="openConfirm(@js($bookPayload))"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white shadow-xs transition {{ $book->availability ? 'bg-indigo-600 hover:bg-indigo-500' : 'bg-amber-600 hover:bg-amber-500' }}"
                            >
                                {{ $book->availability ? 'Reserve Book' : 'Join Queue' }}
                            </button>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white"
                            >
                                Login to Reserve
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-12 text-center">
                <flux:heading size="lg">No matching books found</flux:heading>
                <flux:subheading class="mt-1">Try clearing your search query or switching the genre/availability filter.</flux:subheading>
                <div class="mt-4">
                    <flux:button wire:click="resetFilters" variant="subtle" size="sm">Show All Books</flux:button>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $books->links() }}
    </div>

    <!-- Alpine.js Interactive Book Quick-View Modal (Uses XSS-safe x-text bindings) -->
    <div
        x-show="previewOpen"
        x-transition.opacity
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4"
    >
        <div
            x-on:click.away="previewOpen = false"
            class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4"
        >
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="text-xs font-mono font-semibold text-indigo-600" x-text="selectedBook.code"></span>
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 mt-0.5" x-text="selectedBook.title"></h3>
                    <p class="text-sm text-zinc-500" x-text="'By ' + selectedBook.author"></p>
                </div>
                <button type="button" x-on:click="previewOpen = false" class="text-zinc-400 hover:text-zinc-600 text-sm font-bold">&times;</button>
            </div>

            <div class="grid grid-cols-2 gap-3 bg-zinc-50 dark:bg-zinc-900/60 rounded-xl p-3.5 text-xs">
                <div>
                    <span class="text-zinc-400 block">Genre</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200" x-text="selectedBook.genre"></span>
                </div>
                <div>
                    <span class="text-zinc-400 block">Publisher &amp; Year</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200" x-text="selectedBook.publisher + ' (' + selectedBook.year + ')'"></span>
                </div>
                <div>
                    <span class="text-zinc-400 block">Format &amp; Length</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200" x-text="selectedBook.type + ' • ' + selectedBook.pages + ' pages'"></span>
                </div>
                <div>
                    <span class="text-zinc-400 block">Availability Status</span>
                    <span
                        class="font-semibold"
                        :class="selectedBook.available ? 'text-emerald-600' : 'text-amber-600'"
                        x-text="selectedBook.available ? 'Available for Immediate Reservation' : 'Currently Checked Out (Waitlist Queue)'"
                    ></span>
                </div>
            </div>

            <p class="text-sm text-zinc-600 dark:text-zinc-300 leading-relaxed" x-text="selectedBook.description"></p>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button type="button" variant="ghost" size="sm" x-on:click="previewOpen = false">
                    Close
                </flux:button>
                @auth
                    <flux:button
                        type="button"
                        variant="primary"
                        size="sm"
                        x-on:click="previewOpen = false; confirmOpen = true"
                    >
                        Proceed to Reserve
                    </flux:button>
                @endauth
            </div>
        </div>
    </div>

    <!-- Alpine.js Interactive Reservation Confirmation Dialog (x-data, x-show, x-on:click, x-text) -->
    <div
        x-show="confirmOpen"
        x-transition.opacity
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4"
    >
        <div
            x-on:click.away="confirmOpen = false"
            class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4"
        >
            <flux:heading size="lg">Confirm Book Reservation</flux:heading>
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                You are requesting:
                <strong class="text-zinc-900 dark:text-zinc-100" x-text="selectedBook.title"></strong>
                (<span class="font-mono text-xs" x-text="selectedBook.code"></span>).
            </p>
            <div class="rounded-xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 p-3 text-xs text-indigo-800 dark:text-indigo-200">
                <span x-show="selectedBook.available">
                    This title is currently available. Your reservation will be recorded with status <strong>PENDING / READY</strong> for pickup.
                </span>
                <span x-show="!selectedBook.available">
                    This title is currently checked out on loan. Your request will automatically join the priority waitlist with status <strong>QUEUED</strong>.
                </span>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <flux:button type="button" variant="ghost" size="sm" x-on:click="confirmOpen = false">
                    Cancel
                </flux:button>
                <flux:button
                    type="button"
                    variant="primary"
                    size="sm"
                    x-on:click="$wire.reserveBook(selectedBook.id); confirmOpen = false"
                >
                    Confirm Reservation
                </flux:button>
            </div>
        </div>
    </div>
</div>
