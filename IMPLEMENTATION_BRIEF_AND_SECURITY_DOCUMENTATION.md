# Implementation Brief & Security Documentation
## Library of GMI — Library Book Reservation and Loan System (SPG 0553)

---

### 1. Stage 1 ERD & Database Schema Implementation
All 6 core domain tables from the Stage 1 Entity Relationship Diagram (ERD) are implemented in `database/migrations/` with explicit primary keys, foreign key constraints, `onDelete('cascade')` rules, and performance indexes:

1. **`users` (`0001_01_01_000000_create_users_table.php`)**: Primary key `user_id`, `name`, `email` (unique), `password` (bcrypt hashed), `role` (`member`, `staff`, `admin`), Fortify 2FA columns, and session tables.
2. **`genres` (`2026_09_01_000001_create_genres_table.php`)**: Primary key `genre_id`, `name` (unique).
3. **`books` (`2026_09_01_000002_create_books_table.php`)**: Primary key `book_id`, `title`, `author`, `publisher`, `publication_year`, `description`, `page_count`, `genre_id` (FK -> `genres.genre_id` `onDelete('cascade')`), `book_type`, `availability` (boolean).
4. **`reservations` (`2026_09_01_000003_create_reservations_table.php`)**: Primary key `reservation_id`, `user_id` (FK -> `users.user_id` `onDelete('cascade')`), `book_id` (FK -> `books.book_id` `onDelete('cascade')`), `reservation_date`, `status` (`pending`, `queued`, `ready`, `fulfilled`, `cancelled`).
5. **`loans` (`2026_09_01_000004_create_loans_table.php`)**: Primary key `loan_id`, `user_id` (FK -> `users.user_id` `onDelete('cascade')`), `book_id` (FK -> `books.book_id` `onDelete('cascade')`), `borrowed_date`, `due_date`, `returned_date`, `status` (`borrowed`, `overdue`, `returned`).
6. **`fines` (`2026_09_01_000005_create_fines_table.php`)**: Primary key `fine_id`, `loan_id` (FK -> `loans.loan_id` `onDelete('cascade')`), `amount` (`decimal(8,2)`), `status` (`unpaid`, `paid`, `waived`).

---

### 2. Clean Architecture: Form Requests, Action Classes & Policies

#### 2.1 Custom Form Requests (`app/Http/Requests/`)
Every data mutation endpoint sanitizes inputs (`strip_tags`, `trim`, type normalization) and validates business constraints before reaching any Action class:
- `App\Http\Requests\Genres\GenreRequest`
- `App\Http\Requests\Users\UserRequest`
- `App\Http\Requests\Books\BookRequest`
- `App\Http\Requests\Reservations\ReservationRequest`
- `App\Http\Requests\Loans\LoanRequest`
- `App\Http\Requests\Fines\FineRequest`

#### 2.2 Single-Responsibility Action Classes (`app/Actions/`)
Domain business logic is completely extracted out of controllers and Livewire components into dedicated Action classes:
- **Fortify Auth**: `CreateNewUser`, `UpdateUserProfileInformation`, `UpdateUserPassword`, `ResetUserPassword`
- **Users & Genres**: `CreateUserAction`, `UpdateUserAction`, `DeleteUserAction`, `CreateGenreAction`, `UpdateGenreAction`, `DeleteGenreAction`
- **Books**: `CreateBookAction`, `UpdateBookAction`, `ToggleBookAvailabilityAction`, `DeleteBookAction`
- **Loans**:
  - `IssueLoanAction`: Verifies book availability inside a database transaction (`DB::transaction`), creates loan record, sets `book.availability = false`, and transitions any matching reservation to `fulfilled`.
  - `ReturnLoanAction`: Marks loan as `returned`, restores `book.availability = true`, **automatically calculates overdue days and generates a `Fine` record (RM 1.00/day)** if returned past `due_date`, and promotes the next `queued` reservation to `ready`.
  - `RenewLoanAction`: Extends due date by 7 days for active non-overdue loans.
  - `DeleteLoanAction`: Restores book availability if active and deletes the loan record.
- **Fines**: `IssueFineAction`, `SettleFineAction`, `DeleteFineAction`
- **Reservations**:
  - `PlaceReservationAction`: Blocks duplicate active reservations/loans; dynamically assigns `status = 'pending'` if the book is available or `status = 'queued'` (waitlist queue) if the book is currently on loan.
  - `UpdateReservationStatusAction`, `CancelReservationAction`

