<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'status',
])]
class Shift extends Model
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
        ];
    }
}
