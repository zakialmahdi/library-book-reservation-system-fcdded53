<?php

namespace App\Livewire\Loans;

use App\Actions\Loans\DeleteLoanAction;
use App\Actions\Loans\IssueLoanAction;
use App\Actions\Loans\RenewLoanAction;
use App\Actions\Loans\ReturnLoanAction;
use App\Http\Requests\Loans\LoanRequest;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class LoanManager extends Component
{
    use WithPagination;

    // Staff Circulation Desk fields (matches Stage 1 Wireframe 3)
    public string $memberLookup = '';
    public ?int $selectedUserId = null;
    public string $bookLookup = '';
    public ?int $selectedBookId = null;
    public string $borrowed_date = '';
    public string $due_date = '';

    // Filter fields
    public string $statusFilter = '';
    public string $search = '';

    public function mount(): void
    {
        $this->borrowed_date = now()->toDateString();
        $this->due_date = now()->addDays(14)->toDateString();
    }

    public function verifyMember(): void
    {
        $term = strip_tags(trim($this->memberLookup));
        if ($term === '') {
            $this->selectedUserId = null;
            return;
        }

        // Parse MBR-2026-0003 format or numeric ID or name/email
        if (preg_match('/MBR-\d{4}-0*(\d+)/i', $term, $matches)) {
            $user = User::find((int) $matches[1]);
        } elseif (is_numeric($term)) {
            $user = User::find((int) $term);
        } else {
            $user = User::where('email', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%")
                ->first();
        }

        if ($user) {
            $this->selectedUserId = $user->user_id;
            session()->flash('status', "Verified Member: {$user->name} ({$user->memberCode()}) — Status: Active.");
        } else {
            $this->selectedUserId = null;
            session()->flash('error', "No member found matching \"{$term}\".");
        }
    }

    public function scanBook(): void
    {
        $term = strip_tags(trim($this->bookLookup));
        if ($term === '') {
            $this->selectedBookId = null;
            return;
        }

        // Parse BK-0001 format or numeric ID or book title
        if (preg_match('/BK-0*(\d+)/i', $term, $matches)) {
            $book = Book::find((int) $matches[1]);
        } elseif (is_numeric($term)) {
            $book = Book::find((int) $term);
        } else {
            $book = Book::where('title', 'like', "%{$term}%")->first();
        }

        if ($book) {
            $this->selectedBookId = $book->book_id;
            $availText = $book->availability ? 'Available' : 'Checked Out / Unavailable';
            session()->flash('status', "Scanned Book: \"{$book->title}\" ({$book->bookCode()}) — {$availText}.");
        } else {
            $this->selectedBookId = null;
            session()->flash('error', "No book found matching barcode/title \"{$term}\".");
        }
    }

    public function issueLoan(IssueLoanAction $action): void
    {
        $this->authorize('create', Loan::class);

        $validated = LoanRequest::validatePayload([
            'user_id' => $this->selectedUserId,
            'book_id' => $this->selectedBookId,
            'borrowed_date' => $this->borrowed_date,
            'due_date' => $this->due_date,
            'status' => 'borrowed',
        ]);

        try {
            $loan = $action->execute($validated);
            $loan->load(['user', 'book']);

            session()->flash(
                'status',
                "Loan #LN-{$loan->loan_id} confirmed: \"{$loan->book->title}\" issued to {$loan->user->name} (Due: {$loan->due_date->format('d M Y')})."
            );

            $this->reset(['selectedBookId', 'bookLookup']);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Failed to issue loan.';
            session()->flash('error', $firstError);
        }
    }

    public function returnLoan(int $loanId, ReturnLoanAction $action): void
    {
        $loan = Loan::with(['book', 'user'])->findOrFail($loanId);
        $this->authorize('update', $loan);

        $returnedLoan = $action->execute($loan);

        $fineMessage = $returnedLoan->fine
            ? " Overdue fine of RM ".number_format((float) $returnedLoan->fine->amount, 2)." automatically generated."
            : ' Returned on time with RM 0.00 fine.';

        session()->flash(
            'status',
            "Processed return for \"{$returnedLoan->book->title}\" from {$returnedLoan->user->name}.{$fineMessage}"
        );
    }

    public function renewLoan(int $loanId, RenewLoanAction $action): void
    {
        $loan = Loan::with('book')->findOrFail($loanId);
        $this->authorize('renew', $loan);

        try {
            $updated = $action->execute($loan, 7);
            session()->flash(
                'status',
                "Renewed \"{$updated->book->title}\". New due date: {$updated->due_date->format('d M Y')}."
            );
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Unable to renew loan.';
            session()->flash('error', $firstError);
        }
    }

    public function deleteLoan(int $loanId, DeleteLoanAction $action): void
    {
        $loan = Loan::findOrFail($loanId);
        $this->authorize('delete', $loan);

        $action->execute($loan);
        session()->flash('status', "Deleted loan record #LN-{$loanId}.");
    }

    public function render()
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        $cleanSearch = strip_tags(trim($this->search));

        $loansQuery = Loan::with(['user', 'book.genre', 'fine'])
            ->when(! $authUser->isStaffOrAdmin(), fn ($q) => $q->where('user_id', $authUser->user_id))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($cleanSearch !== '', function ($q) use ($cleanSearch) {
                $q->where(function ($sub) use ($cleanSearch) {
                    $sub->whereHas('book', fn ($b) => $b->where('title', 'like', "%{$cleanSearch}%"))
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$cleanSearch}%"));
                });
            })
            ->orderByDesc('loan_id');

        return view('livewire.loans.loan-manager', [
            'loans' => $loansQuery->paginate(10),
            'verifiedMember' => $this->selectedUserId ? User::find($this->selectedUserId) : null,
            'scannedBook' => $this->selectedBookId ? Book::with('genre')->find($this->selectedBookId) : null,
            'allMembers' => $authUser->isStaffOrAdmin() ? User::orderBy('name')->get() : collect(),
            'availableBooks' => $authUser->isStaffOrAdmin() ? Book::where('availability', true)->orderBy('title')->get() : collect(),
        ]);
    }
}
