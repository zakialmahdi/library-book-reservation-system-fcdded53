================================================================================
LIBRARY OF GMI — LIBRARY BOOK RESERVATION & LOAN SYSTEM
SPG 0553 ADVANCED WEB PROGRAMMING (MINI PROJECT — GMI JULY 2026)
================================================================================

1. PROJECT OVERVIEW & TECHNICAL STACK
--------------------------------------------------------------------------------
Title          : Library of GMI — Library Book Reservation and Loan System
Course         : SPG 0553 Advanced Web Programming
Programme      : Diploma in Software Engineering (GMI)
Framework      : Laravel 11+ (Laravel 13.8 / PHP 8.3 via Laravel Herd)
Starter Stack  : Livewire 4 + Flux UI 2 + Laravel Fortify + Alpine.js + Tailwind CSS v4
Architecture   : MVC + Custom Form Requests + Single-Responsibility Action Classes + Laravel Policies
Testing Suite  : Pest PHP (Unit & Feature Test Suites under tests/)

2. GROUP MEMBERS & EXPLICIT MODULE ALLOCATION (ARCHITECTURAL & DOMAIN SPLIT)
--------------------------------------------------------------------------------
Student 1: MUHAMMAD AIRUL HAIKAL BIN MOHD AZAHIR (Security, Auth, Users & Genres Lead)
  - Technical Domain : Authentication (Fortify), Role-Based Access Control (Middleware &
                       Policies), User Management, Genre Classification, CSRF/XSS/.env Security.
  - Owned Files      :
    * Migrations     : database/migrations/0001_01_01_000000_create_users_table.php
                       database/migrations/2026_09_01_000001_create_genres_table.php
    * Models         : app/Models/User.php, app/Models/Genre.php
    * Fortify & Auth : app/Providers/FortifyServiceProvider.php, config/fortify.php,
                       app/Actions/Fortify/CreateNewUser.php,
                       app/Actions/Fortify/UpdateUserProfileInformation.php,
                       app/Actions/Fortify/UpdateUserPassword.php,
                       app/Actions/Fortify/ResetUserPassword.php
    * Middleware     : app/Http/Middleware/EnsureUserHasRole.php
    * Form Requests  : app/Http/Requests/Users/UserRequest.php,
                       app/Http/Requests/Genres/GenreRequest.php
    * Action Classes : app/Actions/Users/{CreateUserAction,UpdateUserAction,DeleteUserAction}.php
                       app/Actions/Genres/{CreateGenreAction,UpdateGenreAction,DeleteGenreAction}.php
    * Policies       : app/Policies/UserPolicy.php, app/Policies/GenrePolicy.php
    * Livewire & UI  : app/Livewire/Admin/UserManager.php, app/Livewire/Admin/GenreManager.php
                       resources/views/livewire/admin/{user-manager,genre-manager}.blade.php
                       resources/views/auth/{login,register,forgot-password,reset-password}.blade.php
    * Pest Tests     : tests/Unit/UserAndGenrePolicyActionTest.php
                       tests/Feature/AuthAndSecurityTest.php

Student 2: AHMAD MUIZZUDDIN BIN MUSTAPA KAMAL (Module 1 Lead — Book Inventory & Public Catalogue)
  - Technical Domain : Book Inventory CRUD, Multi-Facet Real-Time Catalogue Search & Filtering.
  - Owned Files      :
    * Migration      : database/migrations/2026_09_01_000002_create_books_table.php
    * Model          : app/Models/Book.php
    * Form Request   : app/Http/Requests/Books/BookRequest.php
    * Action Classes : app/Actions/Books/CreateBookAction.php
                       app/Actions/Books/UpdateBookAction.php
                       app/Actions/Books/ToggleBookAvailabilityAction.php
                       app/Actions/Books/DeleteBookAction.php
    * Policy         : app/Policies/BookPolicy.php
    * Controllers    : app/Http/Controllers/BookController.php, app/Http/Controllers/GenreController.php
    * Livewire & UI  : app/Livewire/Books/BookCatalog.php, app/Livewire/Admin/BookInventory.php
                       resources/views/livewire/books/book-catalog.blade.php
                       resources/views/livewire/admin/book-inventory.blade.php
    * Pest Tests     : tests/Unit/BookActionAndPolicyTest.php
                       tests/Feature/BookInventoryTest.php

