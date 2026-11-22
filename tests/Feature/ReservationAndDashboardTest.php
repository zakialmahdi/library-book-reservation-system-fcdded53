<?php

use App\Livewire\Books\BookCatalog;
use App\Livewire\Dashboard\MemberDashboard;
use App\Livewire\Reservations\ReservationManager;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use Livewire\Livewire;

test('Members can reserve available books or join queue for borrowed books and staff can convert reservation to loan', function () {
    $member = User::factory()->member()->create();
    $staff = User::factory()->staff()->create();
    $availableBook = Book::factory()->create(['availability' => true]);
    $borrowedBook = Book::factory()->unavailable()->create();

    // Reserve available book via BookCatalog Livewire component
    Livewire::actingAs($member)
        ->test(BookCatalog::class)
        ->call('reserveBook', $availableBook->book_id);

    $res1 = Reservation::where('user_id', $member->user_id)->where('book_id', $availableBook->book_id)->first();
    expect($res1)->not->toBeNull()
        ->and($res1->status)->toBe('pending');

    // Reserve borrowed book -> automatically joins queue ('queued')
    Livewire::actingAs($member)
        ->test(ReservationManager::class)
        ->set('selectedBookId', $borrowedBook->book_id)
        ->call('placeReservation');

    $res2 = Reservation::where('user_id', $member->user_id)->where('book_id', $borrowedBook->book_id)->first();
    expect($res2)->not->toBeNull()
        ->and($res2->status)->toBe('queued');

    // Staff fulfills $res1 into an active Loan
    Livewire::actingAs($staff)
        ->test(ReservationManager::class)
        ->call('fulfillToLoan', $res1->reservation_id);

    expect($res1->fresh()->status)->toBe('fulfilled')
        ->and($availableBook->fresh()->availability)->toBeFalse()
        ->and(Loan::where('user_id', $member->user_id)->where('book_id', $availableBook->book_id)->exists())->toBeTrue();
});

test('MemberDashboard displays active loans and reservations and allows loan renewal and reservation cancellation', function () {
    $member = User::factory()->member()->create(['name' => 'Muhammad Azhan']);
    $book1 = Book::factory()->unavailable()->create(['title' => 'Domain Driven Laravel']);
    $book2 = Book::factory()->create(['title' => 'Upin and Ipin']);

    $loan = Loan::factory()->create([
        'user_id' => $member->user_id,
        'book_id' => $book1->book_id,
        'borrowed_date' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'status' => 'borrowed',
    ]);

    $reservation = Reservation::factory()->ready()->create([
        'user_id' => $member->user_id,
        'book_id' => $book2->book_id,
    ]);

    $expectedExtendedDue = now()->addDays(14)->toDateString();

    Livewire::actingAs($member)
        ->test(MemberDashboard::class)
        ->assertSee('Welcome Back, Muhammad Azhan!')
        ->assertSee('Domain Driven Laravel')
        ->assertSee('Upin and Ipin')
        ->call('renewLoan', $loan->loan_id)
        ->call('cancelReservation', $reservation->reservation_id);

    expect($loan->fresh()->due_date->toDateString())->toBe($expectedExtendedDue)
        ->and($reservation->fresh()->status)->toBe('cancelled');
});
