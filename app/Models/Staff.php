<?php

namespace App\Models;

use Database\Factories\StaffFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

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
    /** @use HasFactory<StaffFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * @return HasMany<StaffTemporaryPermissionGrant, $this>
     */
    public function temporaryPermissionGrants(): HasMany
    {
        return $this->hasMany(StaffTemporaryPermissionGrant::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::NAME_SUPER_ADMIN) || $this->role?->name === Role::NAME_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::NAME_ADMIN) || $this->role?->name === Role::NAME_ADMIN;
    }

    /** Acceso al modulo de empleados (sidebar y rutas). */
    public function canAccessStaffModule(): bool
    {
        return $this->can('staff.view') || $this->isSuperAdmin() || $this->isAdmin();
    }

    /** Acceso a ciclos academicos, sedes y turnos (solo admin). */
    public function canAccessAcademicCyclesModule(): bool
    {
        return $this->can('academic-cycles.view') || $this->isSuperAdmin() || $this->isAdmin();
    }

    /** Modulo alumnos: todos los roles de staff operativos. */
    public function canAccessStudentsModule(): bool
    {
        $name = $this->role?->name;

        return $this->can('students.view') || in_array($name, [
            Role::NAME_SUPER_ADMIN,
            Role::NAME_ADMIN,
            Role::NAME_TRABAJADOR,
        ], true);
    }

    /** Modulo reportes: solo administradores. */
    public function canAccessReportsModule(): bool
    {
        return $this->can('reports.view') || $this->isSuperAdmin() || $this->isAdmin();
    }

    /** Modulo academico: administracion completa para admin; lectura/carga operativa para staff academico. */
    public function canAccessAcademicManagementModule(): bool
    {
        $name = $this->role?->name;

        return $this->can('academic.view') || in_array($name, [
            Role::NAME_SUPER_ADMIN,
            Role::NAME_ADMIN,
            Role::NAME_TRABAJADOR,
            Role::NAME_DOCENTE,
            Role::NAME_ASISTENTE,
        ], true);
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
