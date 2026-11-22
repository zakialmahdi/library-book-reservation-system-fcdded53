<?php

use App\Actions\Fines\SettleFineAction;
use App\Actions\Loans\IssueLoanAction;
use App\Actions\Loans\RenewLoanAction;
use App\Actions\Loans\ReturnLoanAction;
use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use App\Policies\FinePolicy;
use App\Policies\LoanPolicy;
use Illuminate\Validation\ValidationException;

test('LoanPolicy and FinePolicy enforce role and ownership rules', function () {
    $admin = User::factory()->admin()->create();
    $staff = User::factory()->staff()->create();
    $owner = User::factory()->member()->create();
    $otherMember = User::factory()->member()->create();

    $loan = Loan::factory()->create(['user_id' => $owner->user_id, 'status' => 'borrowed']);
    $fine = Fine::factory()->create(['loan_id' => $loan->loan_id]);

    $loanPolicy = new LoanPolicy;
    $finePolicy = new FinePolicy;

    expect($loanPolicy->view($owner, $loan))->toBeTrue()
        ->and($loanPolicy->view($otherMember, $loan))->toBeFalse()
        ->and($loanPolicy->create($owner))->toBeFalse()
        ->and($loanPolicy->create($staff))->toBeTrue()
        ->and($loanPolicy->renew($owner, $loan))->toBeTrue()
        ->and($loanPolicy->renew($otherMember, $loan))->toBeFalse()
        ->and($loanPolicy->delete($staff, $loan))->toBeFalse()
        ->and($loanPolicy->delete($admin, $loan))->toBeTrue()
        ->and($finePolicy->view($owner, $fine))->toBeTrue()
        ->and($finePolicy->view($otherMember, $fine))->toBeFalse()
        ->and($finePolicy->update($staff, $fine))->toBeTrue()
        ->and($finePolicy->update($owner, $fine))->toBeFalse();
});

test('IssueLoanAction marks book unavailable, fulfills active reservation, and blocks double checkout', function () {
    $member = User::factory()->member()->create();
    $book = Book::factory()->create(['availability' => true]);

    $reservation = Reservation::factory()->create([
        'user_id' => $member->user_id,
        'book_id' => $book->book_id,
        'status' => 'ready',
    ]);

    $loan = (new IssueLoanAction)->execute([
        'user_id' => $member->user_id,
        'book_id' => $book->book_id,
        'borrowed_date' => now()->toDateString(),
        'due_date' => now()->addDays(14)->toDateString(),
    ]);

    expect($loan->status)->toBe('borrowed')
        ->and($book->fresh()->availability)->toBeFalse()
        ->and($reservation->fresh()->status)->toBe('fulfilled');

    expect(fn () => (new IssueLoanAction)->execute([
        'user_id' => $member->user_id,
        'book_id' => $book->book_id,
        'borrowed_date' => now()->toDateString(),
        'due_date' => now()->addDays(14)->toDateString(),
    ]))->toThrow(ValidationException::class);
});

test('ReturnLoanAction automatically generates overdue fine and promotes next queued reservation', function () {
    $borrower = User::factory()->member()->create();
    $queuedMember = User::factory()->member()->create();
    $book = Book::factory()->unavailable()->create();

    $loan = Loan::factory()->create([
        'user_id' => $borrower->user_id,
        'book_id' => $book->book_id,
        'borrowed_date' => now()->subDays(20)->toDateString(),
        'due_date' => now()->subDays(6)->toDateString(),
        'status' => 'overdue',
    ]);

    $queuedReservation = Reservation::factory()->queued()->create([
        'user_id' => $queuedMember->user_id,
        'book_id' => $book->book_id,
    ]);

    $returnedLoan = (new ReturnLoanAction)->execute($loan, now()->toDateString(), 1.00);

    expect($returnedLoan->status)->toBe('returned')
        ->and($book->fresh()->availability)->toBeTrue()
        ->and($returnedLoan->fine)->not->toBeNull()
        ->and((float) $returnedLoan->fine->amount)->toBe(6.00)
        ->and($returnedLoan->fine->status)->toBe('unpaid')
        ->and($queuedReservation->fresh()->status)->toBe('ready');

    $settledFine = (new SettleFineAction)->execute($returnedLoan->fine, 'paid');
    expect($settledFine->status)->toBe('paid');
});

test('RenewLoanAction extends due date by 7 days for active non-overdue loans', function () {
    $loan = Loan::factory()->create([
        'borrowed_date' => now()->toDateString(),
        'due_date' => now()->addDays(5)->toDateString(),
        'status' => 'borrowed',
    ]);

    $expectedDate = now()->addDays(12)->toDateString();
    $renewed = (new RenewLoanAction)->execute($loan, 7);

    expect($renewed->due_date->toDateString())->toBe($expectedDate);
});
