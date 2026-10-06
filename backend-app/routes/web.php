<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\AssessmentController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\TipController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });

    Route::middleware(['auth', 'auth.session', 'admin'])->group(function () {
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/search', SearchController::class)->name('search');
        Route::post('/translate', TranslationController::class)->middleware('throttle:60,1')->name('translate');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        Route::resource('users', UserController::class)->except(['create', 'store']);
        Route::patch('/users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.status');
        Route::put('/users/{user}/password', [UserController::class, 'resetPassword'])->name('users.password');

        Route::resource('questions', QuestionController::class)->except(['show']);
        Route::patch('/questions/{question}/move', [QuestionController::class, 'move'])->name('questions.move');

        Route::resource('tips', TipController::class)->except(['show']);
        Route::patch('/tips/{tip}/move', [TipController::class, 'move'])->name('tips.move');

        Route::get('/assessments/export', [AssessmentController::class, 'export'])->name('assessments.export');
        Route::resource('assessments', AssessmentController::class)->only(['index', 'show', 'destroy']);

        Route::get('/activity', [ActivityController::class, 'index'])->name('activity');
        Route::get('/activity/export', [ActivityController::class, 'export'])->name('activity.export');
    });
});
