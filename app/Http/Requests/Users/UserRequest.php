<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $authUser = $this->user();
        if (! $authUser) {
            return false;
        }

        $routeUser = $this->route('user');
        $targetId = is_object($routeUser) ? $routeUser->user_id : (int) $routeUser;

        return $authUser->isAdmin() || ($targetId > 0 && (int) $authUser->user_id === $targetId);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(self::sanitize($this->all()));
    }

    public static function sanitize(array $data): array
    {
        if (isset($data['name'])) {
            $data['name'] = strip_tags(trim((string) $data['name']));
        }

        if (isset($data['email'])) {
            $data['email'] = strtolower(strip_tags(trim((string) $data['email'])));
        }

        if (isset($data['role'])) {
            $data['role'] = strtolower(strip_tags(trim((string) $data['role'])));
        }

        return $data;
    }

    public function rules(?int $ignoreUserId = null, bool $requirePassword = true): array
    {
        $routeUser = $this->route('user');
        $userId = $ignoreUserId ?? (is_object($routeUser) ? $routeUser->user_id : ($routeUser ? (int) $routeUser : null));
        $isCreating = $userId === null && $requirePassword;

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId, 'user_id'),
            ],
            'role' => ['required', 'string', Rule::in(['member', 'staff', 'admin'])],
            'password' => $isCreating
                ? ['required', 'string', 'min:8']
                : ['nullable', 'string', 'min:8'],
        ];
    }

    public static function validatePayload(array $data, ?int $ignoreUserId = null, bool $requirePassword = true): array
    {
        $sanitized = self::sanitize($data);
        $instance = new self;

        return Validator::make($sanitized, $instance->rules($ignoreUserId, $requirePassword))->validate();
    }
}
