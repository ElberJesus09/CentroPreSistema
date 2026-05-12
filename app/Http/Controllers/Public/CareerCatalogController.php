<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\StudentService;
use Illuminate\View\View;

class CareerCatalogController extends Controller
{
    /** Listado publico de carreras activas. */
    public function __invoke(StudentService $studentService): View
    {
        return view('public.careers', [
            'careers' => $studentService->cachedActiveCareers(),
        ]);
    }
}
