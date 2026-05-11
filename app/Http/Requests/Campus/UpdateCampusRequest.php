<?php

namespace App\Http\Requests\Campus;

use App\Models\Campus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Campus|null $campus */
        $campus = $this->route('campus');

        return $campus !== null && ($this->user()?->can('update', $campus) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Campus $campus */
        $campus = $this->route('campus');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('campuses', 'name')->ignore($campus->id),
            ],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'boolean'],
        ];
    }
}
