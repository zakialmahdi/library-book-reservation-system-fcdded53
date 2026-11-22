<?php

namespace App\Http\Requests\Reservations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(self::sanitize($this->all()));
    }

    public static function sanitize(array $data): array
    {
        if (isset($data['status'])) {
            $data['status'] = strtolower(strip_tags(trim((string) $data['status'])));
        }

        return $data;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer', Rule::exists('users', 'user_id')],
            'book_id' => ['required', 'integer', Rule::exists('books', 'book_id')],
            'reservation_date' => ['sometimes', 'date'],
            'status' => ['sometimes', 'string', Rule::in(['pending', 'queued', 'ready', 'fulfilled', 'cancelled'])],
        ];
    }

    public static function validatePayload(array $data): array
    {
        $sanitized = self::sanitize($data);
        $instance = new self;

        return Validator::make($sanitized, $instance->rules())->validate();
    }
}
