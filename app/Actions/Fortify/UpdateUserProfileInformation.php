<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        $sanitized = [
            'name' => strip_tags(trim((string) ($input['name'] ?? ''))),
            'email' => strtolower(strip_tags(trim((string) ($input['email'] ?? '')))),
        ];

        Validator::make($sanitized, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->user_id, 'user_id'),
            ],
        ])->validateWithBag('updateProfileInformation');

        $user->forceFill([
            'name' => $sanitized['name'],
            'email' => $sanitized['email'],
        ])->save();
    }
}
