<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Fine;
use App\Models\Genre;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        // 1. Seed Multi-Role Users (Admin, Library Staff, Members)
        $admin = User::updateOrCreate(
            ['email' => 'admin@booknest.test'],
            [
                'name' => 'Muhammad Airul Haikal (Admin)',
                'password' => $defaultPassword,
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $staff = User::updateOrCreate(
            ['email' => 'staff@booknest.test'],
            [
                'name' => 'Zaki Al Mahdi (Library Staff)',
                'password' => $defaultPassword,
                'role' => 'staff',
                'email_verified_at' => now(),
            ]
        );

        $member1 = User::updateOrCreate(
            ['email' => 'member@booknest.test'],
            [
                'name' => 'Ahmad Muizzuddin (Member)',
                'password' => $defaultPassword,
                'role' => 'member',
                'email_verified_at' => now(),
            ]
        );

        $member2 = User::updateOrCreate(
            ['email' => 'azhan@booknest.test'],
            [
                'name' => 'Muhammad Azhan (Member)',
                'password' => $defaultPassword,
                'role' => 'member',
                'email_verified_at' => now(),
            ]
        );

        if (User::count() < 8) {
            $extraMembers = User::factory(4)->member()->create();
        } else {
            $extraMembers = User::where('role', 'member')->get();
        }

        // 2. Seed Genres
        $genreNames = [
            'Software Engineering',
            'Web Development',
            'Database Systems',
            'Cybersecurity & Ethics',
            'Artificial Intelligence',
            'Family & Animation',
            'Computer Networks',
        ];

        $genres = collect($genreNames)->mapWithKeys(function (string $name) {
            $genre = Genre::firstOrCreate(['name' => $name]);
            return [$name => $genre];
        });

        // 3. Seed Featured & Domain Books (matching Stage 1 Wireframes & Course Context)
        $catalogData = [
            [
                'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'author' => 'Ahmad Muizzuddin & Robert C. Martin',
                'publisher' => 'Prentice Hall',
                'publication_year' => 2022,
                'description' => 'Comprehensive guide to writing readable, maintainable, and clean object-oriented software with single-responsibility classes.',
                'page_count' => 464,
                'genre_id' => $genres['Software Engineering']->genre_id,
                'book_type' => 'Hardcover',
                'availability' => true,
            ],
            [
                'title' => 'Advanced Web Programming with Laravel, Livewire & Flux',
                'author' => 'Zaki Al Mahdi',
                'publisher' => 'GMI Academic Press',
                'publication_year' => 2026,
                'description' => 'Covers full-stack reactive web development using Laravel 11+, Livewire components, Flux UI, Alpine.js, Action classes, Policies, and Pest testing.',
                'page_count' => 380,
                'genre_id' => $genres['Web Development']->genre_id,
                'book_type' => 'Hardcover',
                'availability' => false, // Currently on active loan
            ],
            [
                'title' => 'Upin and Ipin: Folk Tales & Cultural Heritage',
                'author' => 'Anas Abdul Aziz',
                'publisher' => 'Les Copaque Publishing',
                'publication_year' => 2024,
                'description' => 'Illustrated anthology exploring Malaysian cultural stories, community values, and family adventures.',
                'page_count' => 192,
                'genre_id' => $genres['Family & Animation']->genre_id,
                'book_type' => 'Paperback',
                'availability' => true,
            ],
            [
                'title' => 'Database System Concepts & Normalization',
                'author' => 'Muhammad Airul Haikal',
                'publisher' => 'McGraw-Hill Education',
                'publication_year' => 2023,
                'description' => 'Deep dive into relational database design, Entity Relationship Diagrams (ERD), foreign key constraints, and SQL optimization.',
                'page_count' => 540,
                'genre_id' => $genres['Database Systems']->genre_id,
                'book_type' => 'Hardcover',
                'availability' => false, // Currently overdue on loan
            ],
            [
                'title' => 'Web Application Security: CSRF, XSS & Secret Isolation',
                'author' => 'Muhammad Azhan',
                'publisher' => 'O\'Reilly Media',
                'publication_year' => 2025,
                'description' => 'Practical defense strategies for modern web applications including CSRF token verification, input sanitization, output escaping, and environment security.',
                'page_count' => 315,
                'genre_id' => $genres['Cybersecurity & Ethics']->genre_id,
                'book_type' => 'Paperback',
                'availability' => true,
            ],
            [
                'title' => 'Domain-Driven Laravel: Form Requests, Actions & Policies',
                'author' => 'Brent Roose',
                'publisher' => 'Spatie Press',
                'publication_year' => 2024,
                'description' => 'Architectural patterns for keeping controllers and Livewire components slim by extracting validation, business logic, and authorization.',
                'page_count' => 290,
                'genre_id' => $genres['Software Engineering']->genre_id,
                'book_type' => 'Hardcover',
                'availability' => true,
            ],
            [
                'title' => 'Automated Testing in PHP with Pest Framework',
                'author' => 'Nuno Maduro',
                'publisher' => 'Laravel Press',
                'publication_year' => 2025,
                'description' => 'Mastering expressive unit and feature testing in Laravel applications with Pest PHP.',
                'page_count' => 240,
                'genre_id' => $genres['Web Development']->genre_id,
                'book_type' => 'Paperback',
                'availability' => true,
            ],
            [
                'title' => 'Modern Artificial Intelligence & Machine Learning Systems',
                'author' => 'Stuart Russell',
                'publisher' => 'Pearson',
                'publication_year' => 2024,
                'description' => 'Foundational principles of intelligent agents, search algorithms, knowledge representation, and neural networks.',
                'page_count' => 610,
                'genre_id' => $genres['Artificial Intelligence']->genre_id,
                'book_type' => 'Reference',
                'availability' => true,
            ],
        ];

        $books = collect($catalogData)->map(fn (array $data) => Book::firstOrCreate(
            ['title' => $data['title']],
            $data
        ));

        // Additional generated books across genres
        if (Book::count() < 15) {
            foreach ($genres as $genre) {
                Book::factory(2)->create([
                    'genre_id' => $genre->genre_id,
                    'availability' => true,
                ]);
            }
        }

        // 4. Seed Loans (Active, Overdue, Returned)
        $activeLoanBook = $books[1]; // Advanced Web Programming
        $activeLoan = Loan::firstOrCreate(
            [
                'user_id' => $member1->user_id,
                'book_id' => $activeLoanBook->book_id,
                'status' => 'borrowed',
            ],
            [
                'borrowed_date' => now()->subDays(5)->toDateString(),
                'due_date' => now()->addDays(9)->toDateString(),
                'returned_date' => null,
            ]
        );

        $overdueBook = $books[3]; // Database System Concepts
        $overdueLoan = Loan::firstOrCreate(
            [
                'user_id' => $member1->user_id,
                'book_id' => $overdueBook->book_id,
                'status' => 'overdue',
            ],
            [
                'borrowed_date' => now()->subDays(22)->toDateString(),
                'due_date' => now()->subDays(8)->toDateString(),
                'returned_date' => null,
            ]
        );

        $returnedLoan = Loan::firstOrCreate(
            [
                'user_id' => $member2->user_id,
                'book_id' => $books[0]->book_id,
                'status' => 'returned',
            ],
            [
                'borrowed_date' => now()->subDays(25)->toDateString(),
                'due_date' => now()->subDays(11)->toDateString(),
                'returned_date' => now()->subDays(6)->toDateString(),
            ]
        );

        // 5. Seed Fines (Unpaid & Paid)
        Fine::firstOrCreate(
            ['loan_id' => $overdueLoan->loan_id],
            [
                'amount' => 8.00, // 8 days overdue @ RM 1.00/day
                'status' => 'unpaid',
            ]
        );

        Fine::firstOrCreate(
            ['loan_id' => $returnedLoan->loan_id],
            [
                'amount' => 5.00, // 5 days overdue @ RM 1.00/day
                'status' => 'paid',
            ]
        );

        // 6. Seed Reservations (Ready for pickup, Queued on borrowed book, Pending)
        Reservation::firstOrCreate(
            [
                'user_id' => $member1->user_id,
                'book_id' => $books[2]->book_id, // Upin and Ipin
            ],
            [
                'reservation_date' => now()->subDay(),
                'status' => 'ready',
            ]
        );

        Reservation::firstOrCreate(
            [
                'user_id' => $member2->user_id,
                'book_id' => $activeLoanBook->book_id, // Queued on currently borrowed book
            ],
            [
                'reservation_date' => now()->subHours(6),
                'status' => 'queued',
            ]
        );

        Reservation::firstOrCreate(
            [
                'user_id' => $extraMembers[0]->user_id,
                'book_id' => $books[4]->book_id,
            ],
            [
                'reservation_date' => now()->subHours(2),
                'status' => 'pending',
            ]
        );
    }
}
