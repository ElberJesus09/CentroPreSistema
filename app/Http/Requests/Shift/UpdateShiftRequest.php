<?php

namespace App\Http\Requests\Shift;

use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Shift|null $shift */
        $shift = $this->route('shift');

        return $shift !== null && ($this->user()?->can('update', $shift) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Shift $shift */
        $shift = $this->route('shift');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('shifts', 'name')->ignore($shift->id),
            ],
            'status' => ['required', 'boolean'],
        ];
    }
}
