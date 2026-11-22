<?php

use App\Livewire\Admin\GenreManager;
use App\Livewire\Admin\UserManager;
use App\Models\Genre;
use App\Models\User;
use Livewire\Livewire;

test('Fortify registration creates a sanitized member account and redirects to dashboard', function () {
    $response = $this->post('/register', [
        'name' => '  <script>xss</script>Muhammad Airul Haikal  ',
        'email' => 'AIRUL@BOOKNEST.TEST',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();

    $user = User::where('email', 'airul@booknest.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('xssMuhammad Airul Haikal')
        ->and($user->role)->toBe('member');
});

test('Fortify login authenticates valid credentials and rejects invalid passwords', function () {
    $user = User::factory()->member()->create([
        'email' => 'student@booknest.test',
        'password' => bcrypt('secret1234'),
    ]);

    $badResponse = $this->post('/login', [
        'email' => 'student@booknest.test',
        'password' => 'wrong-password',
    ]);
    $badResponse->assertSessionHasErrors();
    $this->assertGuest();

    $goodResponse = $this->post('/login', [
        'email' => 'student@booknest.test',
        'password' => 'secret1234',
    ]);
    $goodResponse->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('Role-based access control blocks non-admins from user management and non-staff from inventory portal', function () {
    $member = User::factory()->member()->create();
    $staff = User::factory()->staff()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($member)->get('/users')->assertForbidden();
    $this->actingAs($member)->get('/admin/books')->assertForbidden();

    $this->actingAs($staff)->get('/users')->assertForbidden();
    $this->actingAs($staff)->get('/admin/books')->assertOk();

    $this->actingAs($admin)->get('/users')->assertOk();
    $this->actingAs($admin)->get('/admin/books')->assertOk();
});

test('Livewire GenreManager and UserManager perform CRUD and block unauthorized roles', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();

    Livewire::actingAs($member)
        ->test(GenreManager::class)
        ->set('name', 'Unauthorized Genre')
        ->call('saveGenre')
        ->assertForbidden();

    Livewire::actingAs($admin)
        ->test(GenreManager::class)
        ->set('name', '<b>Distributed Systems</b>')
        ->call('saveGenre')
        ->assertHasNoErrors();

    expect(Genre::where('name', 'Distributed Systems')->exists())->toBeTrue();

    Livewire::actingAs($admin)
        ->test(UserManager::class)
        ->call('openCreateModal')
        ->set('name', 'New Library Staff')
        ->set('email', 'newstaff@booknest.test')
        ->set('role', 'staff')
        ->set('password', 'password123')
        ->call('saveUser')
        ->assertHasNoErrors();

    expect(User::where('email', 'newstaff@booknest.test')->where('role', 'staff')->exists())->toBeTrue();
});

test('DatabaseSeeder populates multi-role users, genres, books, loans, fines, and reservations cleanly', function () {
    $this->seed();

    expect(User::where('role', 'admin')->exists())->toBeTrue()
        ->and(User::where('role', 'staff')->exists())->toBeTrue()
        ->and(User::where('role', 'member')->exists())->toBeTrue()
        ->and(Genre::count())->toBeGreaterThanOrEqual(7)
        ->and(\App\Models\Book::count())->toBeGreaterThanOrEqual(15)
        ->and(\App\Models\Loan::count())->toBeGreaterThanOrEqual(3)
        ->and(\App\Models\Fine::count())->toBeGreaterThanOrEqual(2)
        ->and(\App\Models\Reservation::count())->toBeGreaterThanOrEqual(3);
});

