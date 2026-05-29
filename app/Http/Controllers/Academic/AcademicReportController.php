<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Services\Academic\GradeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AcademicReportController extends Controller
{
    public function index(Request $request, GradeService $service): View
    {
        $cycleId = $this->optionalInt($request->query('academic_cycle_id')) ?? $service->cycles()->first()?->id;
        $filters = [
            'career_id' => $this->optionalInt($request->query('career_id')),
        ];
        $report = $cycleId === null ? null : $service->reports($cycleId, $filters);

        return view('academic.reports.index', [
            'cycles' => $service->cycles(),
            'careers' => $service->careers(),
            'cycleId' => $cycleId,
            'filters' => $filters,
            'report' => $report,
        ]);
    }

    public function excel(Request $request, GradeService $service): StreamedResponse
    {
        $this->authorizeReportExport($request);

        $request->validate([
            'academic_cycle_id' => ['required', 'integer', 'exists:academic_cycles,id'],
            'career_id' => ['nullable', 'integer', 'exists:careers,id'],
        ]);

        return $service->exportExcel($request->integer('academic_cycle_id'), [
            'career_id' => $this->optionalInt($request->query('career_id')),
        ]);
    }

    public function pdf(Request $request, GradeService $service): Response
    {
        $this->authorizeReportExport($request);

        $request->validate([
            'academic_cycle_id' => ['required', 'integer', 'exists:academic_cycles,id'],
            'career_id' => ['nullable', 'integer', 'exists:careers,id'],
        ]);

        $pdf = Pdf::loadView('pdf.academic.report', [
            'report' => $service->reports($request->integer('academic_cycle_id'), [
                'career_id' => $this->optionalInt($request->query('career_id')),
            ]),
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('reporte-academico-'.now()->format('Ymd-His').'.pdf');
    }

    private function optionalInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $i = filter_var($value, FILTER_VALIDATE_INT);

        return $i === false ? null : $i;
    }

    private function authorizeReportExport(Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user !== null && ($user->can('academic.reports.export') || $user->isSuperAdmin() || $user->isAdmin()),
            403,
        );
    }
}
