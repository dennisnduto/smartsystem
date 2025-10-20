# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Commands you’ll use often

- Install dependencies
  ```bash path=null start=null
  composer install
  npm install
  ```
- Environment and database
  ```bash path=null start=null
  copy .env.example .env   # Windows (PowerShell)
  php artisan key:generate
  php artisan migrate
  ```
- Run the app in dev (two terminals)
  ```bash path=null start=null
  php artisan serve
  npm run dev
  ```
- Build frontend assets
  ```bash path=null start=null
  npm run build
  ```
- Tests (PHPUnit via Artisan)
  ```bash path=null start=null
  php artisan test                     # run all tests
  php artisan test --testsuite=Unit    # only Unit tests
  php artisan test --testsuite=Feature # only Feature tests
  ```
- Run a single test / method
  ```bash path=null start=null
  vendor/bin/phpunit tests/Feature/ExampleTest.php
  vendor/bin/phpunit --filter test_the_application_returns_a_successful_response
  ```
- Lint/format PHP (Laravel Pint)
  ```bash path=null start=null
  php vendor/bin/pint          # fix
  php vendor/bin/pint -n       # check only
  ```

## High-level architecture

- Framework/runtime
  - Laravel 11 (PHP 8.2). App entry is `artisan` and standard Laravel bootstrap in `bootstrap/`.
  - Composer packages include `laravel/framework`, `laravel/breeze` (auth scaffolding), `laravel/pint` (formatter), `phpunit/phpunit` (tests), and `barryvdh/laravel-dompdf` (PDF).
- HTTP and routing
  - Web routes: `routes/web.php` provide the landing page, dashboard (auth + verified), and profile CRUD via `ProfileController`.
  - Auth routes: `routes/auth.php` (Breeze) handle register/login/password/verification flows.
  - API routes: `routes/api.php` exposes `POST /api/chat` → `App\Http\Controllers\Api\ChatController@handle` (placeholder returning a stubbed response; wire to OpenAI/local LLM later).
- Controllers
  - `App\Http\Controllers\Api\ChatController` currently echoes the `query` and a not-configured message. Intended for role-aware, DB-backed AI responses.
- Domain models and data
  - Eloquent models scaffolded in `app/Models/`: `Institution`, `Department`, `Course`, `Unit`, `Lecturer`, `Room`, `Timetable`, `TeachingGroup`, `ChatLog`, `User`.
  - Migrations exist for the above under `database/migrations/` but are placeholders (ids + timestamps). Extend these with real fields/relationships before relying on generation logic.
  - Default `.env.example` uses SQLite. Switch to MySQL by setting `DB_CONNECTION=mysql` and related creds.
- Frontend
  - Blade views under `resources/views/**` (Breeze layouts/components included).
  - Vite build via `vite.config.js` with entries `resources/css/app.css` and `resources/js/app.js`; Tailwind configured in `tailwind.config.js`.
  - `package.json` scripts: `dev` (Vite dev server w/ Laravel plugin refresh) and `build`.
- Testing
  - PHPUnit configured in `phpunit.xml` with `tests/Unit` and `tests/Feature` suites. Use `php artisan test` or `vendor/bin/phpunit` directly. Example tests present in both suites.
- Reporting/exports
  - `barryvdh/laravel-dompdf` available for generating printable PDFs (e.g., timetables and reports).

## Notes from README

- Project: “Smart University Timetable” with conflict-free generation by lecturer availability, room capacity, and shared courses; includes AI chat, real-time room tracking, and printable reports.
- Planned 3-hour time slots (7–10, 10–1, 1–4, 4–7). Role-based dashboards (Super Admin, Institution Admin, Lecturer, Student).
- API: `POST /api/chat` is a placeholder to be wired to OpenAI/local LLM.
