<?php

use App\Actions\Genres\CreateGenreAction;
use App\Actions\Genres\DeleteGenreAction;
use App\Actions\Genres\UpdateGenreAction;
use App\Actions\Users\CreateUserAction;
use App\Actions\Users\UpdateUserAction;
use App\Http\Requests\Genres\GenreRequest;
use App\Http\Requests\Users\UserRequest;
use App\Models\Genre;
use App\Models\User;
use App\Policies\GenrePolicy;
use App\Policies\UserPolicy;

test('UserPolicy enforces admin access and blocks self-deletion', function () {
    $admin = User::factory()->admin()->create();
    $staff = User::factory()->staff()->create();
    $member = User::factory()->member()->create();
    $policy = new UserPolicy;

    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->viewAny($staff))->toBeFalse()
        ->and($policy->viewAny($member))->toBeFalse()
        ->and($policy->create($admin))->toBeTrue()
        ->and($policy->create($member))->toBeFalse()
        ->and($policy->update($member, $member))->toBeTrue()
        ->and($policy->update($member, $staff))->toBeFalse()
        ->and($policy->delete($admin, $member))->toBeTrue()
        ->and($policy->delete($admin, $admin))->toBeFalse();
});

test('GenrePolicy allows public viewing and restricts mutations to admin', function () {
    $admin = User::factory()->admin()->create();
    $staff = User::factory()->staff()->create();
    $member = User::factory()->member()->create();
    $genre = Genre::factory()->create();
    $policy = new GenrePolicy;

    expect($policy->viewAny(null))->toBeTrue()
        ->and($policy->view(null, $genre))->toBeTrue()
        ->and($policy->create($admin))->toBeTrue()
        ->and($policy->create($staff))->toBeFalse()
        ->and($policy->update($admin, $genre))->toBeTrue()
        ->and($policy->delete($member, $genre))->toBeFalse();
});

test('GenreRequest and UserRequest sanitize XSS script tags from inputs', function () {
    $sanitizedGenre = GenreRequest::sanitize([
        'name' => '  <script>alert("xss")</script>Cybersecurity  ',
    ]);

    $sanitizedUser = UserRequest::sanitize([
        'name' => '<b>Ahmad</b> <script>evil()</script>',
        'email' => '  AHMAD@BOOKNEST.TEST ',
        'role' => ' MEMBER ',
    ]);

    expect($sanitizedGenre['name'])->toBe('alert("xss")Cybersecurity')
        ->and($sanitizedUser['name'])->toBe('Ahmad evil()')
        ->and($sanitizedUser['email'])->toBe('ahmad@booknest.test')
        ->and($sanitizedUser['role'])->toBe('member');
});

test('Genre and User Action classes create, update, and delete records cleanly', function () {
    $genre = (new CreateGenreAction)->execute(['name' => 'Artificial Intelligence']);
    expect($genre->name)->toBe('Artificial Intelligence');

    $updatedGenre = (new UpdateGenreAction)->execute($genre, ['name' => 'Machine Learning']);
    expect($updatedGenre->name)->toBe('Machine Learning');

    $user = (new CreateUserAction)->execute([
        'name' => 'Test Staff',
        'email' => 'teststaff@booknest.test',
        'role' => 'staff',
        'password' => 'password123',
    ]);
    expect($user->isStaff())->toBeTrue();

    $updatedUser = (new UpdateUserAction)->execute($user, [
        'name' => 'Senior Staff',
        'email' => 'teststaff@booknest.test',
        'role' => 'admin',
    ]);
    expect($updatedUser->isAdmin())->toBeTrue()
        ->and($updatedUser->name)->toBe('Senior Staff');

    expect((new DeleteGenreAction)->execute($genre))->toBeTrue();
});
