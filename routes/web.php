<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\Admin\SuperAdminController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/timetables/generate', [TimetableController::class, 'generateWeb'])->middleware(['auth']);
Route::get('/timetables/{timetable}', [TimetableController::class, 'show'])->middleware(['auth'])->whereNumber('timetable')->name('timetables.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Super Admin Routes
Route::middleware(['auth'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    
    // Admin Management
    Route::get('/admins', [SuperAdminController::class, 'manageAdmins'])->name('manage-admins');
    Route::post('/admins', [SuperAdminController::class, 'createAdmin'])->name('create-admin');
    Route::put('/admins/{admin}', [SuperAdminController::class, 'updateAdmin'])->name('update-admin');
    Route::delete('/admins/{admin}', [SuperAdminController::class, 'deleteAdmin'])->name('delete-admin');
    
    // Timetables
    Route::get('/timetables', [SuperAdminController::class, 'viewTimetables'])->name('timetables');
    
    // Reports
    Route::get('/reports/summary', [SuperAdminController::class, 'generateSummaryReport'])->name('generate-report');
    Route::get('/reports/export/{format}', [SuperAdminController::class, 'exportReport'])->name('export-report');
    
    // Institutions
    Route::get('/institutions', [SuperAdminController::class, 'manageInstitutions'])->name('institutions');
    Route::post('/institutions', [SuperAdminController::class, 'createInstitution'])->name('create-institution');
    Route::put('/institutions/{institution}', [SuperAdminController::class, 'updateInstitution'])->name('update-institution');
    Route::delete('/institutions/{institution}', [SuperAdminController::class, 'deleteInstitution'])->name('delete-institution');
    
    // Test route
    Route::get('/test', function() {
        return view('super-admin.dashboard-test', [
            'stats' => [
                'total_institutions' => 1,
                'total_admins' => 1,
                'total_timetables' => 0,
                'total_users' => 2
            ],
            'recent_institutions' => collect([]),
            'recent_admins' => collect([])
        ]);
    })->name('test');
    
    // Super simple test
    Route::get('/simple-test', function() {
        return view('simple-test', [
            'stats' => [
                'total_institutions' => 1,
                'total_admins' => 1
            ]
        ]);
    })->name('simple-test');
    
    // Debug dashboard test
    Route::get('/debug', function() {
        $stats = [
            'total_institutions' => 2,
            'total_admins' => 3,
            'total_users' => 10,
            'total_timetables' => 5,
        ];
        
        $recent_institutions = collect([
            (object)['name' => 'Test University', 'users_count' => 100, 'departments_count' => 5, 'created_at' => now()]
        ]);
        
        $recent_admins = collect([
            (object)['name' => 'John Admin', 'institution' => (object)['name' => 'Test Uni'], 'created_at' => now()]
        ]);
        
        return view('super-admin.dashboard', compact('stats', 'recent_institutions', 'recent_admins'));
    })->name('debug');
});

// Institution Admin Routes
Route::middleware(['auth', 'institution.admin'])->prefix('institution-admin')->name('institution-admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\InstitutionAdmin\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/generate-timetable', [\App\Http\Controllers\InstitutionAdmin\DashboardController::class, 'generateTimetable'])->name('generate-timetable');
    
    // Analytics and Reports
    Route::get('/analytics', [\App\Http\Controllers\InstitutionAdmin\DashboardController::class, 'analytics'])->name('analytics');
    Route::get('/reports', [\App\Http\Controllers\InstitutionAdmin\DashboardController::class, 'reports'])->name('reports');
    
    // Schools Management
    Route::resource('schools', App\Http\Controllers\InstitutionAdmin\SchoolController::class);

    // Department Management
    Route::resource('departments', App\Http\Controllers\InstitutionAdmin\DepartmentController::class);
    
    // Course Management
    Route::resource('courses', \App\Http\Controllers\InstitutionAdmin\CourseController::class);
    
    // Room Management
    Route::resource('rooms', \App\Http\Controllers\InstitutionAdmin\RoomController::class);
    
    // Lecturer Management
    Route::resource('lecturers', App\Http\Controllers\InstitutionAdmin\LecturerController::class);

    // Units Management
    Route::resource('units', App\Http\Controllers\InstitutionAdmin\UnitController::class);

    // Course-Unit-Year mapping (Institution Admin CRUD)
    Route::resource('course-unit-years', App\Http\Controllers\InstitutionAdmin\CourseUnitYearController::class);
    
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', function() { 
            $institution = auth()->user()->institution;
            $students = $institution->users()->where('role', 'student')->paginate(10);
            return view('institution-admin.placeholder', ['title' => 'Students', 'items' => $students, 'type' => 'student']);
        })->name('index');
    });
    
    // Timetable Management
    Route::resource('timetables', App\Http\Controllers\InstitutionAdmin\TimetableController::class);
    Route::post('timetables/{timetable}/toggle-status', [App\Http\Controllers\InstitutionAdmin\TimetableController::class, 'toggleStatus'])->name('timetables.toggle-status');
    Route::post('timetables/{timetable}/generate-entries', [App\Http\Controllers\InstitutionAdmin\TimetableController::class, 'generateEntries'])->name('timetables.generate-entries');
    Route::get('timetables/{timetable}/export-pdf', [App\Http\Controllers\InstitutionAdmin\TimetableController::class, 'exportPdf'])->name('timetables.export-pdf');
});

// Lecturer self-service routes
Route::middleware(['auth'])->prefix('lecturer')->name('lecturer.')->group(function () {
    Route::get('dashboard', [App\Http\Controllers\Lecturer\SelfServiceController::class, 'timetable'])->name('dashboard');
    Route::get('timetable', [App\Http\Controllers\Lecturer\SelfServiceController::class, 'timetable'])->name('timetable');
    Route::post('availability', [App\Http\Controllers\Lecturer\SelfServiceController::class, 'updateAvailability'])->name('availability.update');
    Route::get('assigned', [App\Http\Controllers\Lecturer\SelfServiceController::class, 'assigned'])->name('assigned');
    Route::get('rooms', [App\Http\Controllers\Lecturer\SelfServiceController::class, 'rooms'])->name('rooms');
    Route::post('schedule-change', [App\Http\Controllers\Lecturer\SelfServiceController::class, 'requestChange'])->name('schedule.change');
    Route::post('chatbot', [App\Http\Controllers\Lecturer\SelfServiceController::class, 'chatbot'])->name('chatbot');
});

require __DIR__.'/auth.php';
