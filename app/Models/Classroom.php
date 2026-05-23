<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'academic_cycle_id',
    'name',
    'code',
    'floor',
    'capacity',
    'status',
    'academic_priority',
    'description',
])]
class Classroom extends Model
{
    use SoftDeletes;

    public function academicCycle(): BelongsTo
    {
        return $this->belongsTo(AcademicCycle::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(StudentClassroomAssignment::class);
    }

    public function activeAssignments(): HasMany
    {
        return $this->assignments();
    }

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
}
