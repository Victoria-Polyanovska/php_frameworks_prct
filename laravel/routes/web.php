<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/courses', [CourseController::class, 'index']);
Route::post('/api/courses', [CourseController::class, 'store']);
Route::patch('/api/courses/{id}', [CourseController::class, 'update']);
Route::delete('/api/courses/{id}', [CourseController::class, 'destroy']);