<?php

namespace App\Http\Controllers;

use App\Services\AcademicCycleService;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, AcademicCycleService $academicCycleService, DashboardService $dashboardService): View
    {
        $user = auth()->user();
        abort_unless(
            $user !== null && (
                $user->can('dashboard.view')
                || $user->canAccessStudentsModule()
                || $user->canAccessStaffModule()
                || $user->canAccessAcademicCyclesModule()
                || $user->canAccessAcademicManagementModule()
                || $user->canAccessReportsModule()
            ),
            Response::HTTP_FORBIDDEN,
        );

        $year = $this->optionalInt($request->query('year'));
        $careerId = $this->optionalInt($request->query('career_id'));
        $academicCycleId = $this->optionalInt($request->query('academic_cycle_id'));

        $academicMetrics = null;
        if ($user?->canAccessAcademicCyclesModule()) {
            $academicMetrics = $academicCycleService->dashboardAcademicMetrics($year, $academicCycleId);
        }

        $chartData = $dashboardService->chartData($user, $year, $careerId, $academicCycleId);
        $chartPayload = $dashboardService->chartPayloadForClient($chartData);
        $showCharts = $dashboardService->hasRenderableCharts($chartPayload);

        return view('dashboard.index', [
            'academicMetrics' => $academicMetrics,
            'chartData' => $chartData,
            'chartPayload' => $chartPayload,
            'showCharts' => $showCharts,
            'filterYears' => $user?->canAccessStudentsModule()
                ? $dashboardService->filterYearOptions()
                : collect(),
            'filterCareers' => $user?->canAccessStudentsModule()
                ? $dashboardService->filterCareerOptions()
                : collect(),
            'filterCycles' => $user?->canAccessStudentsModule() || $user?->canAccessAcademicCyclesModule()
                ? $dashboardService->filterCycleOptions()
                : collect(),
            'filterYear' => $year,
            'filterCareerId' => $careerId,
            'filterAcademicCycleId' => $academicCycleId,
        ]);
    }

    private function optionalInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $i = filter_var($value, FILTER_VALIDATE_INT);

        return $i === false ? null : $i;
    }
}
