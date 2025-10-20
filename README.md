# Smart University Timetable (Laravel 11 + MySQL)

Build a Laravel 11 web application for Smart University Timetable Generation and Management. Generates conflict-free timetables based on lecturer availability, room capacity, and shared courses. Includes AI Chat, real-time room tracking, and printable reports.

## Tech Stack
- Laravel 11 (PHP 8.2)
- MySQL
- Blade + Tailwind CSS + Alpine.js (via Laravel Breeze)
- Chart.js
- barryvdh/laravel-dompdf (PDF)
- OpenAI API (gpt-4o-mini / gpt-4-turbo) via `/api/chat`

## Core Features (planned)
- AI-powered timetable generation (3-hour slots, 7 AM – 7 PM)
- Real-time room availability
- Role-based dashboards: Super Admin, Institution Admin, Lecturer, Student
- Printable/exportable reports (PDF)
- AI chat assistant with role-aware, DB-backed answers

## Initial Setup
1. Copy `.env` from example and set DB credentials.
2. Generate app key: `php artisan key:generate`
3. Install PHP deps: `composer install`
4. Install JS deps and build: `npm install && npm run build`
5. Run migrations: `php artisan migrate`

## API
- POST `/api/chat` → placeholder ChatController@handle (wire to OpenAI/local LLM later)

## Data Models (scaffolded)
Institution, Department, Course, Unit, Lecturer, Room, Timetable, TeachingGroup, ChatLog (+ default User)

## Time Slots
- 7–10, 10–1, 1–4, 4–7 (3-hour blocks). One lecturer/room per slot.

## Notes
- Configure OpenAI key and model in future iterations.
- Add RBAC middleware and dashboards per role.
