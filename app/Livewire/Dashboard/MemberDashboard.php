<?php

namespace App\Livewire\Dashboard;

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Actions\Loans\RenewLoanAction;
use App\Actions\Reservations\CancelReservationAction;
use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class MemberDashboard extends Component
{
    public string $profileName = '';
    public string $profileEmail = '';

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $this->profileName = $user->name;
        $this->profileEmail = $user->email;
    }

    public function renewLoan(int $loanId, RenewLoanAction $action): void
    {
        $loan = Loan::with('book')->findOrFail($loanId);
        $this->authorize('renew', $loan);

        try {
            $updated = $action->execute($loan, 7);
            session()->flash('status', "Extended loan for \"{$updated->book->title}\" until {$updated->due_date->format('d M Y')}.");
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Unable to renew loan.';
            session()->flash('error', $firstError);
        }
    }

    public function cancelReservation(int $reservationId, CancelReservationAction $action): void
    {
        $reservation = Reservation::with('book')->findOrFail($reservationId);
        $this->authorize('delete', $reservation);

        $action->execute($reservation);
        session()->flash('status', "Cancelled reservation for \"{$reservation->book->title}\".");
    }

    public function updateProfile(UpdateUserProfileInformation $updater): void
    {
        /** @var User $user */
        $user = Auth::user();
        $this->authorize('update', $user);

        $updater->update($user, [
            'name' => $this->profileName,
            'email' => $this->profileEmail,
        ]);

        $this->profileName = $user->name;
        $this->profileEmail = $user->email;

        session()->flash('status', 'Profile information updated securely.');
    }

    public function render()
    {
        /** @var User $user */
        $user = Auth::user();

        $activeLoans = Loan::with(['book.genre', 'fine'])
            ->where('user_id', $user->user_id)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->orderBy('due_date')
            ->get();

        $currentReservations = Reservation::with('book.genre')
            ->where('user_id', $user->user_id)
            ->whereIn('status', ['pending', 'queued', 'ready'])
            ->orderByDesc('reservation_date')
            ->get();

        $userFines = Fine::with('loan.book')
            ->whereHas('loan', fn ($q) => $q->where('user_id', $user->user_id))
            ->orderByDesc('fine_id')
            ->get();

        $unpaidFineTotal = $userFines->where('status', 'unpaid')->sum('amount');

        $systemStats = null;
        if ($user->isStaffOrAdmin()) {
            $systemStats = [
                'total_books' => Book::count(),
                'available_books' => Book::where('availability', true)->count(),
                'active_loans' => Loan::whereIn('status', ['borrowed', 'overdue'])->count(),
                'pending_reservations' => Reservation::whereIn('status', ['pending', 'queued', 'ready'])->count(),
                'unpaid_fines_rm' => Fine::where('status', 'unpaid')->sum('amount'),
                'total_members' => User::count(),
            ];
        }

        return view('livewire.dashboard.member-dashboard', [
            'user' => $user,
            'activeLoans' => $activeLoans,
            'currentReservations' => $currentReservations,
            'userFines' => $userFines,
            'unpaidFineTotal' => $unpaidFineTotal,
            'systemStats' => $systemStats,
        ]);
    }
}
