<?php

namespace App\Providers;

use App\Models\Book;
use App\Models\Fine;
use App\Models\Genre;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use App\Policies\BookPolicy;
use App\Policies\FinePolicy;
use App\Policies\GenrePolicy;
use App\Policies\LoanPolicy;
use App\Policies\ReservationPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Genre::class, GenrePolicy::class);
        Gate::policy(Book::class, BookPolicy::class);
        Gate::policy(Reservation::class, ReservationPolicy::class);
        Gate::policy(Loan::class, LoanPolicy::class);
        Gate::policy(Fine::class, FinePolicy::class);

        Gate::define('access-staff-portal', fn (User $user) => $user->isStaffOrAdmin());
        Gate::define('access-admin-portal', fn (User $user) => $user->isAdmin());
        Gate::define('manage-books', fn (User $user) => $user->isStaffOrAdmin());
    }
}
