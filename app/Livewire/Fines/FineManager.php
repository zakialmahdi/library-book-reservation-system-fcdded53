<?php

namespace App\Livewire\Fines;

use App\Actions\Fines\DeleteFineAction;
use App\Actions\Fines\IssueFineAction;
use App\Actions\Fines\SettleFineAction;
use App\Http\Requests\Fines\FineRequest;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class FineManager extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public ?int $loan_id = null;
    public string $amount = '5.00';
    public string $status = 'unpaid';
    public bool $showIssueModal = false;

    public function openIssueModal(): void
    {
        $this->authorize('create', Fine::class);
        $this->reset(['loan_id']);
        $this->amount = '5.00';
        $this->status = 'unpaid';
        $this->resetValidation();
        $this->showIssueModal = true;
    }

    public function issueFine(IssueFineAction $action): void
    {
        $this->authorize('create', Fine::class);

        $validated = FineRequest::validatePayload([
            'loan_id' => $this->loan_id,
            'amount' => $this->amount,
            'status' => $this->status,
        ]);

        $fine = $action->execute($validated);
        session()->flash('status', "Issued fine #FN-{$fine->fine_id} (RM ".number_format((float) $fine->amount, 2).") for Loan #LN-{$fine->loan_id}.");
        $this->showIssueModal = false;
    }

    public function settleFine(int $fineId, string $newStatus, SettleFineAction $action): void
    {
        $fine = Fine::with('loan.user')->findOrFail($fineId);
        $this->authorize('update', $fine);

        $updated = $action->execute($fine, $newStatus);
        session()->flash('status', "Fine #FN-{$updated->fine_id} marked as ".strtoupper($updated->status).'.');
    }

    public function deleteFine(int $fineId, DeleteFineAction $action): void
    {
        $fine = Fine::findOrFail($fineId);
        $this->authorize('delete', $fine);

        $action->execute($fine);
        session()->flash('status', "Deleted fine record #FN-{$fineId}.");
    }

    public function render()
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        $finesQuery = Fine::with(['loan.user', 'loan.book'])
            ->when(! $authUser->isStaffOrAdmin(), function ($q) use ($authUser) {
                $q->whereHas('loan', fn ($l) => $l->where('user_id', $authUser->user_id));
            })
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('fine_id');

        $eligibleLoans = $authUser->isStaffOrAdmin()
            ? Loan::with(['user', 'book'])->orderByDesc('loan_id')->get()
            : collect();

        return view('livewire.fines.fine-manager', [
            'fines' => $finesQuery->paginate(10),
            'eligibleLoans' => $eligibleLoans,
        ]);
    }
}
