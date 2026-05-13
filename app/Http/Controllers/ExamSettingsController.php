<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExamSettings\UpdateExamSettingsRequest;
use App\Models\ExamSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExamSettingsController extends Controller
{
    public function edit(): View
    {
        $examSetting = ExamSetting::singleton();
        $this->authorize('update', $examSetting);

        return view('exam-settings.edit', [
            'examSetting' => $examSetting,
        ]);
    }

    public function update(UpdateExamSettingsRequest $request): RedirectResponse
    {
        $examSetting = ExamSetting::singleton();
        $this->authorize('update', $examSetting);
        $examSetting->fill($request->validated());
        $examSetting->save();

        return redirect()->route('exam-settings.edit')->with('success', 'Datos del examen actualizados.');
    }
}
