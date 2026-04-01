<?php

use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ForcePasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KycRecordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');
    Route::get('forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'active', 'password.change'])->group(function (): void {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('password/force', [ForcePasswordController::class, 'edit'])->name('password.force.edit');
    Route::post('password/force', [ForcePasswordController::class, 'update'])->name('password.force.update');

    Route::get('kyc/export', [KycRecordController::class, 'export'])
        ->middleware('throttle:export')
        ->name('kyc.export');
    Route::resource('kyc', KycRecordController::class);

    Route::middleware(['role:admin', 'throttle:admin-sensitive'])->prefix('admin')->name('admin.')->group(function (): void {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('users.reset-password');
    });

    Route::middleware(['reports', 'throttle:reports'])->group(function (): void {
        Route::get('reports', [ReportController::class, 'dashboard'])->name('reports.dashboard');
    });
});
