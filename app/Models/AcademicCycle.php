<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'status',
    'start_date',
    'end_date',
])]
class AcademicCycle extends Model
{
    /**
     * @return HasMany<AcademicCycleShift, $this>
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(AcademicCycleShift::class);
    }

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}
