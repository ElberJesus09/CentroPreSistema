<?php

use App\Http\Controllers\AcademicCycleController;
use App\Http\Controllers\AcademicCycleShiftController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShiftController;
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

    Route::middleware('academic-cycles.module')->prefix('academic-cycles')->name('academic-cycles.')->group(function (): void {
        Route::get('/', [AcademicCycleShiftController::class, 'index'])->name('index');
        Route::resource('cycles', AcademicCycleController::class)->parameters(['cycles' => 'academic_cycle'])->except(['show']);
        Route::resource('campuses', CampusController::class)->parameters(['campuses' => 'campus'])->except(['show']);
        Route::resource('shifts', ShiftController::class)->except(['show']);
        Route::resource('schedules', AcademicCycleShiftController::class)->parameters(['schedules' => 'schedule'])->except(['index', 'show']);
    });
});
