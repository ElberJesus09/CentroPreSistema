<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'student_id',
    'evaluation_id',
    'score',
    'observations',
    'created_by',
])]
class Grade extends Model
{
    use SoftDeletes;

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }
}
