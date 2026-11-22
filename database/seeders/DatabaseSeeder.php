<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Book;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Generates 4 categories and 5 books per category (20 total books)
        Category::factory(4)
            ->has(Book::factory()->count(5))
            ->create();
    }
}
