<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GateController;
use App\Http\Controllers\GateGuardAssignmentController;
use App\Http\Controllers\GuardIssueReportController;
use App\Http\Controllers\GuardPushSubscriptionController;
use App\Http\Controllers\GuardScannerController;
use App\Http\Controllers\SubdivisionController;
use App\Http\Controllers\SubdivisionUserAssignmentController;
use Illuminate\Support\Facades\Auth;
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

// Root route - redirect based on auth status
Route::get('/', function () {
    if (Auth::check()) {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('super-admin') || $user->hasRole('admin')) {
            return redirect()->route('dashboard');
        } elseif ($user->hasRole('guard')) {
            return redirect()->route('guard.scanner');
        } elseif ($user->hasRole('employee')) {
            return redirect()->route('passes.index');
        } elseif ($user->hasRole('requester')) {
            return redirect()->route('requester.passes');
        }

        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    })->name('home');

    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    // Dashboard - main landing after login
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Guard routes
    Route::prefix('guard')->middleware('role:guard')->group(function () {
        Route::get('/scanner', [GuardScannerController::class, 'index'])->name('guard.scanner');
        Route::post('/scans', [GuardScannerController::class, 'store'])->name('guard.scans.store');
        Route::post('/shifts/start', [GuardScannerController::class, 'startShift'])->name('guard.shifts.start');
        Route::post('/shifts/end', [GuardScannerController::class, 'endShift'])->name('guard.shifts.end');
        Route::post('/issues', [GuardScannerController::class, 'reportIssue'])->name('guard.issues.store');
        Route::post('/passes/{pass}/approve', [GuardScannerController::class, 'approvePass'])->name('guard.passes.approve');
        Route::post('/passes/{pass}/reject', [GuardScannerController::class, 'rejectPass'])->name('guard.passes.reject');
        Route::post('/push-subscriptions', [GuardPushSubscriptionController::class, 'store'])->name('guard.push-subscriptions.store');
        Route::delete('/push-subscriptions', [GuardPushSubscriptionController::class, 'destroy'])->name('guard.push-subscriptions.destroy');
        Route::post('/pin/validate', [GuardScannerController::class, 'validatePin'])->name('guard.pin.validate');
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
        Route::resource('guard-issues', GuardIssueReportController::class)->only(['index', 'show', 'update']);
        Route::resource('subdivisions', SubdivisionController::class)->except(['show']);
        Route::resource('gates', GateController::class)->except(['show']);
        Route::post('subdivisions/{subdivision}/users', [SubdivisionUserAssignmentController::class, 'store'])
            ->name('subdivisions.users.store');
        Route::delete('subdivisions/{subdivision}/users/{user}', [SubdivisionUserAssignmentController::class, 'destroy'])
            ->name('subdivisions.users.destroy');
        Route::post('gates/{gate}/guards', [GateGuardAssignmentController::class, 'store'])
            ->name('gates.guards.store');
        Route::delete('gates/{gate}/guards/{user}', [GateGuardAssignmentController::class, 'destroy'])
            ->name('gates.guards.destroy');

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
