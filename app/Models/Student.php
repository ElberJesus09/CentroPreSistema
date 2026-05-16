<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    'payment_voucher_number',
    'payment_agency_number',
    'payment_date',
    'registration_date',
    'guardian_id',
    'school_id',
    'career_id',
    'academic_cycle_id',
    'academic_cycle_shift_id',
    'admission_process_id',
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
     * @return BelongsTo<AcademicCycle, $this>
     */
    public function academicCycle(): BelongsTo
    {
        return $this->belongsTo(AcademicCycle::class);
    }

    /**
     * @return BelongsTo<AdmissionProcess, $this>
     */
    public function admissionProcess(): BelongsTo
    {
        return $this->belongsTo(AdmissionProcess::class);
    }

    /**
     * @return BelongsTo<AcademicCycleShift, $this>
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(AcademicCycleShift::class, 'academic_cycle_shift_id');
    }

    /**
     * @return HasMany<StudentMailLog, $this>
     */
    public function mailLogs(): HasMany
    {
        return $this->hasMany(StudentMailLog::class);
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
            'payment_date' => 'date',
            'registration_date' => 'date',
        ];
    }
}
