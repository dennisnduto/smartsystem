<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, Institution, Department, Lecturer};

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('user:make-inst-admin {email} {--password=}', function (string $email, ?string $password = null) {
    $password = $password ?: 'password';

    $inst = Institution::first() ?: Institution::create(['name' => 'Smart Uni']);
    $user = User::firstOrNew(['email' => $email]);
    $user->name = 'Institution Admin';
    $user->password = Hash::make($password);
    $user->role = 'institution_admin';
    $user->institution_id = $inst->id;
    $user->save();

    $this->info("Institution admin upserted: {$email} / {$password}");
})->purpose('Create or reset an institution admin user');

// Schedule room booking auto-release
use Illuminate\Support\Facades\Schedule;
Schedule::call(function () {
    $job = new \App\Jobs\ReleaseExpiredRoomBookings();
    $job->handle();
})->everyMinute();
