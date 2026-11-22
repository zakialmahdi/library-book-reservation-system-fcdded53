# Library of GMI — Library Book Reservation and Loan System

**SPG 0553 Advanced Web Programming — Mini Project (GMI Diploma in Software Engineering, July 2026)**

Library of GMI is a full-stack reactive web application built with **Laravel**, **Livewire**, **Flux UI**, **Laravel Fortify**, **Alpine.js**, **Form Requests**, **Single-Responsibility Action Classes**, **Laravel Policies**, and **Pest PHP**.

---

## 1. Group Members & Module Allocation

| Student / Role | Assigned Technical Domain | Key Codebase Components |
| :--- | :--- | :--- |
| **1. Muhammad Airul Haikal Bin Mohd Azahir**<br>*(Security, Auth, Users & Genres Lead)* | Laravel Fortify Authentication, Role Middleware, `User` & `Genre` modules, CSRF/XSS/`.env` Security Controls | `FortifyServiceProvider`, `CreateNewUser`, `EnsureUserHasRole`, `UserPolicy`, `GenrePolicy`, `UserRequest`, `GenreRequest`, `CreateUserAction`, `CreateGenreAction`, `UserManager`, `GenreManager`, `AuthAndSecurityTest` |
| **2. Ahmad Muizzuddin Bin Mustapa Kamal**<br>*(Module 1 Lead — Book Inventory & Catalogue)* | Book Inventory CRUD & Real-Time Public Catalogue Search/Filtering (`Book`) | `Book`, `BookPolicy`, `BookRequest`, `CreateBookAction`, `UpdateBookAction`, `ToggleBookAvailabilityAction`, `DeleteBookAction`, `BookCatalog`, `BookInventory`, `BookInventoryTest` |
| **3. Zaki Al Mahdi Mohd Zahwan**<br>*(Module 2 Lead — Loans & Automated Fines)* | Staff Circulation Desk (Member Lookup, Barcode Scan, Check-out, Return, Renewal) & Automated Overdue Fines (`Loan`, `Fine`) | `Loan`, `Fine`, `LoanPolicy`, `FinePolicy`, `LoanRequest`, `FineRequest`, `IssueLoanAction`, `ReturnLoanAction`, `RenewLoanAction`, `IssueFineAction`, `SettleFineAction`, `LoanManager`, `FineManager`, `LoanAndFineWorkflowTest` |
| **4. Muhammad Azhan Bin Md Khairol Fadzli**<br>*(Module 3 & Alpine.js UI Lead — Reservations & Dashboard)* | Book Reservations, Priority Waitlist Queue, Member Dashboard & Alpine.js Interactive UI (`Reservation`) | `Reservation`, `ReservationPolicy`, `ReservationRequest`, `PlaceReservationAction`, `UpdateReservationStatusAction`, `CancelReservationAction`, `ReservationManager`, `MemberDashboard`, `ReservationAndDashboardTest` |

---

## 2. Quick Setup Instructions

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate:fresh --seed
npm install
npm run build
```

---

## 3. Default Seeded Login Credentials

All accounts use the password: `password`

| Role | Name | Email | Password |
| :--- | :--- | :--- | :--- |
| **Admin** | Muhammad Airul Haikal (Admin) | `admin@booknest.test` | `password` |
| **Library Staff** | Zaki Al Mahdi (Library Staff) | `staff@booknest.test` | `password` |
| **Member 1** | Ahmad Muizzuddin (Member) | `member@booknest.test` | `password` |
| **Member 2** | Muhammad Azhan (Member) | `azhan@booknest.test` | `password` |

---

## 4. Running the Pest PHP Test Suite

```bash
php artisan test
```
