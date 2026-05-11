<?php

namespace App\Http\Requests\AcademicCycleShift;

use App\Models\AcademicCycleShift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicCycleShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var AcademicCycleShift|null $schedule */
        $schedule = $this->route('schedule');

        return $schedule !== null && ($this->user()?->can('update', $schedule) ?? false);
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('enrolled') || $this->input('enrolled') === '' || $this->input('enrolled') === null) {
            $this->merge(['enrolled' => 0]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var AcademicCycleShift $schedule */
        $schedule = $this->route('schedule');

        return [
            'academic_cycle_id' => [
                'required',
                'integer',
                Rule::exists('academic_cycles', 'id'),
                Rule::unique('academic_cycle_shifts', 'academic_cycle_id')
                    ->where(function ($query) {
                        return $query
                            ->where('campus_id', $this->input('campus_id'))
                            ->where('shift_id', $this->input('shift_id'));
                    })
                    ->ignore($schedule->id),
            ],
            'campus_id' => ['required', 'integer', Rule::exists('campuses', 'id')],
            'shift_id' => ['required', 'integer', Rule::exists('shifts', 'id')],
            'capacity' => ['required', 'integer', 'min:1'],
            'enrolled' => ['required', 'integer', 'min:0', 'lte:capacity'],
            'status' => ['required', 'boolean'],
        ];
    }
}
