<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
{
    protected $model = Loan::class;

    public function definition(): array
    {
        $borrowed = now()->subDays(5);

        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory()->unavailable(),
            'borrowed_date' => $borrowed->toDateString(),
            'due_date' => $borrowed->copy()->addDays(14)->toDateString(),
            'returned_date' => null,
            'status' => 'borrowed',
        ];
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'borrowed_date' => now()->subDays(25)->toDateString(),
            'due_date' => now()->subDays(11)->toDateString(),
            'returned_date' => null,
            'status' => 'overdue',
        ]);
    }

    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'borrowed_date' => now()->subDays(20)->toDateString(),
            'due_date' => now()->subDays(6)->toDateString(),
            'returned_date' => now()->subDays(7)->toDateString(),
            'status' => 'returned',
        ]);
    }
}
