<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\{Department, Course, Room, User, Timetable};
use App\Policies\{DepartmentPolicy, CoursePolicy, RoomPolicy, UserPolicy, TimetablePolicy};

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
        // Map policies
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Room::class, RoomPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Timetable::class, TimetablePolicy::class);

        // Super admin can do anything
        Gate::before(function ($user, $ability) {
            return method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin() ? true : null;
        });
    }
}
