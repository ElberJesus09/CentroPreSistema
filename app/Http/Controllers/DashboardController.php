<?php

namespace App\Http\Controllers;

use App\Services\AcademicCycleService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AcademicCycleService $academicCycleService): View
    {
        $academicMetrics = null;
        if (auth()->user()?->canAccessAcademicCyclesModule()) {
            $academicMetrics = $academicCycleService->dashboardAcademicMetrics();
        }

        return view('dashboard.index', [
            'academicMetrics' => $academicMetrics,
        ]);
    }
}
