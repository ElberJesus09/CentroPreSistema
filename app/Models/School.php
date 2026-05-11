<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'department',
    'province',
    'district',
    'graduation_year',
])]
class School extends Model
{
    /**
     * @return HasMany<Student, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    protected function casts(): array
    {
        return [
            'graduation_year' => 'integer',
        ];
    }
}
