<?php

use App\Actions\Books\CreateBookAction;
use App\Actions\Books\DeleteBookAction;
use App\Actions\Books\ToggleBookAvailabilityAction;
use App\Actions\Books\UpdateBookAction;
use App\Http\Requests\Books\BookRequest;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use App\Policies\BookPolicy;

test('BookPolicy permits public read, staff/admin write, and admin-only delete', function () {
    $admin = User::factory()->admin()->create();
    $staff = User::factory()->staff()->create();
    $member = User::factory()->member()->create();
    $book = Book::factory()->create();
    $policy = new BookPolicy;

    expect($policy->viewAny(null))->toBeTrue()
        ->and($policy->view($member, $book))->toBeTrue()
        ->and($policy->create($member))->toBeFalse()
        ->and($policy->create($staff))->toBeTrue()
        ->and($policy->create($admin))->toBeTrue()
        ->and($policy->update($staff, $book))->toBeTrue()
        ->and($policy->delete($staff, $book))->toBeFalse()
        ->and($policy->delete($admin, $book))->toBeTrue();
});

test('BookRequest strips HTML and XSS payloads before validation', function () {
    $genre = Genre::factory()->create();

    $validated = BookRequest::validatePayload([
        'title' => '  <script>Hack</script>Clean Architecture  ',
        'author' => '<b>Robert C. Martin</b>',
        'publisher' => '<i>Prentice Hall</i>',
        'publication_year' => 2024,
        'description' => '<img src=x onerror=alert(1)>A complete guide to software architecture principles.',
        'page_count' => 432,
        'genre_id' => $genre->genre_id,
        'book_type' => 'Hardcover',
        'availability' => true,
    ]);

    expect($validated['title'])->toBe('HackClean Architecture')
        ->and($validated['author'])->toBe('Robert C. Martin')
        ->and($validated['publisher'])->toBe('Prentice Hall')
        ->and($validated['description'])->toBe('A complete guide to software architecture principles.');
});

test('Book Action classes handle creation, updates, availability toggling, and deletion', function () {
    $genre = Genre::factory()->create();

    $book = (new CreateBookAction)->execute([
        'title' => 'Refactoring',
        'author' => 'Martin Fowler',
        'publisher' => 'Addison-Wesley',
        'publication_year' => 2019,
        'description' => 'Improving the design of existing code systematically.',
        'page_count' => 448,
        'genre_id' => $genre->genre_id,
        'book_type' => 'Hardcover',
    ], true);

    expect($book->availability)->toBeTrue()
        ->and($book->bookCode())->toStartWith('BK-');

    $toggled = (new ToggleBookAvailabilityAction)->execute($book);
    expect($toggled->availability)->toBeFalse();

    $updated = (new UpdateBookAction)->execute($book, ['title' => 'Refactoring 2nd Edition'], true);
    expect($updated->title)->toBe('Refactoring 2nd Edition')
        ->and($updated->availability)->toBeTrue();

    expect((new DeleteBookAction)->execute($book))->toBeTrue();
});
