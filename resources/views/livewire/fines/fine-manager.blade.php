<div
    x-data="{
        settleConfirmOpen: false,
        targetFine: { id: null, amount: '', member: '', newStatus: 'paid' },
        promptSettle(fine, newStatus) {
            this.targetFine = { ...fine, newStatus };
            this.settleConfirmOpen = true;
        }
    }"
    class="space-y-6"
>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl">Overdue Penalties &amp; Fine Ledger</flux:heading>
                <flux:badge color="amber" size="sm">Fines Module</flux:badge>
            </div>
            <flux:subheading class="mt-1">
                {{ auth()->user()->isStaffOrAdmin()
                    ? 'Review automatically generated overdue penalties, issue manual fines, or mark balances as Paid / Waived.'
                    : 'View your fine statement for items returned past their designated due dates.' }}
            </flux:subheading>
        </div>

        @if (auth()->user()->isStaffOrAdmin())
            <flux:button type="button" wire:click="openIssueModal" variant="primary" size="sm" icon="plus">
                Issue Manual Fine
            </flux:button>
        @endif
    </div>

    <!-- Flash Banner -->
    @if (session()->has('status') || session()->has('error'))
        <div class="rounded-xl border px-4 py-3 text-sm {{ session()->has('error') ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}">
            {{ session('error') ?? session('status') }}
        </div>
    @endif

    <!-- Status Filter -->
    <div class="flex items-center justify-between bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Filter by Payment Status</span>
        <select
            wire:model.live="statusFilter"
            class="rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs font-medium"
        >
            <option value="">All Fines</option>
            <option value="unpaid">Unpaid</option>
            <option value="paid">Paid</option>
            <option value="waived">Waived</option>
        </select>
    </div>

    <!-- Fines Table -->
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900/60 text-xs uppercase text-zinc-500 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="py-3.5 px-4">Fine ID</th>
                        <th class="py-3.5 px-4">Loan ID</th>
                        <th class="py-3.5 px-4">Member</th>
                        <th class="py-3.5 px-4">Book Title</th>
                        <th class="py-3.5 px-4">Due Date</th>
                        <th class="py-3.5 px-4">Amount</th>
                        <th class="py-3.5 px-4">Status</th>
                        @if (auth()->user()->isStaffOrAdmin())
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                    @forelse ($fines as $fine)
                        <tr wire:key="fine-row-{{ $fine->fine_id }}">
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold">#FN-{{ $fine->fine_id }}</td>
                            <td class="py-3.5 px-4 font-mono text-xs text-zinc-500">#LN-{{ $fine->loan_id }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $fine->loan?->user?->name ?? 'N/A' }}</div>
                                <div class="text-xs text-zinc-500">{{ $fine->loan?->user?->memberCode() }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-medium">{{ $fine->loan?->book?->title ?? 'N/A' }}</td>
                            <td class="py-3.5 px-4 text-xs text-zinc-500">{{ $fine->loan?->due_date?->format('d M Y') }}</td>
                            <td class="py-3.5 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                RM {{ number_format((float) $fine->amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($fine->status === 'paid')
                                    <flux:badge color="emerald" size="sm">Paid</flux:badge>
                                @elseif ($fine->status === 'waived')
                                    <flux:badge color="zinc" size="sm">Waived</flux:badge>
                                @else
                                    <flux:badge color="red" size="sm">Unpaid</flux:badge>
                                @endif
                            </td>
                            @if (auth()->user()->isStaffOrAdmin())
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        @if ($fine->status === 'unpaid')
                                            <button
                                                type="button"
                                                x-on:click="promptSettle(@js(['id' => $fine->fine_id, 'amount' => number_format((float) $fine->amount, 2), 'member' => $fine->loan?->user?->name ?? 'Member']), 'paid')"
                                                class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold"
                                            >
                                                Mark Paid
                                            </button>
                                            <button
                                                type="button"
                                                x-on:click="promptSettle(@js(['id' => $fine->fine_id, 'amount' => number_format((float) $fine->amount, 2), 'member' => $fine->loan?->user?->name ?? 'Member']), 'waived')"
                                                class="px-2.5 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-600 text-xs font-semibold hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                            >
                                                Waive
                                            </button>
                                        @endif

                                        @if (auth()->user()->isAdmin())
                                            <flux:button
                                                wire:click="deleteFine({{ $fine->fine_id }})"
                                                variant="ghost"
                                                size="sm"
                                                class="text-red-600"
                                            >
                                                Delete
                                            </flux:button>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-xs text-zinc-500">No fine records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-700">
            {{ $fines->links() }}
        </div>
    </div>

    <!-- Livewire Issue Fine Modal -->
    @if ($showIssueModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
                <flux:heading size="lg">Issue Fine for Loan</flux:heading>

                <form wire:submit="issueFine" class="space-y-4">
                    <flux:field>
                        <flux:label>Associated Loan Record</flux:label>
                        <select
                            wire:model="loan_id"
                            required
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm"
                        >
                            <option value="">-- Select Loan --</option>
                            @foreach ($eligibleLoans as $loanItem)
                                <option value="{{ $loanItem->loan_id }}">
                                    #LN-{{ $loanItem->loan_id }} — {{ $loanItem->user?->name }} ({{ $loanItem->book?->title }})
                                </option>
                            @endforeach
                        </select>
                        <flux:error name="loan_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Fine Amount (RM)</flux:label>
                        <flux:input type="number" step="0.50" min="0.50" wire:model="amount" required />
                        <flux:error name="amount" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Status</flux:label>
                        <select
                            wire:model="status"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm"
                        >
                            <option value="unpaid">Unpaid</option>
                            <option value="paid">Paid</option>
                            <option value="waived">Waived</option>
                        </select>
                        <flux:error name="status" />
                    </flux:field>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <flux:button type="button" variant="ghost" size="sm" wire:click="$set('showIssueModal', false)">
                            Cancel
                        </flux:button>
                        <flux:button type="submit" variant="primary" size="sm">
                            Save Fine
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Alpine.js Fine Settlement Confirmation Modal (x-data, x-show, x-text) -->
    <div
        x-show="settleConfirmOpen"
        x-transition.opacity
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4"
    >
        <div
            x-on:click.away="settleConfirmOpen = false"
            class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4"
        >
            <flux:heading size="lg">Confirm Fine Settlement</flux:heading>
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                Mark fine of <strong>RM <span x-text="targetFine.amount"></span></strong>
                for <strong x-text="targetFine.member"></strong> as
                <span class="uppercase font-bold text-indigo-600" x-text="targetFine.newStatus"></span>?
            </p>
            <div class="flex items-center justify-end gap-2 pt-2">
                <flux:button type="button" variant="ghost" size="sm" x-on:click="settleConfirmOpen = false">
                    Cancel
                </flux:button>
                <flux:button
                    type="button"
                    variant="primary"
                    size="sm"
                    x-on:click="$wire.settleFine(targetFine.id, targetFine.newStatus); settleConfirmOpen = false"
                >
                    Confirm Update
                </flux:button>
            </div>
        </div>
    </div>
</div>
