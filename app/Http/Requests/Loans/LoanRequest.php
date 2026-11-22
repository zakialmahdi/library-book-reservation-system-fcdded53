<?php

namespace App\Http\Requests\Loans;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isStaffOrAdmin();
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
            'user_id' => ['required', 'integer', Rule::exists('users', 'user_id')],
            'book_id' => ['required', 'integer', Rule::exists('books', 'book_id')],
            'borrowed_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:borrowed_date'],
            'status' => ['sometimes', 'string', Rule::in(['borrowed', 'returned', 'overdue'])],
        ];
    }

    public static function validatePayload(array $data): array
    {
        $sanitized = self::sanitize($data);
        $instance = new self;

        return Validator::make($sanitized, $instance->rules())->validate();
    }
}
