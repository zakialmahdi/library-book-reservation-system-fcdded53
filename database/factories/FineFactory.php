<?php

namespace Database\Factories;

use App\Models\Fine;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fine>
 */
class FineFactory extends Factory
{
    protected $model = Fine::class;

    public function definition(): array
    {
        return [
            'loan_id' => Loan::factory()->overdue(),
            'amount' => fake()->randomFloat(2, 2.00, 30.00),
            'status' => 'unpaid',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    public function waived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'waived',
        ]);
    }
}
