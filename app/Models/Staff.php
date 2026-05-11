<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'first_name',
    'last_name',
    'mother_last_name',
    'dni',
    'phone',
    'email',
    'username',
    'password',
    'last_login_at',
    'role_id',
    'status',
])]
#[Hidden(['password', 'remember_token'])]
class Staff extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\StaffFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->name === Role::NAME_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->role?->name === Role::NAME_ADMIN;
    }

    /** Acceso al modulo de empleados (sidebar y rutas). */
    public function canAccessStaffModule(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'status' => 'boolean',
        ];
    }
}
