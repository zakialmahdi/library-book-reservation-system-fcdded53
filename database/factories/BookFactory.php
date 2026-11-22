<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'title' => ucWords($this->faker->words(3,true)),
            'author' => $this->faker->name(),
            'price' => $this->faker->randomFloat(2, 15, 99),
            'is_featured' => $this->faker->boolean(30), // 30% chance of being featured
        ];
    }
}
