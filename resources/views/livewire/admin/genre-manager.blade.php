<div
    x-data="{
        openAccordion: null,
        deleteModalOpen: false,
        targetGenre: { id: null, name: '' },
        toggleAccordion(id) {
            this.openAccordion = this.openAccordion === id ? null : id;
        }
    }"
    class="space-y-6"
>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl">Library Genres &amp; Classification</flux:heading>
                <flux:badge color="indigo" size="sm">Genres Module</flux:badge>
            </div>
            <flux:subheading class="mt-1">
                Browse catalogue categories with interactive Alpine.js accordions or manage genres as Admin.
            </flux:subheading>
        </div>

        <div class="w-full sm:w-64">
            <flux:input
                type="text"
                wire:model.live.debounce.250ms="search"
                placeholder="Search genres..."
                icon="magnifying-glass"
            />
        </div>
    </div>

    <!-- Flash Message -->
    @if (session()->has('status') || session()->has('error'))
        <div class="rounded-xl border px-4 py-3 text-sm {{ session()->has('error') ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}">
            {{ session('error') ?? session('status') }}
        </div>
    @endif

    <!-- Admin Genre Create / Update Form -->
    @auth
        @if (auth()->user()->isAdmin())
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm">
                <flux:heading size="lg">
                    {{ $editingGenreId ? 'Update Genre Name' : 'Create New Library Genre' }}
                </flux:heading>

                <form wire:submit="saveGenre" class="mt-4 flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                    <div class="flex-1 w-full">
                        <flux:field>
                            <flux:label>Genre Name</flux:label>
                            <flux:input type="text" wire:model="name" required placeholder="e.g. Cloud Computing & DevOps" />
                            <flux:error name="name" />
                        </flux:field>
                    </div>

                    <div class="flex gap-2">
                        <flux:button type="submit" variant="primary">
                            {{ $editingGenreId ? 'Update Genre' : 'Add Genre' }}
                        </flux:button>
                        @if ($editingGenreId)
                            <flux:button type="button" wire:click="cancelEdit" variant="ghost">
                                Cancel
                            </flux:button>
                        @endif
                    </div>
                </form>
            </div>
        @endif
    @endauth

    <!-- Alpine.js Interactive Genre Accordions -->
    <div class="space-y-3">
        @forelse ($genres as $genre)
            <div
                wire:key="genre-card-{{ $genre->genre_id }}"
                class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-sm overflow-hidden"
            >
                <div class="p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            x-on:click="toggleAccordion({{ $genre->genre_id }})"
                            class="text-left flex items-center gap-2.5 font-bold text-base text-zinc-900 dark:text-zinc-100"
                        >
                            <span
                                class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-indigo-50 dark:bg-indigo-950 text-indigo-600 text-xs font-mono"
                                x-text="openAccordion === {{ $genre->genre_id }} ? '−' : '+'"
                            ></span>
                            <span>{{ $genre->name }}</span>
                        </button>
                        <flux:badge color="indigo" size="sm">{{ $genre->books_count }} {{ Str::plural('book', $genre->books_count) }}</flux:badge>
                    </div>

                    <div class="flex items-center gap-2">
                        <a
                            href="{{ route('books.index', ['genreFilter' => $genre->genre_id]) }}"
                            class="px-3 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-xs font-medium hover:bg-zinc-50 dark:hover:bg-zinc-700"
                        >
                            Filter in Catalogue
                        </a>

                        @auth
                            @if (auth()->user()->isAdmin())
                                <flux:button
                                    type="button"
                                    wire:click="editGenre({{ $genre->genre_id }})"
                                    variant="subtle"
                                    size="sm"
                                >
                                    Edit
                                </flux:button>
                                <button
                                    type="button"
                                    x-on:click="targetGenre = { id: {{ $genre->genre_id }}, name: @js($genre->name) }; deleteModalOpen = true"
                                    class="px-2.5 py-1.5 rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 text-xs font-semibold"
                                >
                                    Delete
                                </button>
                            @endif
                        @endauth
                    </div>
                </div>

                <!-- Accordion Body showing books in this genre -->
                <div
                    x-show="openAccordion === {{ $genre->genre_id }}"
                    x-transition
                    style="display: none;"
                    class="border-t border-zinc-100 dark:border-zinc-700 bg-zinc-50/60 dark:bg-zinc-900/50 px-5 py-4"
                >
                    @if ($genre->books->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($genre->books as $book)
                                <div class="flex items-center justify-between bg-white dark:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-700 rounded-xl p-3 text-xs">
                                    <div>
                                        <a href="{{ route('books.show', $book->book_id) }}" class="font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $book->title }}
                                        </a>
                                        <p class="text-zinc-500 mt-0.5">{{ $book->author }} &bull; {{ $book->publication_year }}</p>
                                    </div>
                                    <flux:badge :color="$book->availability ? 'emerald' : 'amber'" size="sm">
                                        {{ $book->availability ? 'Available' : 'On Loan' }}
                                    </flux:badge>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-zinc-500">No books assigned to this genre yet.</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-8 text-center text-xs text-zinc-500">
                No genres found.
            </div>
        @endforelse
    </div>

    <!-- Alpine.js Delete Genre Confirmation Modal -->
    <div
        x-show="deleteModalOpen"
        x-transition.opacity
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4"
    >
        <div
            x-on:click.away="deleteModalOpen = false"
            class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4"
        >
            <flux:heading size="lg">Delete Genre?</flux:heading>
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                Are you sure you want to delete <strong class="text-zinc-900 dark:text-zinc-100" x-text="targetGenre.name"></strong>?
                All books under this genre will also be removed via foreign key cascade.
            </p>
            <div class="flex items-center justify-end gap-2 pt-2">
                <flux:button type="button" variant="ghost" size="sm" x-on:click="deleteModalOpen = false">
                    Cancel
                </flux:button>
                <flux:button
                    type="button"
                    variant="danger"
                    size="sm"
                    x-on:click="$wire.deleteGenre(targetGenre.id); deleteModalOpen = false"
                >
                    Confirm Delete
                </flux:button>
            </div>
        </div>
    </div>
</div>
