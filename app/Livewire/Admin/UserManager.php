<?php

namespace App\Livewire\Admin;

use App\Actions\Users\CreateUserAction;
use App\Actions\Users\DeleteUserAction;
use App\Actions\Users\UpdateUserAction;
use App\Http\Requests\Users\UserRequest;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = '';

    public ?int $editingUserId = null;
    public string $name = '';
    public string $email = '';
    public string $role = 'member';
    public string $password = '';
    public bool $showModal = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->authorize('create', User::class);
        $this->reset(['editingUserId', 'name', 'email', 'password']);
        $this->role = 'member';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEditModal(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->authorize('update', $user);

        $this->editingUserId = $user->user_id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function saveUser(CreateUserAction $createAction, UpdateUserAction $updateAction): void
    {
        $payload = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'password' => $this->password ?: null,
        ];

        $validated = UserRequest::validatePayload(
            $payload,
            $this->editingUserId,
            $this->editingUserId === null
        );

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $this->authorize('update', $user);
            $updateAction->execute($user, $validated);
            session()->flash('status', "Updated user account for {$user->name} ({$user->role}).");
        } else {
            $this->authorize('create', User::class);
            $user = $createAction->execute($validated);
            session()->flash('status', "Registered new {$user->role} account for {$user->name} ({$user->memberCode()}).");
        }

        $this->reset(['editingUserId', 'name', 'email', 'password', 'showModal']);
        $this->role = 'member';
    }

    public function deleteUser(int $userId, DeleteUserAction $action): void
    {
        $user = User::findOrFail($userId);
        $this->authorize('delete', $user);

        $name = $user->name;
        $action->execute($user);
        session()->flash('status', "Deleted user account \"{$name}\".");
    }

    public function render()
    {
        $this->authorize('viewAny', User::class);

        $cleanSearch = strip_tags(trim($this->search));

        $users = User::query()
            ->withCount([
                'loans as active_loans_count' => fn ($q) => $q->whereIn('status', ['borrowed', 'overdue']),
                'reservations as active_reservations_count' => fn ($q) => $q->whereIn('status', ['pending', 'queued', 'ready']),
            ])
            ->when($cleanSearch !== '', function ($q) use ($cleanSearch) {
                $q->where(function ($sub) use ($cleanSearch) {
                    $sub->where('name', 'like', "%{$cleanSearch}%")
                        ->orWhere('email', 'like', "%{$cleanSearch}%");
                });
            })
            ->when($this->roleFilter !== '', fn ($q) => $q->where('role', $this->roleFilter))
            ->orderBy('user_id')
            ->paginate(10);

        return view('livewire.admin.user-manager', [
            'users' => $users,
        ]);
    }
}
