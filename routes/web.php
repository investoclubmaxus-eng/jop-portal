<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JobOpeningController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PublicJobController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Candidate-Facing Routes (Module 4)
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicJobController::class, 'index'])->name('public.jobs.home');
Route::get('/jobs', [PublicJobController::class, 'index'])->name('public.jobs.index');
Route::get('/jobs/{jobCode}', [PublicJobController::class, 'show'])->name('public.jobs.show');
Route::get('/jobs/{jobCode}/apply', [PublicJobController::class, 'showApplyForm'])->name('public.jobs.apply-form');
Route::post('/jobs/{jobCode}/apply', [PublicJobController::class, 'apply'])->name('public.jobs.apply');

/*
|--------------------------------------------------------------------------
| Admin Portal Routes (Modules 1, 2, 3)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest Admin Auth
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

        Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');

        Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    });

    // Protected Admin Area
    Route::middleware(['admin.auth'])->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Job Opening Management (Module 3)
        Route::resource('jobs', JobOpeningController::class);
        Route::patch('jobs/{job}/status', [JobOpeningController::class, 'updateStatus'])->name('jobs.update-status');

        // Admin Profile & Password Change
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        // Admin Accounts & Role Management
        Route::middleware(['admin.role:super_admin,admin'])->prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/restore', [UserController::class, 'restore'])->name('restore');
        });
    });
});
