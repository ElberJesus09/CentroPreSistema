<?php

namespace App\Http\Controllers;

use App\Services\AcademicCycleService;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AcademicCycleService $academicCycleService, DashboardService $dashboardService): View
    {
        $user = auth()->user();
        $academicMetrics = null;
        if ($user?->canAccessAcademicCyclesModule()) {
            $academicMetrics = $academicCycleService->dashboardAcademicMetrics();
        }

        $chartData = $dashboardService->chartData($user);
        $chartPayload = $dashboardService->chartPayloadForClient($chartData);
        $showCharts = $dashboardService->hasRenderableCharts($chartPayload);

        return view('dashboard.index', [
            'academicMetrics' => $academicMetrics,
            'chartData' => $chartData,
            'chartPayload' => $chartPayload,
            'showCharts' => $showCharts,
        ]);
    }
}
