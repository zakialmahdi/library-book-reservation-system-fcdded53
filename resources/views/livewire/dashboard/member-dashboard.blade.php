<div
    x-data="{
        activeTab: 'overview',
        cancelModalOpen: false,
        targetReservation: { id: null, title: '' },
        promptCancel(id, title) {
            this.targetReservation = { id, title };
            this.cancelModalOpen = true;
        }
    }"
    class="space-y-6"
>
    <!-- Welcome Banner & Quick Stats (matches Stage 1 Wireframe 1) -->
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <flux:heading size="xl">Welcome Back, {{ $user->name }}!</flux:heading>
                    <flux:badge color="indigo" size="sm">{{ $user->memberCode() }}</flux:badge>
                    <flux:badge color="zinc" size="sm">{{ ucfirst($user->role) }}</flux:badge>
                </div>
                <flux:subheading class="mt-1">
                    Track your active borrowings, reservation pickup statuses, and fine statements.
                </flux:subheading>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <flux:button href="{{ route('books.index') }}" variant="primary" size="sm" icon="magnifying-glass">
                    Browse Catalogue
                </flux:button>
                @if ($user->isStaffOrAdmin())
                    <flux:button href="{{ route('loans.index') }}" variant="subtle" size="sm">
                        Staff Circulation Desk
                    </flux:button>
                    <flux:button href="{{ route('admin.books') }}" variant="subtle" size="sm">
                        Inventory CRUD
                    </flux:button>
                @endif
            </div>
        </div>

        <!-- Quick Stats Bar (Stage 1 Wireframe 1: Active Loans | Pending Reservation | Fines) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
            <div class="rounded-xl border border-indigo-100 dark:border-indigo-900/50 bg-indigo-50/60 dark:bg-indigo-950/30 p-4 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Active Loans</span>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ $activeLoans->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center">
                    <flux:icon.book-open class="size-5" />
                </div>
            </div>

            <div class="rounded-xl border border-amber-100 dark:border-amber-900/50 bg-amber-50/60 dark:bg-amber-950/30 p-4 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Pending / Ready Reservations</span>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ $currentReservations->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-600/10 text-amber-600 flex items-center justify-center">
                    <flux:icon.clock class="size-5" />
                </div>
            </div>

            <div class="rounded-xl border {{ $unpaidFineTotal > 0 ? 'border-red-200 bg-red-50/60 dark:border-red-900/50 dark:bg-red-950/30' : 'border-emerald-100 bg-emerald-50/60 dark:border-emerald-900/50 dark:bg-emerald-950/30' }} p-4 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider {{ $unpaidFineTotal > 0 ? 'text-red-600' : 'text-emerald-600' }}">Outstanding Fines</span>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">RM {{ number_format((float) $unpaidFineTotal, 2) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl {{ $unpaidFineTotal > 0 ? 'bg-red-600/10 text-red-600' : 'bg-emerald-600/10 text-emerald-600' }} flex items-center justify-center">
                    <flux:icon.banknotes class="size-5" />
                </div>
            </div>
        </div>
    </div>

    <!-- Staff / Admin Circulation Summary Cards -->
    @if ($systemStats)
        <div class="bg-gradient-to-r from-indigo-900 to-slate-900 text-white rounded-2xl p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                <div>
                    <h3 class="text-base font-bold">Library-Wide Circulation Overview ({{ ucfirst($user->role) }} Access)</h3>
                    <p class="text-xs text-indigo-200">Real-time system metrics across all library inventory and members</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('loans.index') }}" class="px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 text-xs font-semibold">Process Check-out / Return</a>
                    @if($user->isAdmin())
                        <a href="{{ route('users.index') }}" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-semibold">Manage Users</a>
                    @endif
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 text-center">
                <div class="bg-white/10 rounded-xl p-3">
                    <span class="text-xs text-indigo-200 block">Total Titles</span>
                    <span class="text-lg font-bold">{{ $systemStats['total_books'] }}</span>
                </div>
                <div class="bg-white/10 rounded-xl p-3">
                    <span class="text-xs text-indigo-200 block">Available</span>
                    <span class="text-lg font-bold text-emerald-300">{{ $systemStats['available_books'] }}</span>
                </div>
                <div class="bg-white/10 rounded-xl p-3">
                    <span class="text-xs text-indigo-200 block">Active Loans</span>
                    <span class="text-lg font-bold text-amber-300">{{ $systemStats['active_loans'] }}</span>
                </div>
                <div class="bg-white/10 rounded-xl p-3">
                    <span class="text-xs text-indigo-200 block">Reservations</span>
                    <span class="text-lg font-bold">{{ $systemStats['pending_reservations'] }}</span>
                </div>
                <div class="bg-white/10 rounded-xl p-3">
                    <span class="text-xs text-indigo-200 block">Unpaid Fines</span>
                    <span class="text-lg font-bold text-rose-300">RM {{ number_format((float) $systemStats['unpaid_fines_rm'], 2) }}</span>
                </div>
                <div class="bg-white/10 rounded-xl p-3">
                    <span class="text-xs text-indigo-200 block">Registered Users</span>
                    <span class="text-lg font-bold">{{ $systemStats['total_members'] }}</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Livewire Flash Feedback -->
    @if (session()->has('status') || session()->has('error'))
        <div class="rounded-xl border px-4 py-3 text-sm {{ session()->has('error') ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}">
            {{ session('error') ?? session('status') }}
        </div>
    @endif

    <!-- Alpine.js Dynamic Tabs Navigation -->
    <div class="flex flex-wrap gap-2 border-b border-zinc-200 dark:border-zinc-700 pb-3">
        <button
            type="button"
            x-on:click="activeTab = 'overview'"
            :class="activeTab === 'overview' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100'"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition shadow-2xs"
        >
            Active Loans &amp; Reservations
        </button>
        <button
            type="button"
            x-on:click="activeTab = 'fines'"
            :class="activeTab === 'fines' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100'"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition shadow-2xs"
        >
            Fine History ({{ $userFines->count() }})
        </button>
        <button
            type="button"
            x-on:click="activeTab = 'profile'"
            :class="activeTab === 'profile' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100'"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition shadow-2xs"
        >
            Profile &amp; Security Settings
        </button>
    </div>

    <!-- Tab 1: Active Loans & Current Reservations (Stage 1 Wireframe 1) -->
    <div x-show="activeTab === 'overview'" x-transition class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Active Loans Card -->
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Active Loans</flux:heading>
                <a href="{{ route('loans.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">View All Loans &rarr;</a>
            </div>

            <div class="divide-y divide-zinc-100 dark:divide-zinc-700">
                @forelse ($activeLoans as $loan)
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <a href="{{ route('books.show', $loan->book_id) }}" class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 hover:text-indigo-600">
                                {{ $loan->book?->title }}
                            </a>
                            <div class="text-xs text-zinc-500 mt-1 flex flex-wrap items-center gap-2">
                                <span>Borrowed: {{ $loan->borrowed_date?->format('d M Y') }}</span>
                                <span>&bull;</span>
                                <span class="{{ $loan->status === 'overdue' ? 'text-red-600 font-semibold' : '' }}">
                                    Due: {{ $loan->due_date?->format('d M Y') }}
                                </span>
                                @if ($loan->status === 'overdue')
                                    <flux:badge color="red" size="sm">Overdue</flux:badge>
                                @else
                                    <flux:badge color="emerald" size="sm">Borrowed</flux:badge>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if ($loan->status === 'borrowed')
                                <flux:button
                                    wire:click="renewLoan({{ $loan->loan_id }})"
                                    variant="subtle"
                                    size="sm"
                                >
                                    Renew (+7d)
                                </flux:button>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-xs text-zinc-500 text-center">You have no active book loans right now.</p>
                @endforelse
            </div>
        </div>

        <!-- Current Reservations Card -->
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Current Reservations</flux:heading>
                <a href="{{ route('reservations.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">Manage Queue &rarr;</a>
            </div>

            <div class="divide-y divide-zinc-100 dark:divide-zinc-700">
                @forelse ($currentReservations as $reservation)
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <a href="{{ route('books.show', $reservation->book_id) }}" class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 hover:text-indigo-600">
                                {{ $reservation->book?->title }}
                            </a>
                            <div class="text-xs text-zinc-500 mt-1 flex flex-wrap items-center gap-2">
                                <span>Reserved: {{ $reservation->reservation_date?->format('d M Y, h:i A') }}</span>
                                @if ($reservation->status === 'ready')
                                    <flux:badge color="emerald" size="sm">Stats: Ready for Pickup</flux:badge>
                                @elseif ($reservation->status === 'queued')
                                    <flux:badge color="amber" size="sm">Stats: In Waitlist Queue</flux:badge>
                                @else
                                    <flux:badge color="indigo" size="sm">Stats: Pending Confirmation</flux:badge>
                                @endif
                            </div>
                        </div>

                        <div>
                            <button
                                type="button"
                                x-on:click="promptCancel({{ $reservation->reservation_id }}, @js($reservation->book?->title ?? 'Book'))"
                                class="px-3 py-1.5 rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 text-xs font-semibold"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-xs text-zinc-500 text-center">You have no active reservations or queued books.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Tab 2: Fine History -->
    <div x-show="activeTab === 'fines'" x-transition style="display: none;" class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <flux:heading size="lg">Overdue Penalties &amp; Fine History</flux:heading>
            <a href="{{ route('fines.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">Full Statement &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-zinc-500 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="py-2.5 px-3">Fine ID</th>
                        <th class="py-2.5 px-3">Book Title</th>
                        <th class="py-2.5 px-3">Due Date</th>
                        <th class="py-2.5 px-3">Amount</th>
                        <th class="py-2.5 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                    @forelse ($userFines as $fine)
                        <tr>
                            <td class="py-3 px-3 font-mono text-xs">#FN-{{ $fine->fine_id }}</td>
                            <td class="py-3 px-3 font-medium">{{ $fine->loan?->book?->title ?? 'Loan #'.$fine->loan_id }}</td>
                            <td class="py-3 px-3 text-xs text-zinc-500">{{ $fine->loan?->due_date?->format('d M Y') }}</td>
                            <td class="py-3 px-3 font-semibold">RM {{ number_format((float) $fine->amount, 2) }}</td>
                            <td class="py-3 px-3">
                                @if ($fine->status === 'paid')
                                    <flux:badge color="emerald" size="sm">Paid</flux:badge>
                                @elseif ($fine->status === 'waived')
                                    <flux:badge color="zinc" size="sm">Waived</flux:badge>
                                @else
                                    <flux:badge color="red" size="sm">Unpaid</flux:badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-xs text-zinc-500">No fines recorded on your account.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 3: Profile & Fortify Security Settings -->
    <div x-show="activeTab === 'profile'" x-transition style="display: none;" class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm max-w-xl">
        <flux:heading size="lg">Update Profile Information</flux:heading>
        <flux:subheading class="mt-1">Validated and sanitized through Fortify's UpdateUserProfileInformation action.</flux:subheading>

        <form wire:submit="updateProfile" class="space-y-4 mt-5">
            <flux:field>
                <flux:label>Full Name</flux:label>
                <flux:input type="text" wire:model="profileName" required />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Email Address</flux:label>
                <flux:input type="email" wire:model="profileEmail" required />
                <flux:error name="email" />
            </flux:field>

            <flux:button type="submit" variant="primary" size="sm">
                Save Profile Changes
            </flux:button>
        </form>
    </div>

    <!-- Alpine.js Cancellation Confirmation Modal (with x-text XSS protection) -->
    <div
        x-show="cancelModalOpen"
        x-transition.opacity
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4"
    >
        <div
            x-on:click.away="cancelModalOpen = false"
            class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4"
        >
            <flux:heading size="lg">Cancel Reservation?</flux:heading>
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                Are you sure you want to cancel your reservation for
                <strong class="text-zinc-900 dark:text-zinc-100" x-text="targetReservation.title"></strong>?
            </p>
            <div class="flex items-center justify-end gap-2 pt-2">
                <flux:button type="button" variant="ghost" size="sm" x-on:click="cancelModalOpen = false">
                    Keep Reservation
                </flux:button>
                <flux:button
                    type="button"
                    variant="danger"
                    size="sm"
                    x-on:click="$wire.cancelReservation(targetReservation.id); cancelModalOpen = false"
                >
                    Confirm Cancel
                </flux:button>
            </div>
        </div>
    </div>
</div>
