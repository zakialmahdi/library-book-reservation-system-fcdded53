<?php

namespace App\Http\Controllers;

use App\Actions\Reservations\CancelReservationAction;
use App\Actions\Reservations\PlaceReservationAction;
use App\Actions\Reservations\UpdateReservationStatusAction;
use App\Http\Requests\Reservations\ReservationRequest;
use App\Models\Book;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReservationController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Reservation::class);

        return view('reservations.index');
    }

    public function create()
    {
        Gate::authorize('create', Reservation::class);

        return view('reservations.index', ['openCreate' => true]);
    }

    public function store(ReservationRequest $request, PlaceReservationAction $action)
    {
        Gate::authorize('create', Reservation::class);

        $validated = $request->validated();
        $book = Book::findOrFail($validated['book_id']);

        $reservation = $action->execute($request->user(), $book);

        $message = $reservation->status === 'queued'
            ? 'Book is currently on loan. You have been added to the reservation queue!'
            : 'Book reserved successfully!';

        return redirect()->route('reservations.index')
            ->with('success', $message);
    }

    public function show(Reservation $reservation)
    {
        Gate::authorize('view', $reservation);

        $reservation->load(['user', 'book.genre']);

        return view('reservations.show', ['reservation' => $reservation]);
    }

    public function edit(Reservation $reservation)
    {
        Gate::authorize('update', $reservation);

        return view('reservations.index', ['editReservationId' => $reservation->reservation_id]);
    }

    public function update(Request $request, Reservation $reservation, UpdateReservationStatusAction $action)
    {
        Gate::authorize('update', $reservation);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,queued,ready,fulfilled,cancelled'],
        ]);

        $action->execute($reservation, $validated['status']);

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation status updated.');
    }

    public function destroy(Reservation $reservation, CancelReservationAction $action)
    {
        Gate::authorize('delete', $reservation);

        $action->execute($reservation);

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation cancelled.');
    }
}
