<?php

namespace App\Livewire\Books;

use App\Actions\Reservations\PlaceReservationAction;
use App\Http\Requests\Reservations\ReservationRequest;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BookCatalog extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $genreFilter = '';

    #[Url(except: '')]
    public string $availabilityFilter = '';

    #[Url(except: '')]
    public string $bookTypeFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingGenreFilter(): void
    {
        $this->resetPage();
    }

    public function updatingAvailabilityFilter(): void
    {
        $this->resetPage();
    }

    public function updatingBookTypeFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'genreFilter', 'availabilityFilter', 'bookTypeFilter']);
        $this->resetPage();
    }

    public function reserveBook(int $bookId, PlaceReservationAction $action): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'));
            return;
        }

        $this->authorize('create', Reservation::class);

        try {
            $validated = ReservationRequest::validatePayload(['book_id' => $bookId]);
            $book = Book::findOrFail($validated['book_id']);

            $reservation = $action->execute(Auth::user(), $book);

            $msg = $reservation->status === 'queued'
                ? "Joined waitlist queue for \"{$book->title}\" (#RES-{$reservation->reservation_id})."
                : "Reserved \"{$book->title}\" (#RES-{$reservation->reservation_id}).";

            session()->flash('status', $msg);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Unable to reserve book.';
            session()->flash('error', $firstError);
        }
    }

    public function render()
    {
        $cleanSearch = strip_tags(trim($this->search));

        $booksQuery = Book::query()
            ->with('genre')
            ->withCount([
                'reservations as active_queue_count' => fn ($q) => $q->whereIn('status', ['pending', 'queued', 'ready']),
            ]);

        if ($cleanSearch !== '') {
            $booksQuery->where(function ($q) use ($cleanSearch) {
                $q->where('title', 'like', "%{$cleanSearch}%")
                    ->orWhere('author', 'like', "%{$cleanSearch}%")
                    ->orWhere('publisher', 'like', "%{$cleanSearch}%");
            });
        }

        if ($this->genreFilter !== '') {
            $booksQuery->where('genre_id', (int) $this->genreFilter);
        }

        if ($this->availabilityFilter === 'available') {
            $booksQuery->where('availability', true);
        } elseif ($this->availabilityFilter === 'borrowed') {
            $booksQuery->where('availability', false);
        }

        if ($this->bookTypeFilter !== '') {
            $booksQuery->where('book_type', $this->bookTypeFilter);
        }

        return view('livewire.books.book-catalog', [
            'books' => $booksQuery->orderByDesc('book_id')->paginate(9),
            'genres' => Genre::orderBy('name')->get(),
        ]);
    }
}
