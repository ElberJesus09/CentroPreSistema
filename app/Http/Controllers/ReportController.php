<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $reportService): View
    {
        $year = $this->optionalInt($request->query('year'));
        $careerId = $this->optionalInt($request->query('career_id'));
        $academicCycleId = $this->optionalInt($request->query('academic_cycle_id'));

        return view('reports.index', [
            'report' => $reportService->summary($year, $careerId, $academicCycleId),
            'filterYears' => $reportService->filterYearOptions(),
            'filterCareers' => $reportService->filterCareerOptions(),
            'filterCycles' => $reportService->filterCycleOptions(),
            'filterYear' => $year,
            'filterCareerId' => $careerId,
            'filterAcademicCycleId' => $academicCycleId,
        ]);
    }

    public function download(Request $request, ReportService $reportService): Response
    {
        $year = $this->optionalInt($request->query('year'));
        $careerId = $this->optionalInt($request->query('career_id'));
        $academicCycleId = $this->optionalInt($request->query('academic_cycle_id'));

        $pdf = Pdf::loadView('pdf.reports.students', [
            'report' => $reportService->summary($year, $careerId, $academicCycleId),
            'filters' => [
                'year' => $year,
                'career' => $careerId === null ? null : $reportService->filterCareerOptions()->firstWhere('id', $careerId)?->name,
                'cycle' => $academicCycleId === null ? null : $reportService->filterCycleOptions()->firstWhere('id', $academicCycleId)?->name,
            ],
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('reporte-alumnos-'.now()->format('Ymd-His').'.pdf');
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
