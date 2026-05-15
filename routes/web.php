<?php

use App\Http\Controllers\AcademicCycleController;
use App\Http\Controllers\AcademicCycleShiftController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamSettingsController;
use App\Http\Controllers\Public\CampusCatalogController;
use App\Http\Controllers\Public\CareerCatalogController;
use App\Http\Controllers\Public\PublicHomeController;
use App\Http\Controllers\Public\RegistrationWizardController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal publico (sin autenticacion)
|--------------------------------------------------------------------------
*/

Route::get('/', PublicHomeController::class)->name('home');

Route::get('/careers', CareerCatalogController::class)->name('careers');
Route::get('/campuses', CampusCatalogController::class)->name('campuses');

Route::redirect('/register', '/registration')->name('register');
Route::redirect('/pre-registration', '/registration')->name('pre-registration.create');

Route::middleware('throttle:public-registration')->group(function (): void {
    Route::get('/registration', [RegistrationWizardController::class, 'start'])->name('registration.start');
    Route::get('/registration/step/{step}', [RegistrationWizardController::class, 'show'])
        ->whereNumber('step')
        ->name('registration.step.show');
    Route::post('/registration/step/1', [RegistrationWizardController::class, 'storeStep1'])->name('registration.step1.store');
    Route::post('/registration/step/2', [RegistrationWizardController::class, 'storeStep2'])->name('registration.step2.store');
    Route::post('/registration/step/3', [RegistrationWizardController::class, 'storeStep3'])->name('registration.step3.store');
    Route::post('/registration/step/4', [RegistrationWizardController::class, 'storeStep4'])->name('registration.step4.store');
});

Route::post('/registration/finish', [RegistrationWizardController::class, 'finish'])
    ->middleware('throttle:public-registration-finish')
    ->name('registration.finish');

Route::get('/registration/dni-lookup', [RegistrationWizardController::class, 'lookupDni'])
    ->middleware('throttle:public-registration-lookup')
    ->name('registration.dni-lookup');

Route::get('/registration/complete', [RegistrationWizardController::class, 'complete'])
    ->name('registration.complete');

/*
|--------------------------------------------------------------------------
| Panel administrativo (/admin)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->middleware('throttle:admin-login');
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

        Route::middleware('students.module')->group(function (): void {
            Route::get('exam-settings/edit', [ExamSettingsController::class, 'edit'])->name('exam-settings.edit');
            Route::put('exam-settings', [ExamSettingsController::class, 'update'])->name('exam-settings.update');
            Route::post('students/{student}/registration-mail/resend', [StudentController::class, 'resendRegistrationMail'])
                ->middleware('throttle:admin-student-mail-resend')
                ->name('students.registration-mail.resend');
            Route::get('students/{student}/registration-documents/download', [StudentController::class, 'downloadRegistrationDocuments'])
                ->middleware('throttle:admin-student-documents-download')
                ->name('students.registration-documents.download');
            Route::resource('students', StudentController::class)->except(['show']);
        });
    });
});
