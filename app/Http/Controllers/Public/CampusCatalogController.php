<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\StudentService;
use Illuminate\View\View;

class CampusCatalogController extends Controller
{
    /** Listado publico de sedes activas. */
    public function __invoke(StudentService $studentService): View
    {
        return view('public.campuses', [
            'campuses' => $studentService->cachedActiveCampuses(),
        ]);
    }
}
