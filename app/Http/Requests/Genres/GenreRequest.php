<?php

namespace App\Http\Requests\Genres;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GenreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
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

        return $data;
    }

    public function rules(?int $ignoreGenreId = null): array
    {
        $routeGenre = $this->route('genre');
        $genreId = $ignoreGenreId ?? (is_object($routeGenre) ? $routeGenre->genre_id : $routeGenre);

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('genres', 'name')->ignore($genreId, 'genre_id'),
            ],
        ];
    }

    public static function validatePayload(array $data, ?int $ignoreGenreId = null): array
    {
        $sanitized = self::sanitize($data);
        $instance = new self;

        return Validator::make($sanitized, $instance->rules($ignoreGenreId))->validate();
    }
}
