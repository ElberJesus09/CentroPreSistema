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
        $report = $cycleId === null ? null : $service->reports($cycleId, []);

        return view('academic.reports.index', [
            'cycles' => $service->cycles(),
            'cycleId' => $cycleId,
            'report' => $report,
        ]);
    }

    public function excel(Request $request, GradeService $service): StreamedResponse
    {
        $request->validate(['academic_cycle_id' => ['required', 'integer', 'exists:academic_cycles,id']]);

        return $service->exportExcel($request->integer('academic_cycle_id'));
    }

    public function pdf(Request $request, GradeService $service): Response
    {
        $request->validate(['academic_cycle_id' => ['required', 'integer', 'exists:academic_cycles,id']]);

        $pdf = Pdf::loadView('pdf.academic.report', [
            'report' => $service->reports($request->integer('academic_cycle_id')),
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
}
