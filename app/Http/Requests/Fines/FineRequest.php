<?php

namespace App\Http\Requests\Fines;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FineRequest extends FormRequest
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
            'loan_id' => ['required', 'integer', Rule::exists('loans', 'loan_id')],
            'amount' => ['required', 'numeric', 'min:0.50', 'max:9999.99'],
            'status' => ['required', 'string', Rule::in(['unpaid', 'paid', 'waived'])],
        ];
    }

    public static function validatePayload(array $data): array
    {
        $sanitized = self::sanitize($data);
        $instance = new self;

        return Validator::make($sanitized, $instance->rules())->validate();
    }
}
