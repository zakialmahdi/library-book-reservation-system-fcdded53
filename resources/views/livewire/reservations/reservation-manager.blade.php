<div
    x-data="{
        showNewReservationForm: false,
        confirmCancelOpen: false,
        selectedRes: { id: null, title: '' }
    }"
    class="space-y-6"
>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl">Book Reservations &amp; Priority Waitlist Queue</flux:heading>
                <flux:badge color="indigo" size="sm">Reservation Module</flux:badge>
            </div>
            <flux:subheading class="mt-1">
                Place reservations on available books or join the automated waitlist queue for books currently on loan.
            </flux:subheading>
        </div>

        <flux:button
            type="button"
            variant="primary"
            size="sm"
            icon="plus"
            x-on:click="showNewReservationForm = !showNewReservationForm"
        >
            Place New Reservation
        </flux:button>
    </div>

    <!-- Flash Alert -->
    @if (session()->has('status') || session()->has('error'))
        <div class="rounded-xl border px-4 py-3 text-sm {{ session()->has('error') ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}">
            {{ session('error') ?? session('status') }}
        </div>
    @endif

    <!-- Alpine.js Collapsible New Reservation Form -->
    <div
        x-show="showNewReservationForm"
        x-transition
        style="display: none;"
        class="bg-white dark:bg-zinc-800 border border-indigo-200 dark:border-indigo-800 rounded-2xl p-6 shadow-sm space-y-4"
    >
        <flux:heading size="lg">Select a Book to Reserve or Join Queue</flux:heading>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div class="sm:col-span-2">
                <flux:field>
                    <flux:label>Book Title</flux:label>
                    <select
                        wire:model="selectedBookId"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm"
                    >
                        <option value="">-- Select a Book from Catalogue --</option>
                        @foreach ($books as $book)
                            <option value="{{ $book->book_id }}">
                                {{ $book->bookCode() }} — {{ $book->title }} ({{ $book->availability ? 'Available -> Reserve' : 'On Loan -> Join Queue' }})
                            </option>
                        @endforeach
                    </select>
                    <flux:error name="book_id" />
                </flux:field>
            </div>

            <div class="flex gap-2">
                <flux:button type="button" wire:click="placeReservation" variant="primary" class="flex-1">
                    Submit Reservation
                </flux:button>
                <flux:button type="button" variant="ghost" x-on:click="showNewReservationForm = false">
                    Close
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Status Filter -->
    <div class="flex items-center justify-between bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Filter Reservations by Status</span>
        <select
            wire:model.live="statusFilter"
            class="rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs font-medium"
        >
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="queued">Queued (Waitlist)</option>
            <option value="ready">Ready for Pickup</option>
            <option value="fulfilled">Fulfilled (Converted to Loan)</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <!-- Reservations Table -->
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900/60 text-xs uppercase text-zinc-500 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="py-3.5 px-4">ID</th>
                        <th class="py-3.5 px-4">Member</th>
                        <th class="py-3.5 px-4">Book Title</th>
                        <th class="py-3.5 px-4">Reservation Date</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                    @forelse ($reservations as $res)
                        <tr wire:key="res-row-{{ $res->reservation_id }}">
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold">#RES-{{ $res->reservation_id }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $res->user?->name }}</div>
                                <div class="text-xs text-zinc-500">{{ $res->user?->memberCode() }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('books.show', $res->book_id) }}" class="font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $res->book?->title }}
                                </a>
                                <div class="text-xs text-zinc-500">{{ $res->book?->bookCode() }} &bull; {{ $res->book?->availability ? 'In Stock' : 'Checked Out' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-zinc-500">
                                {{ $res->reservation_date?->format('d M Y, h:i A') }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($res->status === 'ready')
                                    <flux:badge color="emerald" size="sm">Ready for Pickup</flux:badge>
                                @elseif ($res->status === 'queued')
                                    <flux:badge color="amber" size="sm">Queued (Waitlist)</flux:badge>
                                @elseif ($res->status === 'pending')
                                    <flux:badge color="indigo" size="sm">Pending</flux:badge>
                                @elseif ($res->status === 'fulfilled')
                                    <flux:badge color="zinc" size="sm">Fulfilled</flux:badge>
                                @else
                                    <flux:badge color="red" size="sm">Cancelled</flux:badge>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    @if (auth()->user()->isStaffOrAdmin())
                                        @if (in_array($res->status, ['pending', 'queued'], true))
                                            <flux:button
                                                wire:click="updateStatus({{ $res->reservation_id }}, 'ready')"
                                                variant="subtle"
                                                size="sm"
                                            >
                                                Mark Ready
                                            </flux:button>
                                        @endif

                                        @if (in_array($res->status, ['pending', 'ready'], true) && $res->book?->availability)
                                            <flux:button
                                                wire:click="fulfillToLoan({{ $res->reservation_id }})"
                                                variant="primary"
                                                size="sm"
                                            >
                                                Issue Loan
                                            </flux:button>
                                        @endif
                                    @endif

                                    @if (in_array($res->status, ['pending', 'queued', 'ready'], true))
                                        <button
                                            type="button"
                                            x-on:click="selectedRes = { id: {{ $res->reservation_id }}, title: @js($res->book?->title ?? 'Book') }; confirmCancelOpen = true"
                                            class="px-2.5 py-1.5 rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 text-xs font-semibold"
                                        >
                                            Cancel
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-xs text-zinc-500">No reservations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-700">
            {{ $reservations->links() }}
        </div>
    </div>

    <!-- Alpine.js Cancellation Confirmation Modal -->
    <div
        x-show="confirmCancelOpen"
        x-transition.opacity
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4"
    >
        <div
            x-on:click.away="confirmCancelOpen = false"
            class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4"
        >
            <flux:heading size="lg">Confirm Reservation Cancellation</flux:heading>
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                Cancel reservation for <strong class="text-zinc-900 dark:text-zinc-100" x-text="selectedRes.title"></strong>?
            </p>
            <div class="flex items-center justify-end gap-2 pt-2">
                <flux:button type="button" variant="ghost" size="sm" x-on:click="confirmCancelOpen = false">
                    Keep
                </flux:button>
                <flux:button
                    type="button"
                    variant="danger"
                    size="sm"
                    x-on:click="$wire.cancelReservation(selectedRes.id); confirmCancelOpen = false"
                >
                    Confirm Cancel
                </flux:button>
            </div>
        </div>
    </div>
</div>
