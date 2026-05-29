<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $reportService, ActivityLogService $activityLogService): View
    {
        $year = $this->optionalInt($request->query('year'));
        $careerId = $this->optionalInt($request->query('career_id'));
        $academicCycleId = $this->optionalInt($request->query('academic_cycle_id'));
        $shiftId = $this->optionalInt($request->query('shift_id'));
        $activityFilters = $this->activityFilters($request);
        $canViewActivityReport = $request->user()?->isSuperAdmin() ?? false;

        return view('reports.index', [
            'report' => $reportService->summary($year, $careerId, $academicCycleId, $shiftId),
            'filterYears' => $reportService->filterYearOptions(),
            'filterCareers' => $reportService->filterCareerOptions(),
            'filterCycles' => $reportService->filterCycleOptions(),
            'filterShifts' => $reportService->filterShiftOptions(),
            'filterYear' => $year,
            'filterCareerId' => $careerId,
            'filterAcademicCycleId' => $academicCycleId,
            'filterShiftId' => $shiftId,
            'canViewActivityReport' => $canViewActivityReport,
            'activityLogs' => $canViewActivityReport ? $activityLogService->paginate($activityFilters) : null,
            'activityFilters' => $activityFilters,
            'activityStaffOptions' => $canViewActivityReport ? $activityLogService->staffOptions() : collect(),
            'activityModuleOptions' => $activityLogService->moduleOptions(),
        ]);
    }

    public function download(Request $request, ReportService $reportService, ActivityLogService $activityLogService): Response
    {
        $this->authorizeReportExport($request);

        $year = $this->optionalInt($request->query('year'));
        $careerId = $this->optionalInt($request->query('career_id'));
        $academicCycleId = $this->optionalInt($request->query('academic_cycle_id'));
        $shiftId = $this->optionalInt($request->query('shift_id'));

        $pdf = Pdf::loadView('pdf.reports.students', [
            'report' => $reportService->summary($year, $careerId, $academicCycleId, $shiftId),
            'filters' => [
                'year' => $year,
                'career' => $careerId === null ? null : $reportService->filterCareerOptions()->firstWhere('id', $careerId)?->name,
                'cycle' => $academicCycleId === null ? null : $reportService->filterCycleOptions()->firstWhere('id', $academicCycleId)?->name,
                'shift' => $shiftId === null ? null : $reportService->filterShiftOptions()->firstWhere('id', $shiftId)?->name,
            ],
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $activityLogService->record('reports', 'download_pdf', 'Genero PDF de reporte de alumnos.', properties: [
            'year' => $year,
            'career_id' => $careerId,
            'academic_cycle_id' => $academicCycleId,
            'shift_id' => $shiftId,
        ], request: $request);

        return $pdf->download('reporte-alumnos-'.now()->format('Ymd-His').'.pdf');
    }

    public function downloadEmails(Request $request, ReportService $reportService, ActivityLogService $activityLogService): StreamedResponse
    {
        $this->authorizeReportExport($request);

        $year = $this->optionalInt($request->query('year'));
        $careerId = $this->optionalInt($request->query('career_id'));
        $academicCycleId = $this->optionalInt($request->query('academic_cycle_id'));
        $shiftId = $this->optionalInt($request->query('shift_id'));
        $emails = $reportService->studentEmails($year, $careerId, $academicCycleId, $shiftId);
        $filename = 'correos-alumnos-'.now()->format('Ymd-His').'.txt';

        $activityLogService->record('reports', 'download_txt', 'Genero TXT de correos de alumnos.', properties: [
            'year' => $year,
            'career_id' => $careerId,
            'academic_cycle_id' => $academicCycleId,
            'shift_id' => $shiftId,
        ], request: $request);

        return response()->streamDownload(function () use ($emails): void {
            echo $emails->implode(PHP_EOL);
            if ($emails->isNotEmpty()) {
                echo PHP_EOL;
            }
        }, $filename, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function downloadActivity(Request $request, ActivityLogService $activityLogService): Response
    {
        abort_unless($request->user()?->isSuperAdmin(), Response::HTTP_FORBIDDEN);

        $filters = $this->activityFilters($request);
        $logs = $activityLogService->forPdf($filters);

        $pdf = Pdf::loadView('pdf.reports.activity', [
            'logs' => $logs,
            'filters' => $filters,
            'staffName' => $this->activityStaffName($activityLogService, $filters['staff_id']),
            'moduleName' => $activityLogService->moduleLabel($filters['module']),
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        $activityLogService->record('reports', 'download_pdf', 'Genero PDF de auditoria de usuarios.', properties: $filters, request: $request);

        return $pdf->download('auditoria-usuarios-'.now()->format('Ymd-His').'.pdf');
    }

    private function optionalInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $i = filter_var($value, FILTER_VALIDATE_INT);

        return $i === false ? null : $i;
    }

    /**
     * @return array{date_from: ?string, date_to: ?string, staff_id: ?int, module: ?string}
     */
    private function activityFilters(Request $request): array
    {
        return [
            'date_from' => $this->optionalDate($request->query('activity_date_from')),
            'date_to' => $this->optionalDate($request->query('activity_date_to')),
            'staff_id' => $this->optionalInt($request->query('activity_staff_id')),
            'module' => $this->optionalString($request->query('activity_module')),
        ];
    }

    private function optionalDate(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : null;
    }

    private function optionalString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : mb_substr($value, 0, 80);
    }

    private function activityStaffName(ActivityLogService $activityLogService, ?int $staffId): string
    {
        if ($staffId === null) {
            return 'Todos';
        }

        $staff = $activityLogService->staffOptions()->firstWhere('id', $staffId);

        return $staff === null ? 'Usuario eliminado' : trim("{$staff->first_name} {$staff->last_name} ({$staff->username})");
    }

    private function authorizeReportExport(Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user !== null && ($user->can('reports.export') || $user->isSuperAdmin() || $user->isAdmin()),
            403,
        );
    }
}
