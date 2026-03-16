<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\TimetableController;
use App\Models\Course;

Route::post('/chat', [ChatController::class, 'handle']);
Route::delete('/chat', [ChatController::class, 'clear']);
Route::match(['get', 'post'], '/timetable/generate', [TimetableController::class, 'generate']);

// Get courses by institution (for student registration)
Route::get('/courses', function (\Illuminate\Http\Request $request) {
    $institutionId = $request->query('institution_id');
    
    if (!$institutionId) {
        return response()->json([]);
    }
    
    $courses = Course::whereHas('department', function($q) use ($institutionId) {
        $q->where('institution_id', $institutionId);
    })
    ->with('department')
    ->orderBy('name')
    ->get(['id', 'name', 'code', 'department_id']);
    
    return response()->json($courses);
});
