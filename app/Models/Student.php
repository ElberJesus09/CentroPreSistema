<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'first_name',
    'last_name',
    'mother_last_name',
    'dni',
    'birth_date',
    'gender',
    'phone',
    'address',
    'email',
    'registration_date',
    'guardian_id',
    'school_id',
    'career_id',
    'academic_cycle_shift_id',
    'status',
])]
class Student extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_REJECTED = 'rejected';

    /**
     * @return BelongsTo<Guardian, $this>
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return BelongsTo<Career, $this>
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    /**
     * @return BelongsTo<AcademicCycleShift, $this>
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(AcademicCycleShift::class, 'academic_cycle_shift_id');
    }

    /** Nombre completo para listados. */
    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name} {$this->mother_last_name}");
    }

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'registration_date' => 'date',
        ];
    }
}
