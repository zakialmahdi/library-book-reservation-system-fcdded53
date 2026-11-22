<?php

namespace App\Livewire\Reservations;

use App\Actions\Loans\IssueLoanAction;
use App\Actions\Reservations\CancelReservationAction;
use App\Actions\Reservations\PlaceReservationAction;
use App\Actions\Reservations\UpdateReservationStatusAction;
use App\Http\Requests\Reservations\ReservationRequest;
use App\Models\Book;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class ReservationManager extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public ?int $selectedBookId = null;

    public function placeReservation(PlaceReservationAction $action): void
    {
        $this->authorize('create', Reservation::class);

        try {
            $validated = ReservationRequest::validatePayload([
                'book_id' => $this->selectedBookId,
            ]);

            $book = Book::findOrFail($validated['book_id']);
            $reservation = $action->execute(Auth::user(), $book);

            $label = $reservation->status === 'queued'
                ? 'Added to waitlist queue (book currently checked out)'
                : 'Reservation placed and awaiting pickup confirmation';

            session()->flash('status', "Reservation #RES-{$reservation->reservation_id} for \"{$book->title}\": {$label}.");
            $this->reset('selectedBookId');
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Could not place reservation.';
            session()->flash('error', $firstError);
        }
    }

    public function updateStatus(int $reservationId, string $status, UpdateReservationStatusAction $action): void
    {
        $reservation = Reservation::with(['book', 'user'])->findOrFail($reservationId);
        $this->authorize('update', $reservation);

        $updated = $action->execute($reservation, $status);
        session()->flash(
            'status',
            "Reservation #RES-{$updated->reservation_id} ({$updated->book->title}) updated to ".strtoupper($updated->status).'.'
        );
    }

    public function fulfillToLoan(int $reservationId, IssueLoanAction $loanAction): void
    {
        $reservation = Reservation::with(['book', 'user'])->findOrFail($reservationId);
        $this->authorize('update', $reservation);

        try {
            $loan = $loanAction->execute([
                'user_id' => $reservation->user_id,
                'book_id' => $reservation->book_id,
                'borrowed_date' => now()->toDateString(),
                'due_date' => now()->addDays(14)->toDateString(),
                'status' => 'borrowed',
            ]);

            session()->flash(
                'status',
                "Fulfilled reservation #RES-{$reservation->reservation_id} and issued Loan #LN-{$loan->loan_id} to {$reservation->user->name}."
            );
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Book is currently unavailable for loan conversion.';
            session()->flash('error', $firstError);
        }
    }

    public function cancelReservation(int $reservationId, CancelReservationAction $action): void
    {
        $reservation = Reservation::with('book')->findOrFail($reservationId);
        $this->authorize('delete', $reservation);

        $action->execute($reservation);
        session()->flash('status', "Cancelled reservation #RES-{$reservationId} for \"{$reservation->book->title}\".");
    }

    public function render()
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        $query = Reservation::with(['user', 'book.genre'])
            ->when(! $authUser->isStaffOrAdmin(), fn ($q) => $q->where('user_id', $authUser->user_id))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('reservation_id');

        return view('livewire.reservations.reservation-manager', [
            'reservations' => $query->paginate(10),
            'books' => Book::with('genre')->orderBy('title')->get(),
        ]);
    }
}
