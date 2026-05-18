<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $reportService): View
    {
        $year = $this->optionalInt($request->query('year'));
        $careerId = $this->optionalInt($request->query('career_id'));
        $academicCycleId = $this->optionalInt($request->query('academic_cycle_id'));
        $shiftId = $this->optionalInt($request->query('shift_id'));

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
        ]);
    }

    public function download(Request $request, ReportService $reportService): Response
    {
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

        return $pdf->download('reporte-alumnos-'.now()->format('Ymd-His').'.pdf');
    }

    public function downloadEmails(Request $request, ReportService $reportService): StreamedResponse
    {
        $year = $this->optionalInt($request->query('year'));
        $careerId = $this->optionalInt($request->query('career_id'));
        $academicCycleId = $this->optionalInt($request->query('academic_cycle_id'));
        $shiftId = $this->optionalInt($request->query('shift_id'));
        $emails = $reportService->studentEmails($year, $careerId, $academicCycleId, $shiftId);
        $filename = 'correos-alumnos-'.now()->format('Ymd-His').'.txt';

        return response()->streamDownload(function () use ($emails): void {
            echo $emails->implode(PHP_EOL);
            if ($emails->isNotEmpty()) {
                echo PHP_EOL;
            }
        }, $filename, ['Content-Type' => 'text/plain; charset=UTF-8']);
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
