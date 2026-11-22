<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Support\Facades\Route;

// Public Home Page & Catalogue Overview
Route::get('/', function () {
    $featuredBooks = Book::with('genre')->orderByDesc('book_id')->take(6)->get();
    $genres = Genre::withCount('books')->orderBy('name')->get();

    return view('home', [
        'featuredBooks' => $featuredBooks,
        'genres' => $genres,
    ]);
})->name('home');

// Authenticated Routes (Member, Library Staff, Admin)
Route::middleware('auth')->group(function () {
    // Personal & Role Dashboard
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // Dedicated Admin & Staff Inventory Management Portal
    Route::view('/admin/books', 'books.inventory')
        ->middleware('role:staff,admin')
        ->name('admin.books');

    // Genre Write Routes (Admin Only)
    Route::get('/genres/create', [GenreController::class, 'create'])->name('genres.create');
    Route::post('/genres', [GenreController::class, 'store'])->name('genres.store');
    Route::get('/genres/{genre}/edit', [GenreController::class, 'edit'])->name('genres.edit');
    Route::match(['put', 'patch'], '/genres/{genre}', [GenreController::class, 'update'])->name('genres.update');
    Route::delete('/genres/{genre}', [GenreController::class, 'destroy'])->name('genres.destroy');

    // Book Write Routes (Staff & Admin)
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::match(['put', 'patch'], '/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

    // 6.3 Reservation RESTful Routes (Member, Staff, Admin)
    Route::resource('reservations', ReservationController::class);

    // 6.4 Loan RESTful Routes (Member views/renews own; Staff/Admin full circulation)
    Route::resource('loans', LoanController::class);

    // 6.5 Fine RESTful Routes (Member views own; Staff/Admin issues & settles fines)
    Route::resource('fines', FineController::class);

    // 6.6 User RESTful Routes (Admin manages all users; Member views/updates own profile)
    Route::resource('users', UserController::class);
});

// 6.1 Public Genre Read Routes
Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
Route::get('/genres/{genre}', [GenreController::class, 'show'])->name('genres.show');

// 6.2 Public Book Read Routes
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
