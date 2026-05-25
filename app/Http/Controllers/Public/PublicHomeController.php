<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ExamSetting;
use App\Services\StudentService;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    /** Landing institucional (portal publico). */
    public function __invoke(StudentService $studentService): View
    {
        return view('public.landing', [
            'careerCount' => $studentService->cachedActiveCareers()->count(),
            'campusCount' => $studentService->cachedActiveCampuses()->count(),
            'openSlots' => $studentService->cachedPublicAvailableSchedules()->count(),
            'publicResultsEnabled' => ExamSetting::singleton()->public_results_enabled,
        ]);
    }
}
