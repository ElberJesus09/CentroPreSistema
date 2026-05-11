<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory;
    public const NAME_SUPER_ADMIN = 'super_admin';

    public const NAME_ADMIN = 'admin';

    public const NAME_TRABAJADOR = 'trabajador';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * @return HasMany<Staff, $this>
     */
    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
}