Student 3: ZAKI AL MAHDI MOHD ZAHWAN (Module 2 Lead — Circulation Loans & Automated Overdue Fines)
  - Technical Domain : Staff Circulation Portal (Member Verification, Book Barcode Scan, Loan
                       Issuance, Book Return, Renewal) & Automated Overdue Fine Calculation/Settlement.
  - Owned Files      :
    * Migrations     : database/migrations/2026_09_01_000004_create_loans_table.php
                       database/migrations/2026_09_01_000005_create_fines_table.php
    * Models         : app/Models/Loan.php, app/Models/Fine.php
    * Form Requests  : app/Http/Requests/Loans/LoanRequest.php
                       app/Http/Requests/Fines/FineRequest.php
    * Action Classes : app/Actions/Loans/IssueLoanAction.php
                       app/Actions/Loans/ReturnLoanAction.php
                       app/Actions/Loans/RenewLoanAction.php
                       app/Actions/Loans/DeleteLoanAction.php
                       app/Actions/Fines/{IssueFineAction,SettleFineAction,DeleteFineAction}.php
    * Policies       : app/Policies/LoanPolicy.php, app/Policies/FinePolicy.php
    * Controllers    : app/Http/Controllers/LoanController.php, app/Http/Controllers/FineController.php
    * Livewire & UI  : app/Livewire/Loans/LoanManager.php, app/Livewire/Fines/FineManager.php
                       resources/views/livewire/loans/loan-manager.blade.php
                       resources/views/livewire/fines/fine-manager.blade.php
    * Pest Tests     : tests/Unit/LoanAndFineActionPolicyTest.php
                       tests/Feature/LoanAndFineWorkflowTest.php

Student 4: MUHAMMAD AZHAN BIN MD KHAIROL FADZLI (Module 3 & Alpine.js UI Lead — Reservations & Member Dashboard)
  - Technical Domain : Book Reservations, Priority Waitlist Queue System, Member Personal Dashboard,
                       and Advanced Alpine.js Client-Side Interactive UI (Modals, Tabs, Accordions,
                       Confirmation Popups, and XSS-safe x-text bindings).
  - Owned Files      :
    * Migration      : database/migrations/2026_09_01_000003_create_reservations_table.php
    * Model          : app/Models/Reservation.php
    * Form Request   : app/Http/Requests/Reservations/ReservationRequest.php
    * Action Classes : app/Actions/Reservations/PlaceReservationAction.php
                       app/Actions/Reservations/UpdateReservationStatusAction.php
                       app/Actions/Reservations/CancelReservationAction.php
    * Policy         : app/Policies/ReservationPolicy.php
    * Controllers    : app/Http/Controllers/ReservationController.php, app/Http/Controllers/UserController.php
    * Livewire & UI  : app/Livewire/Reservations/ReservationManager.php
                       app/Livewire/Dashboard/MemberDashboard.php
                       resources/views/livewire/reservations/reservation-manager.blade.php
                       resources/views/livewire/dashboard/member-dashboard.blade.php
    * Pest Tests     : tests/Unit/ReservationActionAndPolicyTest.php
                       tests/Feature/ReservationAndDashboardTest.php

3. ENVIRONMENT VARIABLE CONFIGURATION (.env)
--------------------------------------------------------------------------------
1. Copy the example environment file:
   cp .env.example .env

2. Configure your database connection inside .env:
   - For MySQL (via Laravel Herd / DBngin):
     APP_NAME="BookNest"
     APP_ENV=local
     APP_DEBUG=true
     APP_URL=http://library-book-reservation-system.tech-bookstore.test

     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=conehub
     DB_USERNAME=root
     DB_PASSWORD=

   - Or for zero-configuration SQLite:
     DB_CONNECTION=sqlite

   SECURITY NOTE: Never commit the .env file to version control (.env is isolated
   in .gitignore). All secrets, APP_KEY, and database credentials are read strictly
   through config() helpers backed by env().

4. INSTALLATION & SETUP COMMANDS
--------------------------------------------------------------------------------
Run the following commands in the project root directory:

   composer install
   php artisan key:generate
   php artisan migrate:fresh --seed
   npm install
   npm run build

To run the application locally (if not using Laravel Herd auto-virtual host):
   php artisan serve

5. DEFAULT DEMO USER LOGIN CREDENTIALS (POPULATED BY php artisan db:seed)
--------------------------------------------------------------------------------
All demo accounts use the default password: password

  1. ADMIN ACCOUNT
     - Name     : Muhammad Airul Haikal (Admin)
     - Email    : admin@booknest.test
     - Password : password
     - Role     : admin (Full access to Inventory CRUD, Genres CRUD, Users & Roles, Circulation, Fines)

  2. LIBRARY STAFF ACCOUNT
     - Name     : Zaki Al Mahdi (Library Staff)
     - Email    : staff@booknest.test
     - Password : password
     - Role     : staff (Access to Staff Circulation Desk, Loans/Returns, Fines, Reservations, Inventory)

  3. MEMBER ACCOUNT 1
     - Name     : Ahmad Muizzuddin (Member)
     - Email    : member@booknest.test
     - Password : password
     - Role     : member (Has seeded active loan, overdue loan with fine, and ready reservation)

  4. MEMBER ACCOUNT 2
     - Name     : Muhammad Azhan (Member)
     - Email    : azhan@booknest.test
     - Password : password
     - Role     : member (Has seeded queued waitlist reservation and returned loan history)

6. RUNNING AUTOMATED PEST PHP TESTS
--------------------------------------------------------------------------------
Execute the complete Unit and Feature test suite using either command:

   php artisan test
   ./vendor/bin/pest
================================================================================
