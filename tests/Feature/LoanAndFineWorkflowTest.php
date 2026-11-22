<?php

use App\Livewire\Fines\FineManager;
use App\Livewire\Loans\LoanManager;
use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use Livewire\Livewire;

test('Staff can verify member, scan book barcode, issue loan, and process overdue return with automatic fine via LoanManager', function () {
    $staff = User::factory()->staff()->create();
    $member = User::factory()->member()->create(['name' => 'Ahmad bin Ali']);
    $book = Book::factory()->create([
        'title' => 'Advanced Web Programming',
        'availability' => true,
    ]);

    // 1. Staff verifies member & scans book barcode, then issues loan
    Livewire::actingAs($staff)
        ->test(LoanManager::class)
        ->set('memberLookup', $member->memberCode())
        ->call('verifyMember')
        ->assertSet('selectedUserId', $member->user_id)
        ->set('bookLookup', $book->bookCode())
        ->call('scanBook')
        ->assertSet('selectedBookId', $book->book_id)
        ->set('borrowed_date', now()->subDays(20)->toDateString())
        ->set('due_date', now()->subDays(5)->toDateString())
        ->call('issueLoan')
        ->assertHasNoErrors();

    $loan = Loan::where('user_id', $member->user_id)->where('book_id', $book->book_id)->first();
    expect($loan)->not->toBeNull()
        ->and($book->fresh()->availability)->toBeFalse();

    // 2. Staff processes return on overdue loan -> automatic fine (5 days * RM 1.00 = RM 5.00) is created
    Livewire::actingAs($staff)
        ->test(LoanManager::class)
        ->call('returnLoan', $loan->loan_id);

    $loan->refresh();
    expect($loan->status)->toBe('returned')
        ->and($book->fresh()->availability)->toBeTrue()
        ->and($loan->fine)->not->toBeNull()
        ->and((float) $loan->fine->amount)->toBe(5.00)
        ->and($loan->fine->status)->toBe('unpaid');
});

test('FineManager allows staff to settle fines as paid or waived and blocks members from modifying fines', function () {
    $staff = User::factory()->staff()->create();
    $member = User::factory()->member()->create();
    $loan = Loan::factory()->overdue()->create(['user_id' => $member->user_id]);
    $fine = Fine::factory()->create([
        'loan_id' => $loan->loan_id,
        'amount' => 10.00,
        'status' => 'unpaid',
    ]);

    Livewire::actingAs($member)
        ->test(FineManager::class)
        ->call('settleFine', $fine->fine_id, 'paid')
        ->assertForbidden();

    Livewire::actingAs($staff)
        ->test(FineManager::class)
        ->call('settleFine', $fine->fine_id, 'paid')
        ->assertHasNoErrors();

    expect($fine->fresh()->status)->toBe('paid');
});
