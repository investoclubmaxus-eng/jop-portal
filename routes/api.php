<?php

use App\Http\Controllers\Api\Admin\ApplicationController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\JobOpeningController;
use App\Http\Controllers\Api\Admin\ProfileController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\PublicJobController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Candidate-Facing APIs (Modules 4, 5, 6)
|--------------------------------------------------------------------------
*/
Route::prefix('jobs')->group(function () {
    Route::get('/', [PublicJobController::class, 'index']);
    Route::get('/{jobCode}', [PublicJobController::class, 'show']);
    Route::post('/{jobCode}/apply', [PublicJobController::class, 'apply']);
});

// Document Preview / Download
Route::get('/documents/{id}/download', [PublicJobController::class, 'downloadDocument']);

/*
|--------------------------------------------------------------------------
| Admin Portal APIs (Modules 1, 2, 3, 7, 8, 9, 10)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    // Public Admin Auth Endpoints
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Protected Admin Endpoints
    Route::middleware(['admin.auth'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Dashboard Metrics & Breakdown (Module 2)
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Profile & Password Management (Module 1)
        Route::get('/profile', [ProfileController::class, 'index']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
        Route::put('/password', [ProfileController::class, 'updatePassword']);

        // Job Opening Management (Module 3)
        Route::apiResource('jobs', JobOpeningController::class);
        Route::patch('/jobs/{job}/status', [JobOpeningController::class, 'updateStatus']);

        // Application Management, Screening, Shortlisting & CRM Transfer (Modules 7, 8, 9, 10)
        Route::get('/applications/export', [ApplicationController::class, 'export']);
        Route::post('/applications/bulk-status', [ApplicationController::class, 'bulkUpdateStatus']);
        Route::post('/applications/crm-transfer', [ApplicationController::class, 'transferToCrm']);
        Route::get('/applications', [ApplicationController::class, 'index']);
        Route::get('/applications/{application}', [ApplicationController::class, 'show']);
        Route::patch('/applications/{application}/status', [ApplicationController::class, 'updateStatus']);

        // Admin Accounts & Role Management (Super Admin & Admin only)
        Route::middleware(['admin.role:super_admin,admin'])->prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::put('/{user}', [UserController::class, 'update']);
            Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus']);
            Route::delete('/{user}', [UserController::class, 'destroy']);
            Route::post('/{id}/restore', [UserController::class, 'restore']);
        });
    });
});
