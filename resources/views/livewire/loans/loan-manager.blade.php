<div
    x-data="{
        confirmReturnOpen: false,
        targetLoan: { id: null, title: '', member: '', due: '', isOverdue: false },
        promptReturn(loan) {
            this.targetLoan = loan;
            this.confirmReturnOpen = true;
        }
    }"
    class="space-y-6"
>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl">
                    {{ auth()->user()->isStaffOrAdmin() ? 'Staff Circulation Portal — Loans & Returns' : 'My Book Borrowings & Due Dates' }}
                </flux:heading>
                <flux:badge color="emerald" size="sm">Circulation Module</flux:badge>
            </div>
            <flux:subheading class="mt-1">
                {{ auth()->user()->isStaffOrAdmin()
                    ? 'Scan member IDs and book barcodes to process check-outs, returns, and automatic overdue fine generation.'
                    : 'View your borrowed items, due dates, and renew eligible active loans.' }}
            </flux:subheading>
        </div>
    </div>

    <!-- Flash Status Alert -->
    @if (session()->has('status') || session()->has('error'))
        <div class="rounded-xl border px-4 py-3 text-sm {{ session()->has('error') ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}">
            {{ session('error') ?? session('status') }}
        </div>
    @endif

    <!-- Staff / Admin Check-Out Desk (Matches Stage 1 Wireframe 3: Process Book Loan / Return) -->
    @if (auth()->user()->isStaffOrAdmin())
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-700 pb-4">
                <div>
                    <flux:heading size="lg">Process Book Loan / Check-Out Transaction</flux:heading>
                    <p class="text-xs text-zinc-500">Look up by Member ID (e.g. MBR-2026-0003) and Book Barcode (e.g. BK-0001) or select directly.</p>
                </div>
                <flux:badge color="indigo" size="sm">IssueLoanAction + LoanRequest</flux:badge>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Step 1: Member Lookup -->
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 space-y-3 bg-zinc-50/50 dark:bg-zinc-900/40">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500">1. Enter Member ID or Select Member</label>
                    <div class="flex gap-2">
                        <flux:input
                            type="text"
                            wire:model="memberLookup"
                            placeholder="MBR-2026-0003 or Ahmad..."
                            class="flex-1"
                        />
                        <flux:button type="button" wire:click="verifyMember" variant="subtle">
                            Verify Member
                        </flux:button>
                    </div>

                    <div>
                        <select
                            wire:model.live="selectedUserId"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm"
                        >
                            <option value="">-- Choose Verified Library Member --</option>
                            @foreach ($allMembers as $member)
                                <option value="{{ $member->user_id }}">
                                    {{ $member->memberCode() }} — {{ $member->name }} ({{ ucfirst($member->role) }})
                                </option>
                            @endforeach
                        </select>
                        <flux:error name="user_id" />
                    </div>

                    @if ($verifiedMember)
                        <div class="rounded-lg bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 px-3 py-2 text-xs text-emerald-800 dark:text-emerald-200">
                            <strong>Name:</strong> {{ $verifiedMember->name }} ({{ $verifiedMember->memberCode() }}) &bull;
                            <strong>Status:</strong> Active ({{ ucfirst($verifiedMember->role) }})
                        </div>
                    @endif
                </div>

                <!-- Step 2: Book Barcode Scan -->
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 space-y-3 bg-zinc-50/50 dark:bg-zinc-900/40">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500">2. Enter Book Barcode ID or Select Title</label>
                    <div class="flex gap-2">
                        <flux:input
                            type="text"
                            wire:model="bookLookup"
                            placeholder="BK-0001 or Clean Code..."
                            class="flex-1"
                        />
                        <flux:button type="button" wire:click="scanBook" variant="subtle">
                            Scan Book
                        </flux:button>
                    </div>

                    <div>
                        <select
                            wire:model.live="selectedBookId"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm"
                        >
                            <option value="">-- Choose Available Book Title --</option>
                            @foreach ($availableBooks as $bookItem)
                                <option value="{{ $bookItem->book_id }}">
                                    {{ $bookItem->bookCode() }} — {{ $bookItem->title }} (Available)
                                </option>
                            @endforeach
                        </select>
                        <flux:error name="book_id" />
                    </div>

                    @if ($scannedBook)
                        <div class="rounded-lg border px-3 py-2 text-xs {{ $scannedBook->availability ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-amber-50 border-amber-200 text-amber-800' }}">
                            <strong>Book Title:</strong> {{ $scannedBook->title }} ({{ $scannedBook->bookCode() }}) &bull;
                            <strong>Availability:</strong> {{ $scannedBook->availability ? 'Available' : 'Checked Out / Unavailable' }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Step 3: Dates & Confirm Loan Transaction -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end pt-2">
                <flux:field>
                    <flux:label>Borrowed Date</flux:label>
                    <flux:input type="date" wire:model="borrowed_date" />
                    <flux:error name="borrowed_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Designated Due Date</flux:label>
                    <flux:input type="date" wire:model="due_date" />
                    <flux:error name="due_date" />
                </flux:field>

                <flux:button type="button" wire:click="issueLoan" variant="primary" class="w-full">
                    Confirm Loan Transaction
                </flux:button>
            </div>
        </div>
    @endif

    <!-- Filter & Search Bar for Loan Records -->
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row gap-3 items-center justify-between">
        <div class="w-full sm:w-80">
            <flux:input
                type="text"
                wire:model.live.debounce.250ms="search"
                placeholder="Search loans by book title or member..."
                icon="magnifying-glass"
            />
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <select
                wire:model.live="statusFilter"
                class="rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-xs font-medium"
            >
                <option value="">All Loan Statuses</option>
                <option value="borrowed">Borrowed (Active)</option>
                <option value="overdue">Overdue</option>
                <option value="returned">Returned</option>
            </select>
        </div>
    </div>

    <!-- Circulation Loans Table -->
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900/60 text-xs uppercase text-zinc-500 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="py-3.5 px-4">Loan ID</th>
                        <th class="py-3.5 px-4">Member</th>
                        <th class="py-3.5 px-4">Book Title</th>
                        <th class="py-3.5 px-4">Borrowed</th>
                        <th class="py-3.5 px-4">Due Date</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Fine</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                    @forelse ($loans as $loan)
                        <tr wire:key="loan-row-{{ $loan->loan_id }}">
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold">#LN-{{ $loan->loan_id }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $loan->user?->name }}</div>
                                <div class="text-xs text-zinc-500">{{ $loan->user?->memberCode() }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('books.show', $loan->book_id) }}" class="font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $loan->book?->title }}
                                </a>
                                <div class="text-xs text-zinc-500">{{ $loan->book?->bookCode() }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-zinc-600 dark:text-zinc-400">
                                {{ $loan->borrowed_date?->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-xs font-medium {{ $loan->isOverdue() ? 'text-red-600 font-bold' : 'text-zinc-700 dark:text-zinc-300' }}">
                                {{ $loan->due_date?->format('d M Y') }}
                                @if ($loan->returned_date)
                                    <div class="text-[11px] text-emerald-600">Returned: {{ $loan->returned_date->format('d M Y') }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($loan->status === 'returned')
                                    <flux:badge color="emerald" size="sm">Returned</flux:badge>
                                @elseif ($loan->status === 'overdue' || $loan->isOverdue())
                                    <flux:badge color="red" size="sm">Overdue</flux:badge>
                                @else
                                    <flux:badge color="indigo" size="sm">Borrowed</flux:badge>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                @if ($loan->fine)
                                    <span class="font-semibold {{ $loan->fine->status === 'unpaid' ? 'text-red-600' : 'text-emerald-600' }}">
                                        RM {{ number_format((float) $loan->fine->amount, 2) }} ({{ ucfirst($loan->fine->status) }})
                                    </span>
                                @else
                                    <span class="text-zinc-400">RM 0.00</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    @if ($loan->status === 'borrowed')
                                        <flux:button
                                            wire:click="renewLoan({{ $loan->loan_id }})"
                                            variant="ghost"
                                            size="sm"
                                        >
                                            Renew
                                        </flux:button>
                                    @endif

                                    @if (auth()->user()->isStaffOrAdmin() && $loan->status !== 'returned')
                                        <button
                                            type="button"
                                            x-on:click="promptReturn(@js([
                                                'id' => $loan->loan_id,
                                                'title' => $loan->book?->title ?? 'Book',
                                                'member' => $loan->user?->name ?? 'Member',
                                                'due' => $loan->due_date?->format('d M Y'),
                                                'isOverdue' => $loan->isOverdue() || $loan->status === 'overdue',
                                            ]))"
                                            class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold"
                                        >
                                            Process Return
                                        </button>
                                    @endif

                                    @if (auth()->user()->isAdmin())
                                        <flux:button
                                            wire:click="deleteLoan({{ $loan->loan_id }})"
                                            variant="ghost"
                                            size="sm"
                                            class="text-red-600"
                                        >
                                            Delete
                                        </flux:button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-xs text-zinc-500">No loan records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-700">
            {{ $loans->links() }}
        </div>
    </div>

    <!-- Alpine.js Return & Auto-Fine Confirmation Modal (x-data, x-show, x-text) -->
    <div
        x-show="confirmReturnOpen"
        x-transition.opacity
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4"
    >
        <div
            x-on:click.away="confirmReturnOpen = false"
            class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4"
        >
            <flux:heading size="lg">Confirm Book Check-In / Return</flux:heading>
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                Process book return for <strong class="text-zinc-900 dark:text-zinc-100" x-text="targetLoan.title"></strong>
                borrowed by <strong x-text="targetLoan.member"></strong> (Due: <span x-text="targetLoan.due"></span>)?
            </p>

            <div
                x-show="targetLoan.isOverdue"
                class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800"
            >
                <strong>Automated Overdue Fine Notice:</strong> This item is past its designated due date.
                <code>ReturnLoanAction</code> will automatically calculate and record an overdue penalty (RM 1.00/day).
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <flux:button type="button" variant="ghost" size="sm" x-on:click="confirmReturnOpen = false">
                    Cancel
                </flux:button>
                <flux:button
                    type="button"
                    variant="primary"
                    size="sm"
                    x-on:click="$wire.returnLoan(targetLoan.id); confirmReturnOpen = false"
                >
                    Confirm Check-In
                </flux:button>
            </div>
        </div>
    </div>
</div>
