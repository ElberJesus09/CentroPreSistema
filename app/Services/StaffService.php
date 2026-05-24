<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Staff;
use App\Services\ActivityLogService;
use App\Support\Permissions\FormatsPermissionChanges;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StaffService
{
    use FormatsPermissionChanges;

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
        /** @var Staff $staff */
        $staff = Staff::query()->create($attributes);
        $this->syncSpatieRole($staff);

        return $staff;
    }

    /** Actualizacion; omite password si viene vacio. */
    public function update(Staff $staff, array $attributes, ?array $directPermissions = null): Staff
    {
        return DB::transaction(function () use ($staff, $attributes, $directPermissions): Staff {
            $staff->loadMissing('role', 'permissions');
            $beforeRole = $staff->role?->displayName() ?? 'Sin rol';
            $beforeDirectPermissions = $staff->permissions->pluck('name')->sort()->values()->all();

            if (empty($attributes['password'])) {
                unset($attributes['password']);
            }

            $staff->update($attributes);
            $this->syncSpatieRole($staff);

            if ($directPermissions !== null) {
                $staff->syncPermissions($directPermissions);
            }

            $updated = $staff->fresh()->load('role', 'permissions');
            $this->recordAccessChanges($updated, $beforeRole, $beforeDirectPermissions, $directPermissions);

            return $updated;
        });
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

    private function syncSpatieRole(Staff $staff): void
    {
        $staff->loadMissing('role');

        if ($staff->role !== null) {
            $staff->syncRoles([$staff->role]);
        }
    }

    /**
     * @param  list<string>  $beforeDirectPermissions
     * @param  list<string>|null  $requestedDirectPermissions
     */
    private function recordAccessChanges(
        Staff $staff,
        string $beforeRole,
        array $beforeDirectPermissions,
        ?array $requestedDirectPermissions,
    ): void {
        $changed = [];
        $afterRole = $staff->role?->displayName() ?? 'Sin rol';

        if ($beforeRole !== $afterRole) {
            $changed['role_name'] = [
                'before' => $beforeRole,
                'after' => $afterRole,
            ];
        }

        if ($requestedDirectPermissions !== null) {
            $afterDirectPermissions = $staff->permissions->pluck('name')->sort()->values()->all();

            if ($beforeDirectPermissions !== $afterDirectPermissions) {
                $changed['direct_permissions'] = [
                    'before' => null,
                    'after' => $this->permissionDiff($beforeDirectPermissions, $afterDirectPermissions),
                ];
            }
        }

        if ($changed === []) {
            return;
        }

        app(ActivityLogService::class)->record(
            'roles_permissions',
            'updated',
            'Actualizó accesos del empleado: '.(trim($staff->first_name.' '.$staff->last_name) ?: $staff->username),
            $staff,
            ['changed' => $changed],
        );
    }

}
