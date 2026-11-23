<div
    x-data="{
        deleteModalOpen: false,
        targetBook: { id: null, title: '', code: '' },
        promptDelete(id, title, code) {
            this.targetBook = { id, title, code };
            this.deleteModalOpen = true;
        }
    }"
    class="space-y-6"
>
    <!-- Header (Matches Stage 1 Wireframe 4: Book Inventory Management [+ Add New Books]) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl">Book Inventory Management</flux:heading>
                <flux:badge color="amber" size="sm">Admin / Staff Portal</flux:badge>
            </div>
            <flux:subheading class="mt-1">
                Add new titles, edit publication metadata, or toggle real-time book availability statuses.
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            <flux:button href="{{ route('genres.index') }}" variant="subtle" size="sm">
                Manage Genres
            </flux:button>
            <flux:button type="button" wire:click="openCreateModal" variant="primary" size="sm" icon="plus">
                + Add New Book
            </flux:button>
        </div>
    </div>

    <!-- Flash Status -->
    @if (session()->has('status') || session()->has('error'))
        <div class="rounded-xl border px-4 py-3 text-sm {{ session()->has('error') ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}">
            {{ session('error') ?? session('status') }}
        </div>
    @endif

    <!-- Search & Genre Filter -->
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row gap-3 items-center justify-between">
        <div class="w-full sm:w-96">
            <flux:input
                type="text"
                wire:model.live.debounce.250ms="search"
                placeholder="Filter inventory by title, author, or publisher..."
                icon="magnifying-glass"
            />
        </div>

        <select
            wire:model.live="genreFilter"
            class="w-full sm:w-56 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-xs font-medium"
        >
            <option value="">All Genres</option>
            @foreach ($genres as $genre)
                <option value="{{ $genre->genre_id }}">{{ $genre->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Inventory Table (Stage 1 Wireframe 4: ID | Title | Author | Genre | Status | Actions) -->
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900/60 text-xs uppercase text-zinc-500 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="py-3.5 px-4">ID</th>
                        <th class="py-3.5 px-4">Title &amp; Publisher</th>
                        <th class="py-3.5 px-4">Author</th>
                        <th class="py-3.5 px-4">Genre</th>
                        <th class="py-3.5 px-4">Type / Pages</th>
                        <th class="py-3.5 px-4">Availability</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                    @forelse ($books as $book)
                        <tr wire:key="inv-book-{{ $book->book_id }}">
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold">{{ $book->bookCode() }}</td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('books.show', $book->book_id) }}" class="font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $book->title }}
                                </a>
                                <div class="text-xs text-zinc-500">{{ $book->publisher }} ({{ $book->publication_year }})</div>
                            </td>
                            <td class="py-3.5 px-4 text-zinc-700 dark:text-zinc-300">{{ $book->author }}</td>
                            <td class="py-3.5 px-4">
                                <flux:badge color="indigo" size="sm">{{ $book->genre?->name ?? 'General' }}</flux:badge>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-zinc-500">
                                {{ $book->book_type }} &bull; {{ $book->page_count }} pp.
                            </td>
                            <td class="py-3.5 px-4">
                                <button
                                    type="button"
                                    wire:click="toggleAvailability({{ $book->book_id }})"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $book->availability ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}"
                                >
                                    <span class="w-2 h-2 rounded-full {{ $book->availability ? 'bg-emerald-600' : 'bg-amber-600' }}"></span>
                                    {{ $book->availability ? 'Available' : 'Checked Out' }}
                                </button>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <flux:button
                                        type="button"
                                        wire:click="openEditModal({{ $book->book_id }})"
                                        variant="subtle"
                                        size="sm"
                                    >
                                        Edit
                                    </flux:button>

                                    @if (auth()->user()->isAdmin())
                                        <button
                                            type="button"
                                            x-on:click="promptDelete({{ $book->book_id }}, @js($book->title), @js($book->bookCode()))"
                                            class="px-2.5 py-1.5 rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 text-xs font-semibold"
                                        >
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-xs text-zinc-500">No books in inventory matching your filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-700">
            {{ $books->links() }}
        </div>
    </div>

    <!-- Livewire + Flux UI Create / Edit Book Modal -->
    @if ($showFormModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-y-auto">
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl max-w-2xl w-full p-6 shadow-xl space-y-5 my-8">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">
                        {{ $editingBookId ? 'Edit Book Details' : 'Add New Book Title to Inventory' }}
                    </flux:heading>
                    <button type="button" wire:click="resetForm" class="text-zinc-400 hover:text-zinc-600 font-bold">&times;</button>
                </div>

                <form wire:submit="saveBook" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <flux:field>
                                <flux:label>Book Title</flux:label>
                                <flux:input type="text" wire:model="title" required placeholder="e.g. Clean Code" />
                                <flux:error name="title" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label>Author</flux:label>
                            <flux:input type="text" wire:model="author" required placeholder="e.g. Robert C. Martin" />
                            <flux:error name="author" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Publisher</flux:label>
                            <flux:input type="text" wire:model="publisher" required placeholder="e.g. Prentice Hall" />
                            <flux:error name="publisher" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Genre</flux:label>
                            <select
                                wire:model="genre_id"
                                required
                                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm"
                            >
                                <option value="">-- Select Genre --</option>
                                @foreach ($genres as $genre)
                                    <option value="{{ $genre->genre_id }}">{{ $genre->name }}</option>
                                @endforeach
                            </select>
                            <flux:error name="genre_id" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Book Type / Format</flux:label>
                            <select
                                wire:model="book_type"
                                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm"
                            >
                                <option value="Hardcover">Hardcover</option>
                                <option value="Paperback">Paperback</option>
                                <option value="Reference">Reference</option>
                                <option value="Technical Manual">Technical Manual</option>
                            </select>
                            <flux:error name="book_type" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Publication Year</flux:label>
                            <flux:input type="number" wire:model="publication_year" min="1901" max="2030" required />
                            <flux:error name="publication_year" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Page Count</flux:label>
                            <flux:input type="number" wire:model="page_count" min="1" max="10000" required />
                            <flux:error name="page_count" />
                        </flux:field>

                        <div class="sm:col-span-2">
                            <flux:field>
                                <flux:label>Synopsis / Description</flux:label>
                                <flux:textarea wire:model="description" rows="3" required placeholder="Enter book description..." />
                                <flux:error name="description" />
                            </flux:field>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 cursor-pointer">
                                <input type="checkbox" wire:model="availability" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                                <span>Mark book as currently Available for borrowing</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button type="button" variant="ghost" size="sm" wire:click="resetForm">
                            Cancel
                        </flux:button>
                        <flux:button type="submit" variant="primary" size="sm">
                            {{ $editingBookId ? 'Update Book' : 'Save Book to Catalogue' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Alpine.js Delete Confirmation Modal (x-data, x-show, x-text) -->
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
            <flux:heading size="lg">Remove Book from Inventory?</flux:heading>
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                Permanently delete <strong class="text-zinc-900 dark:text-zinc-100" x-text="targetBook.title"></strong>
                (<span class="font-mono text-xs" x-text="targetBook.code"></span>)?
                Associated loan and reservation records will cascade on delete.
            </p>
            <div class="flex items-center justify-end gap-2 pt-2">
                <flux:button type="button" variant="ghost" size="sm" x-on:click="deleteModalOpen = false">
                    Cancel
                </flux:button>
                <flux:button
                    type="button"
                    variant="danger"
                    size="sm"
                    x-on:click="$wire.deleteBook(targetBook.id); deleteModalOpen = false"
                >
                    Confirm Delete
                </flux:button>
            </div>
        </div>
    </div>
</div>
