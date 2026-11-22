<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'publisher' => fake()->company(),
            'publication_year' => (int) fake()->numberBetween(2005, 2026),
            'description' => fake()->paragraph(3),
            'page_count' => fake()->numberBetween(120, 750),
            'genre_id' => Genre::factory(),
            'book_type' => fake()->randomElement(['Hardcover', 'Paperback', 'Reference', 'Technical Manual']),
            'availability' => true,
        ];
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability' => false,
        ]);
    }
}
