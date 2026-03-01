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
    Route::get('/timetables/{timetable}', [SuperAdminController::class, 'viewTimetable'])->name('timetables.view');
    Route::get('/timetables/{timetable}/download', [SuperAdminController::class, 'downloadTimetable'])->name('timetables.download');
    
    // Reports
    Route::get('/reports/summary', [SuperAdminController::class, 'generateSummaryReport'])->name('generate-report');
    Route::get('/reports/export/{format}', [SuperAdminController::class, 'exportReport'])->name('export-report');
    
    // Institutions
    Route::get('/institutions', [SuperAdminController::class, 'manageInstitutions'])->name('institutions');
    Route::post('/institutions', [SuperAdminController::class, 'createInstitution'])->name('create-institution');
    Route::put('/institutions/{institution}', [SuperAdminController::class, 'updateInstitution'])->name('update-institution');
    Route::patch('/institutions/{institution}/deactivate', [SuperAdminController::class, 'deactivateInstitution'])->name('institutions.deactivate');
    Route::patch('/institutions/{institution}/reactivate', [SuperAdminController::class, 'reactivateInstitution'])->name('institutions.reactivate');
    Route::delete('/institutions/{institution}', [SuperAdminController::class, 'deleteInstitution'])->name('delete-institution');

    // Deactivation for admins (replaces hard delete in UI)
    Route::patch('/admins/{admin}/deactivate', [SuperAdminController::class, 'deactivateAdmin'])->name('admins.deactivate');
    Route::patch('/admins/{admin}/reactivate', [SuperAdminController::class, 'reactivateAdmin'])->name('admins.reactivate');
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
    Route::patch('lecturers/{lecturer}/deactivate', [App\Http\Controllers\InstitutionAdmin\LecturerController::class, 'deactivate'])->name('lecturers.deactivate');
    Route::patch('lecturers/{lecturer}/activate', [App\Http\Controllers\InstitutionAdmin\LecturerController::class, 'activate'])->name('lecturers.activate');
    Route::resource('lecturers', App\Http\Controllers\InstitutionAdmin\LecturerController::class);

    // Units Management
    Route::resource('units', App\Http\Controllers\InstitutionAdmin\UnitController::class);

    // Course-Unit-Year mapping (Institution Admin CRUD)
    Route::resource('course-unit-years', App\Http\Controllers\InstitutionAdmin\CourseUnitYearController::class);
    
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [App\Http\Controllers\InstitutionAdmin\StudentController::class, 'index'])->name('index');
        Route::post('/{student}/approve', [App\Http\Controllers\InstitutionAdmin\StudentController::class, 'approve'])->name('approve');
        Route::post('/{student}/reject', [App\Http\Controllers\InstitutionAdmin\StudentController::class, 'reject'])->name('reject');
        Route::delete('/{student}', [App\Http\Controllers\InstitutionAdmin\StudentController::class, 'destroy'])->name('destroy');
    });
    
    // Student room requests
    Route::prefix('student-room-requests')->name('student-room-requests.')->group(function () {
        Route::get('/', [App\Http\Controllers\InstitutionAdmin\StudentRoomRequestController::class, 'index'])->name('index');
        Route::post('/{request}/approve', [App\Http\Controllers\InstitutionAdmin\StudentRoomRequestController::class, 'approve'])->name('approve');
        Route::post('/{request}/reject', [App\Http\Controllers\InstitutionAdmin\StudentRoomRequestController::class, 'reject'])->name('reject');
    });
    
    // Timetable Management
    // Place specific routes before resource route to avoid conflicts
    // Approvals page no longer used; approval/publish is done inline on the timetable
    // Route::get('timetables/approvals', [App\Http\Controllers\InstitutionAdmin\TimetableController::class, 'approvals'])->name('timetables.approvals');
    Route::post('timetables/{timetable}/request-approval', [App\Http\Controllers\InstitutionAdmin\TimetableController::class, 'requestApproval'])->name('timetables.request-approval');
    Route::post('timetables/{timetable}/approve', [App\Http\Controllers\InstitutionAdmin\TimetableController::class, 'approve'])->name('timetables.approve');
    Route::post('timetables/{timetable}/approve-and-publish', [App\Http\Controllers\InstitutionAdmin\TimetableController::class, 'approveAndPublish'])->name('timetables.approve-and-publish');
    Route::post('timetables/{timetable}/reject', [App\Http\Controllers\InstitutionAdmin\TimetableController::class, 'reject'])->name('timetables.reject');
    Route::post('timetables/{timetable}/toggle-status', [App\Http\Controllers\InstitutionAdmin\TimetableController::class, 'toggleStatus'])->name('timetables.toggle-status');
    Route::post('timetables/{timetable}/generate-entries', [App\Http\Controllers\InstitutionAdmin\TimetableController::class, 'generateEntries'])->name('timetables.generate-entries');
    Route::get('timetables/{timetable}/export-pdf', [App\Http\Controllers\InstitutionAdmin\TimetableController::class, 'exportPdf'])->name('timetables.export-pdf');
    Route::resource('timetables', App\Http\Controllers\InstitutionAdmin\TimetableController::class);
});

// Lecturer self-service routes
Route::middleware(['auth'])->prefix('lecturer')->name('lecturer.')->group(function () {
    Route::get('dashboard', [App\Http\Controllers\Lecturer\SelfServiceController::class, 'timetable'])->name('dashboard');
    Route::get('timetable', [App\Http\Controllers\Lecturer\SelfServiceController::class, 'timetable'])->name('timetable');
    Route::post('availability/toggle', [App\Http\Controllers\Lecturer\SelfServiceController::class, 'toggleAvailability'])->name('availability.toggle');
    Route::get('assigned', [App\Http\Controllers\Lecturer\SelfServiceController::class, 'assigned'])->name('assigned');
    Route::get('rooms', [App\Http\Controllers\Lecturer\SelfServiceController::class, 'rooms'])->name('rooms');
    Route::post('schedule-change', [App\Http\Controllers\Lecturer\SelfServiceController::class, 'requestChange'])->name('schedule.change');
    Route::post('chatbot', [App\Http\Controllers\Lecturer\SelfServiceController::class, 'chatbot'])->name('chatbot');
    Route::get('export/csv', [App\Http\Controllers\Lecturer\SelfServiceController::class, 'exportCSV'])->name('export.csv');
    Route::get('export/pdf', [App\Http\Controllers\Lecturer\SelfServiceController::class, 'exportPDF'])->name('export.pdf');
    
    // Room bookings
    Route::resource('room-bookings', App\Http\Controllers\Lecturer\RoomBookingController::class);
});

// Student routes
Route::middleware(['auth'])->prefix('student')->name('student.')->group(function () {
    Route::get('dashboard', [App\Http\Controllers\Student\StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('timetable', [App\Http\Controllers\Student\StudentController::class, 'timetable'])->name('timetable');
    Route::get('timetable/full', [App\Http\Controllers\Student\StudentController::class, 'fullTimetable'])->name('timetable.full');
    Route::get('rooms', [App\Http\Controllers\Student\StudentController::class, 'rooms'])->name('rooms');
    Route::post('chatbot', [App\Http\Controllers\Student\StudentController::class, 'chatbot'])->name('chatbot');
    Route::get('timetable/print', [App\Http\Controllers\Student\StudentController::class, 'printTimetable'])->name('timetable.print');
    Route::put('profile', [App\Http\Controllers\Student\StudentController::class, 'updateProfile'])->name('profile.update');
    
    // Room booking requests
    Route::resource('room-requests', App\Http\Controllers\Student\RoomRequestController::class);
});

require __DIR__.'/auth.php';