#### 2.3 Laravel Policies (`app/Policies/`)
Registered in `AppServiceProvider` and enforced via `$this->authorize()` / `Gate::authorize()` inside every Livewire component and controller:
- `GenrePolicy`: Public `viewAny`/`view`; Admin-only `create`/`update`/`delete`.
- `BookPolicy`: Public `viewAny`/`view`; Staff & Admin `create`/`update`; Admin-only `delete`.
- `ReservationPolicy`: Members view/cancel own reservations; Staff & Admin oversee all reservations.
- `LoanPolicy`: Members view/renew own active loans; Staff & Admin issue loans and process returns; Admin-only `delete`.
- `FinePolicy`: Members view own fines; Staff & Admin issue and settle (`paid`/`waived`) fines; Admin-only `delete`.
- `UserPolicy`: Admin manages all users (with self-deletion prevention); Members can view/update their own profile.

---

### 3. Livewire, Flux UI & Alpine.js Interactive Components

| Livewire Component | Blade View | Flux UI & Alpine.js Interactive Features |
| :--- | :--- | :--- |
| `BookCatalog` | `resources/views/livewire/books/book-catalog.blade.php` | Livewire real-time debounced search, genre & availability filters, pagination; Alpine.js Quick-View modal & Reservation confirmation dialog using `x-data`, `x-show`, `x-on:click`, `x-text`. |
| `MemberDashboard` | `resources/views/livewire/dashboard/member-dashboard.blade.php` | Quick stats cards (Active Loans, Pending/Ready Reservations, Unpaid Fines), Alpine.js dynamic tabs (`overview`, `fines`, `profile`), 1-click loan renewal, and Alpine cancellation dialog. |
| `LoanManager` | `resources/views/livewire/loans/loan-manager.blade.php` | Staff Circulation Desk with Member ID verification (`MBR-2026-XXXX`), Book Barcode scan (`BK-XXXX`), loan issuance, and Alpine.js return confirmation modal with overdue notice. |
| `FineManager` | `resources/views/livewire/fines/fine-manager.blade.php` | Fine ledger filtering, manual fine issuance modal, and Alpine.js settlement confirmation modal (`Mark Paid` / `Waive`). |
| `ReservationManager` | `resources/views/livewire/reservations/reservation-manager.blade.php` | Collapsible Alpine.js reservation form, queue status transitions (`Mark Ready`, `Issue Loan`), and cancellation dialog. |
| `BookInventory` | `resources/views/livewire/admin/book-inventory.blade.php` | Full inventory CRUD modal, instant 1-click availability toggle pill, and Alpine.js delete confirmation dialog. |
| `GenreManager` | `resources/views/livewire/admin/genre-manager.blade.php` | Interactive Alpine.js accordions expanding books within each genre (`x-data`, `x-show`, `x-text`) + Admin CRUD. |
| `UserManager` | `resources/views/livewire/admin/user-manager.blade.php` | Admin user & role management modal with search/role filters and Alpine.js delete confirmation. |

---

### 4. Web Security & Ethics Controls (Part B Defense Reference)

1. **CSRF Protection**:
   - All classic POST/PUT/DELETE forms include `@csrf`, verified by Laravel's `VerifyCsrfToken` (`ValidateCsrfToken`) web middleware.
   - All Livewire requests automatically include and validate the X-CSRF-TOKEN header payload signed by the session token (`<meta name="csrf-token" content="{{ csrf_token() }}">`).
2. **XSS Defense (Input Sanitization + Output Escaping + `x-text`)**:
   - **Input Layer**: Every `FormRequest::sanitize()` method and `CreateNewUser` runs `strip_tags(trim(...))` before validation and persistence, preventing stored HTML/JavaScript injection.
   - **Server Output Layer**: All Blade templates strictly use escaped `{{ ... }}` syntax (zero unescaped `{!! !!}` rendering on user data).
   - **Client Alpine.js Layer**: All dynamic client-side modals and alerts bind data via `x-text="selectedBook.title"` (which sets DOM `textContent` rather than `innerHTML`), neutralizing DOM-based XSS.
3. **`.env` Secret Isolation**:
   - `.env` is excluded from Git via `.gitignore`.
   - No database passwords, API keys, or encryption keys are hardcoded in source files; all configuration reads go through `config()` files referencing `env()`.
