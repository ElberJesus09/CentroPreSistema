<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\StudentCardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class StudentCardController extends Controller
{
    public function create(Request $request, StudentCardService $studentCardService): View
    {
        $this->authorize('viewAny', Student::class);

        return view('students.cards.create', [
            'cycles' => $studentCardService->cycles(),
            'careers' => $studentCardService->careers(),
            'shifts' => $studentCardService->shifts(),
            'filters' => [
                'student' => trim((string) $request->query('student', '')),
                'academic_cycle_id' => $this->optionalInt($request->query('academic_cycle_id')),
                'career_id' => $this->optionalInt($request->query('career_id')),
                'shift_id' => $this->optionalInt($request->query('shift_id')),
            ],
        ]);
    }

    public function download(Request $request, StudentCardService $studentCardService): Response
    {
        $this->authorize('viewAny', Student::class);

        $filters = [
            'student' => trim((string) $request->query('student', '')),
            'academic_cycle_id' => $this->optionalInt($request->query('academic_cycle_id')),
            'career_id' => $this->optionalInt($request->query('career_id')),
            'shift_id' => $this->optionalInt($request->query('shift_id')),
        ];

        $students = $studentCardService->studentsForCards($filters);
        $cards = $studentCardService->buildCardPayload($students);

        try {
            $pdf = Pdf::loadView('pdf.student-cards', [
                'cards' => $cards,
                'generatedAt' => now(),
            ])->setPaper('a4', 'portrait');

            $contents = $pdf->output();
        } finally {
            $studentCardService->deleteQrFiles($cards);
        }

        return response($contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="carnets-estudiantes-'.now()->format('Ymd-His').'.pdf"',
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
