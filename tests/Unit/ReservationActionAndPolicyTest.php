<?php

use App\Actions\Reservations\CancelReservationAction;
use App\Actions\Reservations\PlaceReservationAction;
use App\Actions\Reservations\UpdateReservationStatusAction;
use App\Models\Book;
use App\Models\Reservation;
use App\Models\User;
use App\Policies\ReservationPolicy;
use Illuminate\Validation\ValidationException;

test('ReservationPolicy enforces member ownership and allows staff/admin oversight', function () {
    $member = User::factory()->member()->create();
    $otherMember = User::factory()->member()->create();
    $staff = User::factory()->staff()->create();

    $reservation = Reservation::factory()->create(['user_id' => $member->user_id]);
    $policy = new ReservationPolicy;

    expect($policy->view($member, $reservation))->toBeTrue()
        ->and($policy->view($otherMember, $reservation))->toBeFalse()
        ->and($policy->view($staff, $reservation))->toBeTrue()
        ->and($policy->delete($member, $reservation))->toBeTrue()
        ->and($policy->delete($otherMember, $reservation))->toBeFalse();
});

test('PlaceReservationAction assigns pending status for available book and queued status for borrowed book', function () {
    $member1 = User::factory()->member()->create();
    $member2 = User::factory()->member()->create();

    $availableBook = Book::factory()->create(['availability' => true]);
    $borrowedBook = Book::factory()->unavailable()->create();

    $action = new PlaceReservationAction;

    $res1 = $action->execute($member1, $availableBook);
    $res2 = $action->execute($member2, $borrowedBook);

    expect($res1->status)->toBe('pending')
        ->and($res2->status)->toBe('queued');

    // Duplicate active reservation for the same book by the same user must be rejected
    expect(fn () => $action->execute($member1, $availableBook))
        ->toThrow(ValidationException::class);

    $ready = (new UpdateReservationStatusAction)->execute($res1, 'ready');
    expect($ready->status)->toBe('ready');

    $cancelled = (new CancelReservationAction)->execute($res1);
    expect($cancelled->status)->toBe('cancelled');
});
