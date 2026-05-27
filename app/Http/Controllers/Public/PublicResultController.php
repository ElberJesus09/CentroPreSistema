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

        $dni = trim((string) $request->query('dni', ''));
        $result = null;

        if ($request->has('dni')) {
            $request->validate([
                'dni' => ['required', 'digits:8'],
            ], [
                'dni.required' => 'Ingrese su DNI para consultar sus resultados.',
                'dni.digits' => 'El DNI debe contener exactamente 8 dígitos.',
            ]);

            $result = $service->publicResultForDni($dni);
        }

        return view('public.results.show', [
            'dni' => $dni,
            'result' => $result,
            'searched' => $request->has('dni') && $dni !== '',
        ]);
    }
}
