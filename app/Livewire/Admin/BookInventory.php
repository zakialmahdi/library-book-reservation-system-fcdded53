<?php

namespace App\Livewire\Admin;

use App\Actions\Books\CreateBookAction;
use App\Actions\Books\DeleteBookAction;
use App\Actions\Books\ToggleBookAvailabilityAction;
use App\Actions\Books\UpdateBookAction;
use App\Http\Requests\Books\BookRequest;
use App\Models\Book;
use App\Models\Genre;
use Livewire\Component;
use Livewire\WithPagination;

class BookInventory extends Component
{
    use WithPagination;

    public string $search = '';
    public string $genreFilter = '';

    public ?int $editingBookId = null;
    public string $title = '';
    public string $author = '';
    public string $publisher = '';
    public int $publication_year = 2026;
    public string $description = '';
    public int $page_count = 250;
    public string $genre_id = '';
    public string $book_type = 'Hardcover';
    public bool $availability = true;

    public bool $showFormModal = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->authorize('create', Book::class);
        $this->resetForm();
        $firstGenre = Genre::orderBy('name')->first();
        $this->genre_id = $firstGenre ? (string) $firstGenre->genre_id : '';
        $this->showFormModal = true;
    }

    public function openEditModal(int $bookId): void
    {
        $book = Book::findOrFail($bookId);
        $this->authorize('update', $book);

        $this->editingBookId = $book->book_id;
        $this->title = $book->title;
        $this->author = $book->author;
        $this->publisher = $book->publisher;
        $this->publication_year = (int) $book->publication_year;
        $this->description = $book->description;
        $this->page_count = (int) $book->page_count;
        $this->genre_id = (string) $book->genre_id;
        $this->book_type = $book->book_type;
        $this->availability = (bool) $book->availability;
        $this->resetValidation();
        $this->showFormModal = true;
    }

    public function saveBook(CreateBookAction $createAction, UpdateBookAction $updateAction): void
    {
        $payload = [
            'title' => $this->title,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'publication_year' => $this->publication_year,
            'description' => $this->description,
            'page_count' => $this->page_count,
            'genre_id' => (int) $this->genre_id,
            'book_type' => $this->book_type,
            'availability' => $this->availability,
        ];

        $validated = BookRequest::validatePayload($payload);

        if ($this->editingBookId) {
            $book = Book::findOrFail($this->editingBookId);
            $this->authorize('update', $book);
            $updateAction->execute($book, $validated, $this->availability);
            session()->flash('status', "Updated book \"{$book->title}\" (#{$book->bookCode()}).");
        } else {
            $this->authorize('create', Book::class);
            $book = $createAction->execute($validated, $this->availability);
            session()->flash('status', "Added new title \"{$book->title}\" (#{$book->bookCode()}) to inventory.");
        }

        $this->resetForm();
        $this->showFormModal = false;
    }

    public function toggleAvailability(int $bookId, ToggleBookAvailabilityAction $action): void
    {
        $book = Book::findOrFail($bookId);
        $this->authorize('update', $book);

        $updated = $action->execute($book);
        $state = $updated->availability ? 'Available' : 'Checked Out / Unavailable';
        session()->flash('status', "Book \"{$updated->title}\" status changed to {$state}.");
    }

    public function deleteBook(int $bookId, DeleteBookAction $action): void
    {
        $book = Book::findOrFail($bookId);
        $this->authorize('delete', $book);

        $title = $book->title;
        $action->execute($book);
        session()->flash('status', "Removed \"{$title}\" from library inventory.");
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingBookId',
            'title',
            'author',
            'publisher',
            'description',
            'showFormModal',
        ]);
        $this->publication_year = (int) date('Y');
        $this->page_count = 250;
        $this->book_type = 'Hardcover';
        $this->availability = true;
        $this->resetValidation();
    }

    public function render()
    {
        $cleanSearch = strip_tags(trim($this->search));

        $query = Book::with('genre')
            ->when($cleanSearch !== '', function ($q) use ($cleanSearch) {
                $q->where(function ($sub) use ($cleanSearch) {
                    $sub->where('title', 'like', "%{$cleanSearch}%")
                        ->orWhere('author', 'like', "%{$cleanSearch}%")
                        ->orWhere('publisher', 'like', "%{$cleanSearch}%");
                });
            })
            ->when($this->genreFilter !== '', fn ($q) => $q->where('genre_id', (int) $this->genreFilter))
            ->orderByDesc('book_id');

        return view('livewire.admin.book-inventory', [
            'books' => $query->paginate(8),
            'genres' => Genre::orderBy('name')->get(),
        ]);
    }
}
