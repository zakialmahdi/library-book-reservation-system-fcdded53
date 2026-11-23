<div
    x-data="{
        deleteModalOpen: false,
        targetUser: { id: null, name: '', code: '' }
    }"
    class="space-y-6"
>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl">User &amp; Role Access Control Management</flux:heading>
                <flux:badge color="amber" size="sm">Admin Only</flux:badge>
            </div>
            <flux:subheading class="mt-1">
                Manage Member, Library Staff, and Admin accounts protected by UserPolicy and role middleware.
            </flux:subheading>
        </div>

        <flux:button type="button" wire:click="openCreateModal" variant="primary" size="sm" icon="user-plus">
            + Register New User
        </flux:button>
    </div>

    <!-- Flash Banner -->
    @if (session()->has('status') || session()->has('error'))
        <div class="rounded-xl border px-4 py-3 text-sm {{ session()->has('error') ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}">
            {{ session('error') ?? session('status') }}
        </div>
    @endif

    <!-- Search & Role Filter -->
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row gap-3 items-center justify-between">
        <div class="w-full sm:w-80">
            <flux:input
                type="text"
                wire:model.live.debounce.250ms="search"
                placeholder="Search users by name or email..."
                icon="magnifying-glass"
            />
        </div>

        <select
            wire:model.live="roleFilter"
            class="w-full sm:w-48 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-xs font-medium"
        >
            <option value="">All Roles</option>
            <option value="member">Member</option>
            <option value="staff">Library Staff</option>
            <option value="admin">Admin</option>
        </select>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900/60 text-xs uppercase text-zinc-500 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="py-3.5 px-4">Member ID</th>
                        <th class="py-3.5 px-4">Full Name</th>
                        <th class="py-3.5 px-4">Email Address</th>
                        <th class="py-3.5 px-4">Role</th>
                        <th class="py-3.5 px-4">Active Loans</th>
                        <th class="py-3.5 px-4">Reservations</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                    @forelse ($users as $u)
                        <tr wire:key="user-row-{{ $u->user_id }}">
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold">{{ $u->memberCode() }}</td>
                            <td class="py-3.5 px-4 font-semibold text-zinc-900 dark:text-zinc-100">
                                <a href="{{ route('users.show', $u->user_id) }}">
                                    {{ $u->name }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-zinc-500">{{ $u->email }}</td>
                            <td class="py-3.5 px-4">
                                @if ($u->role === 'admin')
                                    <flux:badge color="amber" size="sm">Admin</flux:badge>
                                @elseif ($u->role === 'staff')
                                    <flux:badge color="emerald" size="sm">Library Staff</flux:badge>
                                @else
                                    <flux:badge color="indigo" size="sm">Member</flux:badge>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs">{{ $u->active_loans_count }}</td>
                            <td class="py-3.5 px-4 text-xs">{{ $u->active_reservations_count }}</td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <flux:button
                                        type="button"
                                        wire:click="openEditModal({{ $u->user_id }})"
                                        variant="subtle"
                                        size="sm"
                                    >
                                        Edit Role
                                    </flux:button>

                                    @if ((int) auth()->user()->user_id !== (int) $u->user_id)
                                        <button
                                            type="button"
                                            x-on:click="targetUser = { id: {{ $u->user_id }}, name: @js($u->name), code: @js($u->memberCode()) }; deleteModalOpen = true"
                                            class="px-2.5 py-1.5 rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 text-xs font-semibold"
                                        >
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-xs text-zinc-500">No user accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-100 dark:border-zinc-700">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Create / Edit User Modal -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
                <flux:heading size="lg">
                    {{ $editingUserId ? 'Update User & Role' : 'Register New Library User' }}
                </flux:heading>

                <form wire:submit="saveUser" class="space-y-4">
                    <flux:field>
                        <flux:label>Full Name</flux:label>
                        <flux:input type="text" wire:model="name" required />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Email Address</flux:label>
                        <flux:input type="email" wire:model="email" required />
                        <flux:error name="email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>System Role</flux:label>
                        <select
                            wire:model="role"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm"
                        >
                            <option value="member">Member</option>
                            <option value="staff">Library Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                        <flux:error name="role" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Password {{ $editingUserId ? '(Leave blank to keep existing)' : '' }}</flux:label>
                        <flux:input type="password" wire:model="password" :required="!$editingUserId" placeholder="Minimum 8 characters" />
                        <flux:error name="password" />
                    </flux:field>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <flux:button type="button" variant="ghost" size="sm" wire:click="$set('showModal', false)">
                            Cancel
                        </flux:button>
                        <flux:button type="submit" variant="primary" size="sm">
                            {{ $editingUserId ? 'Save Changes' : 'Create Account' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Alpine.js Delete User Confirmation Modal -->
    <div
        x-show="deleteModalOpen"
        x-transition.opacity
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4"
    >
        <div
            x-on:click.away="deleteModalOpen = false"
            class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4"
        >
            <flux:heading size="lg">Delete User Account?</flux:heading>
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                Permanently remove user <strong class="text-zinc-900 dark:text-zinc-100" x-text="targetUser.name"></strong>
                (<span class="font-mono text-xs" x-text="targetUser.code"></span>)?
            </p>
            <div class="flex items-center justify-end gap-2 pt-2">
                <flux:button type="button" variant="ghost" size="sm" x-on:click="deleteModalOpen = false">
                    Cancel
                </flux:button>
                <flux:button
                    type="button"
                    variant="danger"
                    size="sm"
                    x-on:click="$wire.deleteUser(targetUser.id); deleteModalOpen = false"
                >
                    Confirm Delete
                </flux:button>
            </div>
        </div>
    </div>
</div>
