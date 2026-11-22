<?php

namespace App\Http\Requests\Books;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
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
        foreach (['title', 'author', 'publisher', 'description', 'book_type'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = strip_tags(trim((string) $data[$field]));
            }
        }

        if (isset($data['availability'])) {
            $data['availability'] = filter_var($data['availability'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $data['availability'];
        }

        return $data;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'author' => ['required', 'string', 'min:2', 'max:255'],
            'publisher' => ['required', 'string', 'min:2', 'max:255'],
            'publication_year' => ['required', 'integer', 'min:1901', 'max:'.(date('Y') + 1)],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'page_count' => ['required', 'integer', 'min:1', 'max:10000'],
            'genre_id' => ['required', 'integer', Rule::exists('genres', 'genre_id')],
            'book_type' => ['required', 'string', 'max:50'],
            'availability' => ['sometimes', 'boolean'],
        ];
    }

    public static function validatePayload(array $data): array
    {
        $sanitized = self::sanitize($data);
        $instance = new self;

        return Validator::make($sanitized, $instance->rules())->validate();
    }
}
