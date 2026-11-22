<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $sanitized = [
            'name' => strip_tags(trim((string) ($input['name'] ?? ''))),
            'email' => strtolower(strip_tags(trim((string) ($input['email'] ?? '')))),
            'password' => $input['password'] ?? '',
            'password_confirmation' => $input['password_confirmation'] ?? '',
        ];

        Validator::make($sanitized, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class, 'email'),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'name' => $sanitized['name'],
            'email' => $sanitized['email'],
            'password' => Hash::make($sanitized['password']),
            'role' => 'member',
        ]);
    }
}
