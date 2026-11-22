<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    public function execute(array $validated): User
    {
        return User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'] ?? 'member',
            'password' => Hash::make($validated['password'] ?? 'password'),
            'email_verified_at' => now(),
        ]);
    }
}
