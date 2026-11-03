<?php

use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContentController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:app-login');
Route::post('/auth/google', [AuthController::class, 'google'])->middleware('throttle:google-login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::patch('/user', [AuthController::class, 'updateUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/questions', [AssessmentController::class, 'questions']);
    Route::get('/content', [ContentController::class, 'index']);
    Route::post('/assessment', [AssessmentController::class, 'store']);
    Route::get('/assessments', [AssessmentController::class, 'index']);
    Route::get('/assessment/{id}', [AssessmentController::class, 'show'])->whereNumber('id');
});
