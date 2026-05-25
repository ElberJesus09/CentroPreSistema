<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ExamSetting;
use App\Services\Academic\GradeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicResultController extends Controller
{
    public function __invoke(Request $request, GradeService $service): View
    {
        $setting = ExamSetting::singleton();
        abort_unless($setting->public_results_enabled, 404);

        $dni = mb_substr(trim((string) $request->query('dni', '')), 0, 8);
        $result = null;

        if ($dni !== '') {
            $request->validate([
                'dni' => ['required', 'digits:8'],
            ]);

            $result = $service->publicResultForDni($dni);
        }

        return view('public.results.show', [
            'dni' => $dni,
            'result' => $result,
            'searched' => $dni !== '',
        ]);
    }
}
