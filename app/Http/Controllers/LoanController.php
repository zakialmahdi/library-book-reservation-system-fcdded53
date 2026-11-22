<?php

namespace App\Http\Controllers;

use App\Actions\Loans\DeleteLoanAction;
use App\Actions\Loans\IssueLoanAction;
use App\Actions\Loans\RenewLoanAction;
use App\Actions\Loans\ReturnLoanAction;
use App\Http\Requests\Loans\LoanRequest;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LoanController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Loan::class);

        return view('loans.index');
    }

    public function create()
    {
        Gate::authorize('create', Loan::class);

        return view('loans.index', ['openCreate' => true]);
    }

    public function store(LoanRequest $request, IssueLoanAction $action)
    {
        Gate::authorize('create', Loan::class);

        $action->execute($request->validated());

        return redirect()->route('loans.index')
            ->with('success', 'Book loan issued successfully.');
    }

    public function show(Loan $loan)
    {
        Gate::authorize('view', $loan);

        $loan->load(['user', 'book.genre', 'fine']);

        return view('loans.show', ['loan' => $loan]);
    }

    public function edit(Loan $loan)
    {
        Gate::authorize('update', $loan);

        return view('loans.index', ['editLoanId' => $loan->loan_id]);
    }

    public function update(Request $request, Loan $loan, ReturnLoanAction $returnAction, RenewLoanAction $renewAction)
    {
        if ($request->input('action') === 'renew') {
            Gate::authorize('renew', $loan);
            $renewAction->execute($loan);

            return redirect()->route('loans.index')
                ->with('success', 'Loan due date extended by 7 days.');
        }

        Gate::authorize('update', $loan);

        if ($request->input('status') === 'returned' || $request->input('action') === 'return') {
            $returnAction->execute($loan, $request->input('returned_date'));

            return redirect()->route('loans.index')
                ->with('success', 'Book return processed and inventory updated.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:borrowed,returned,overdue'],
            'due_date' => ['sometimes', 'date'],
        ]);

        $loan->update($validated);

        return redirect()->route('loans.index')
            ->with('success', 'Loan record updated.');
    }

    public function destroy(Loan $loan, DeleteLoanAction $action)
    {
        Gate::authorize('delete', $loan);

        $action->execute($loan);

        return redirect()->route('loans.index')
            ->with('success', 'Loan record deleted.');
    }
}
