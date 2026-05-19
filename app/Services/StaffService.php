<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Staff;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StaffService
{
    /** Listado paginado para administracion. */
    public function paginateIndex(?Staff $actor = null, int $perPage = 15): LengthAwarePaginator
    {
        return Staff::query()
            ->with('role')
            ->when(
                ! $actor?->isSuperAdmin(),
                fn (Builder $query) => $query->whereDoesntHave(
                    'role',
                    fn (Builder $query) => $query->where('name', Role::NAME_SUPER_ADMIN)
                )
            )
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /** Alta desde datos ya validados. */
    public function create(array $attributes): Staff
    {
        return Staff::query()->create($attributes);
    }

    /** Actualizacion; omite password si viene vacio. */
    public function update(Staff $staff, array $attributes): Staff
    {
        if (empty($attributes['password'])) {
            unset($attributes['password']);
        }

        $staff->update($attributes);

        return $staff->fresh()->load('role');
    }

    /** Baja logica (soft delete). */
    public function delete(Staff $staff): void
    {
        $staff->delete();
    }

    /** Busqueda por credencial de login. */
    public function findActiveByUsername(string $username): ?Staff
    {
        /** @var Staff|null $staff */
        $staff = Staff::query()
            ->where('username', $username)
            ->where('status', true)
            ->first();

        return $staff;
    }

    public function recordLogin(Staff $staff): void
    {
        $staff->forceFill(['last_login_at' => now()])->save();
    }

    /** Roles asignables en formularios (excluye inactivos si aplica). */
    public function assignableRolesQuery(?Staff $actor = null, ?Staff $staff = null): Builder
    {
        $allowed = Role::assignableNamesForActor($actor);

        if (
            $actor?->isSuperAdmin()
            && $staff?->role?->name === Role::NAME_SUPER_ADMIN
            && ! in_array(Role::NAME_SUPER_ADMIN, $allowed, true)
        ) {
            $allowed[] = Role::NAME_SUPER_ADMIN;
        }

        $query = Role::query()
            ->where('status', true)
            ->whereIn('name', $allowed)
            ->orderBy('name');

        return $query;
    }
}
