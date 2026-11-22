<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Models\Book;
use Illuminate\Support\Facades\Route;

// Public Home Page
Route::get('/', function () {
    $featuredBooks = Book::with('category')->where('is_featured', true)->get();
    return view('home', ['featuredBooks' => $featuredBooks]);
})->name('home');

// Guest Routes (Only accessible when NOT logged in)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes (Only accessible when logged in)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Protect CRUD modifications (Create, Store, Edit, Update, Destroy)
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{id}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{id}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{id}', [BookController::class, 'destroy'])->name('books.destroy');
});

// Public Read Routes (Catalog view & Detail view)
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{id}', [BookController::class, 'show'])->name('books.show');


