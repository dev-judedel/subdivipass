<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\GuardScannerController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect('/login');
    });

    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    // Dashboard - main landing after login
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Guard routes
    Route::prefix('guard')->middleware('role:guard')->group(function () {
        Route::get('/scanner', [GuardScannerController::class, 'index'])->name('guard.scanner');
        Route::post('/scans', [GuardScannerController::class, 'store'])->name('guard.scans.store');
        Route::post('/shifts/start', [GuardScannerController::class, 'startShift'])->name('guard.shifts.start');
        Route::post('/shifts/end', [GuardScannerController::class, 'endShift'])->name('guard.shifts.end');
        Route::post('/issues', [GuardScannerController::class, 'reportIssue'])->name('guard.issues.store');
    });

    // Employee/Admin routes - Pass Management
    Route::middleware('role:employee|admin|super-admin')->group(function () {
        Route::resource('passes', \App\Http\Controllers\PassController::class);

        // Additional pass actions
        Route::post('passes/{pass}/approve', [\App\Http\Controllers\PassController::class, 'approve'])
            ->name('passes.approve')
            ->middleware('role:admin|super-admin');

        Route::post('passes/{pass}/reject', [\App\Http\Controllers\PassController::class, 'reject'])
            ->name('passes.reject')
            ->middleware('role:admin|super-admin');

        Route::post('passes/{pass}/revoke', [\App\Http\Controllers\PassController::class, 'revoke'])
            ->name('passes.revoke');

        Route::get('passes/{pass}/qr-download', [\App\Http\Controllers\PassController::class, 'downloadQR'])
            ->name('passes.qr.download');
    });

    // Admin routes - User Management
    Route::middleware('role:admin|super-admin')->group(function () {
        Route::resource('users', \App\Http\Controllers\UserController::class);
        Route::resource('roles', \App\Http\Controllers\RoleController::class)->except(['show']);
        Route::get('roles/activity', [\App\Http\Controllers\RoleController::class, 'activity'])->name('roles.activity');

        // Additional user actions
        Route::post('users/{user}/change-status', [\App\Http\Controllers\UserController::class, 'changeStatus'])
            ->name('users.change-status');

        Route::post('users/{user}/reset-password', [\App\Http\Controllers\UserController::class, 'resetPassword'])
            ->name('users.reset-password');

        // Pass Type Management
        Route::resource('pass-types', \App\Http\Controllers\PassTypeController::class);

        // Additional pass type actions
        Route::post('pass-types/{pass_type}/change-status', [\App\Http\Controllers\PassTypeController::class, 'changeStatus'])
            ->name('pass-types.change-status');

        Route::post('pass-types/update-order', [\App\Http\Controllers\PassTypeController::class, 'updateOrder'])
            ->name('pass-types.update-order');
    });

    // Requester routes
    Route::prefix('my-passes')->middleware('role:requester')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Requester/MyPasses');
        })->name('requester.passes');
    });
});
