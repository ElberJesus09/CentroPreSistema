<?php

use App\Http\Controllers\AcademicCycleController;
use App\Http\Controllers\AcademicCycleShiftController;
use App\Http\Controllers\Academic\AcademicReportController;
use App\Http\Controllers\Academic\ClassroomController;
use App\Http\Controllers\Academic\DistributionController;
use App\Http\Controllers\Academic\GradeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamSettingsController;
use App\Http\Controllers\Public\CampusCatalogController;
use App\Http\Controllers\Public\CareerCatalogController;
use App\Http\Controllers\Public\PublicHomeController;
use App\Http\Controllers\Public\RegistrationWizardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StudentCardController;
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

Route::get('/registration/documents/{student}/{document}', [RegistrationWizardController::class, 'downloadDocument'])
    ->whereIn('document', ['enrollment_form', 'regulations'])
    ->middleware(['signed', 'throttle:public-registration'])
    ->name('registration.documents.download');

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

        Route::middleware('reports.module')->group(function (): void {
            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/students/pdf', [ReportController::class, 'download'])->name('reports.students.pdf');
            Route::get('reports/students/emails.txt', [ReportController::class, 'downloadEmails'])->name('reports.students.emails');
            Route::get('reports/activity/pdf', [ReportController::class, 'downloadActivity'])->name('reports.activity.pdf');
        });

        Route::middleware('academic-management.module')->prefix('academic')->name('academic.')->group(function (): void {
            Route::resource('classrooms', ClassroomController::class)->except(['show']);
            Route::get('distribution', [DistributionController::class, 'index'])->name('distribution.index');
            Route::post('distribution/import', [DistributionController::class, 'import'])->name('distribution.import');
            Route::post('distribution/import/confirm', [DistributionController::class, 'confirmImport'])->name('distribution.import.confirm');
            Route::post('distribution/run', [DistributionController::class, 'distribute'])->name('distribution.run');
            Route::post('distribution/move', [DistributionController::class, 'move'])->name('distribution.move');
            Route::post('distribution/assignments/{assignment}/lock', [DistributionController::class, 'toggleLock'])->name('distribution.lock');
            Route::get('grades', [GradeController::class, 'index'])->name('grades.index');
            Route::post('grades/import', [GradeController::class, 'import'])->name('grades.import');
            Route::post('grades/import/confirm', [GradeController::class, 'confirmImport'])->name('grades.import.confirm');
            Route::post('evaluations', [GradeController::class, 'storeEvaluation'])->name('evaluations.store');
            Route::put('evaluations/{evaluation}', [GradeController::class, 'updateEvaluation'])->name('evaluations.update');
            Route::delete('evaluations/{evaluation}', [GradeController::class, 'destroyEvaluation'])->name('evaluations.destroy');
            Route::get('reports', [AcademicReportController::class, 'index'])->name('reports.index');
            Route::get('reports/excel', [AcademicReportController::class, 'excel'])->name('reports.excel');
            Route::get('reports/pdf', [AcademicReportController::class, 'pdf'])->name('reports.pdf');
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
            Route::get('students/cards', [StudentCardController::class, 'create'])->name('students.cards.create');
            Route::get('students/cards/pdf', [StudentCardController::class, 'download'])->name('students.cards.download');
            Route::resource('students', StudentController::class);
        });
    });
});
