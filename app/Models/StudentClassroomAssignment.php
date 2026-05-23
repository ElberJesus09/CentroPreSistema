<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id',
    'academic_cycle_id',
    'classroom_id',
    'placement_score',
    'distribution_locked',
    'assigned_by',
    'assigned_at',
])]
class StudentClassroomAssignment extends Model
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicCycle(): BelongsTo
    {
        return $this->belongsTo(AcademicCycle::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_by');
    }

    protected function casts(): array
    {
        return [
            'distribution_locked' => 'boolean',
            'assigned_at' => 'datetime',
            'placement_score' => 'decimal:2',
        ];
    }
}
