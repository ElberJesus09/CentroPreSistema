<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function (): void {
    Route::post('logout', LogoutController::class)->name('logout');
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('staff.module')->group(function (): void {
        Route::resource('staff', StaffController::class)->except(['show']);
    });
});
