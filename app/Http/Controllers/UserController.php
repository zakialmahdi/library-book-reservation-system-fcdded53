<?php

namespace App\Http\Controllers;

use App\Actions\Users\CreateUserAction;
use App\Actions\Users\DeleteUserAction;
use App\Actions\Users\UpdateUserAction;
use App\Http\Requests\Users\UserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', User::class);

        return view('users.index');
    }

    public function create()
    {
        Gate::authorize('create', User::class);

        return view('users.index', ['openCreate' => true]);
    }

    public function store(UserRequest $request, CreateUserAction $action)
    {
        Gate::authorize('create', User::class);

        $action->execute($request->validated());

        return redirect()->route('users.index')
            ->with('success', 'User account created successfully.');
    }

    public function show(User $user)
    {
        Gate::authorize('view', $user);

        $user->load(['loans.book', 'reservations.book', 'fines']);

        return view('users.show', ['profileUser' => $user]);
    }

    public function edit(User $user)
    {
        Gate::authorize('update', $user);

        return view('users.index', ['editUserId' => $user->user_id]);
    }

    public function update(UserRequest $request, User $user, UpdateUserAction $action)
    {
        Gate::authorize('update', $user);

        $validated = $request->validated();
        if (! $request->user()->isAdmin()) {
            $validated['role'] = $user->role;
        }

        $action->execute($user, $validated);

        return redirect()->back()
            ->with('success', 'User profile updated successfully.');
    }

    public function destroy(User $user, DeleteUserAction $action)
    {
        Gate::authorize('delete', $user);

        $action->execute($user);

        return redirect()->route('users.index')
            ->with('success', 'User account deleted.');
    }
}
