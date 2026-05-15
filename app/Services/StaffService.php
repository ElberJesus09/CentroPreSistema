<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Staff;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StaffService
{
    /** Listado paginado para administracion. */
    public function paginateIndex(int $perPage = 15): LengthAwarePaginator
    {
        return Staff::query()
            ->with('role')
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
    public function assignableRolesQuery(?Staff $actor = null): Builder
    {
        $query = Role::query()
            ->where('status', true)
            ->orderBy('name');

        if ($actor !== null && ! $actor->isSuperAdmin()) {
            $query->where('name', '!=', Role::NAME_SUPER_ADMIN);
        }

        return $query;
    }
}
