<?php

use App\Livewire\Admin\BookInventory;
use App\Livewire\Books\BookCatalog;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Livewire\Livewire;

test('Public BookCatalog filters books by title, genre, and availability status', function () {
    $sweGenre = Genre::factory()->create(['name' => 'Software Engineering']);
    $aiGenre = Genre::factory()->create(['name' => 'Artificial Intelligence']);

    Book::factory()->create([
        'title' => 'Clean Code',
        'author' => 'Ahmad Muizzuddin',
        'genre_id' => $sweGenre->genre_id,
        'availability' => true,
    ]);

    Book::factory()->create([
        'title' => 'Deep Learning Neural Networks',
        'author' => 'Ian Goodfellow',
        'genre_id' => $aiGenre->genre_id,
        'availability' => false,
    ]);

    Livewire::test(BookCatalog::class)
        ->assertSee('Clean Code')
        ->assertSee('Deep Learning Neural Networks')
        ->set('search', 'Clean Code')
        ->assertSee('Clean Code')
        ->assertDontSee('Deep Learning Neural Networks')
        ->call('resetFilters')
        ->set('availabilityFilter', 'borrowed')
        ->assertSee('Deep Learning Neural Networks')
        ->assertDontSee('Clean Code');
});

test('Livewire BookInventory allows staff and admin to create, update, toggle availability, and admin to delete books', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $genre = Genre::factory()->create(['name' => 'Web Programming']);

    Livewire::actingAs($member)
        ->test(BookInventory::class)
        ->call('openCreateModal')
        ->assertForbidden();

    Livewire::actingAs($admin)
        ->test(BookInventory::class)
        ->call('openCreateModal')
        ->set('title', 'Laravel Livewire Mastery')
        ->set('author', 'Caleb Porzio')
        ->set('publisher', 'Laravel Press')
        ->set('publication_year', 2026)
        ->set('description', 'Complete handbook for building reactive full-stack applications with Livewire and Alpine.js.')
        ->set('page_count', 320)
        ->set('genre_id', (string) $genre->genre_id)
        ->set('book_type', 'Hardcover')
        ->set('availability', true)
        ->call('saveBook')
        ->assertHasNoErrors();

    $book = Book::where('title', 'Laravel Livewire Mastery')->first();
    expect($book)->not->toBeNull()
        ->and($book->availability)->toBeTrue();

    Livewire::actingAs($admin)
        ->test(BookInventory::class)
        ->call('toggleAvailability', $book->book_id);

    expect($book->fresh()->availability)->toBeFalse();

    Livewire::actingAs($admin)
        ->test(BookInventory::class)
        ->call('deleteBook', $book->book_id);

    expect(Book::find($book->book_id))->toBeNull();
});
