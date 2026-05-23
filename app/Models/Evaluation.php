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
    'type',
    'weight',
    'counts_for_average',
    'rounding_decimals',
    'status',
    'created_by',
])]
class Evaluation extends Model
{
    use SoftDeletes;

    public const TYPE_PLACEMENT = 'ubicacion';

    public function academicCycle(): BelongsTo
    {
        return $this->belongsTo(AcademicCycle::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'counts_for_average' => 'boolean',
            'status' => 'boolean',
        ];
    }
}
