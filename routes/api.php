<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\TimetableController;

Route::post('/chat', [ChatController::class, 'handle']);
Route::match(['get', 'post'], '/timetable/generate', [TimetableController::class, 'generate']);

