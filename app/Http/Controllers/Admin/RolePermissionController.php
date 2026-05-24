<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permissions\UpdateRolePermissionsRequest;
use App\Models\Role;
use App\Models\Staff;
use App\Services\ActivityLogService;
use App\Support\Permissions\FormatsPermissionChanges;
use App\Support\Permissions\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{
    use FormatsPermissionChanges;

    public function index(Request $request): View
    {
        $user = auth()->user();
        abort_unless($user?->can('roles.view') || $user?->isSuperAdmin(), 403);

        $mode = $request->query('mode') === 'staff' ? 'staff' : 'role';
        $selectedRoleId = (int) $request->query('role_id');
        $staffSearch = trim((string) $request->query('staff_search'));
        $selectedStaffId = (int) $request->query('staff_id');
        $roles = Role::query()
            ->withCount('staff')
            ->with('permissions:id,name')
            ->orderByRaw("case name when 'super_admin' then 1 when 'admin' then 2 when 'trabajador' then 3 when 'docente' then 4 when 'asistente' then 5 else 6 end")
            ->get();
        $selectedRole = $selectedRoleId > 0 ? $roles->firstWhere('id', $selectedRoleId) : $roles->first();
        $staffOptions = Staff::query()
            ->with('role')
            ->when($staffSearch !== '', function ($query) use ($staffSearch): void {
                $query->where(function ($query) use ($staffSearch): void {
                    $query->where('first_name', 'like', "%{$staffSearch}%")
                        ->orWhere('last_name', 'like', "%{$staffSearch}%")
                        ->orWhere('mother_last_name', 'like', "%{$staffSearch}%")
                        ->orWhere('username', 'like', "%{$staffSearch}%")
                        ->orWhere('dni', 'like', "%{$staffSearch}%");
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(50)
            ->get();
        $selectedStaff = $selectedStaffId > 0
            ? Staff::query()->with('role.permissions', 'temporaryPermissionGrants.permission')->find($selectedStaffId)
            : null;

        return view('permissions.index', [
            'mode' => $mode,
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'permissionGroups' => PermissionCatalog::groups(),
            'staffSearch' => $staffSearch,
            'staffOptions' => $staffOptions,
            'selectedStaff' => $selectedStaff,
        ]);
    }

    public function update(UpdateRolePermissionsRequest $request, Role $role, ActivityLogService $activityLogService): RedirectResponse
    {
        $role->load('permissions');
        $beforePermissions = $role->permissions->pluck('name')->sort()->values()->all();
        $beforeStatus = (bool) $role->status;

        DB::transaction(function () use ($request, $role): void {
            $role->forceFill([
                'status' => $role->name === Role::NAME_SUPER_ADMIN ? true : (bool) $request->validated('status'),
                'guard_name' => 'web',
            ])->save();

            $role->syncPermissions($request->permissionNames());
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role->refresh()->load('permissions');
        $afterPermissions = $role->permissions->pluck('name')->sort()->values()->all();

        $activityLogService->record(
            'roles_permissions',
            'updated',
            'Actualizó permisos del rol: '.$role->displayName(),
            $role,
            [
                'changed' => [
                    'status' => [
                        'before' => $beforeStatus,
                        'after' => (bool) $role->status,
                    ],
                    'role_permissions' => [
                        'before' => null,
                        'after' => $this->permissionDiff($beforePermissions, $afterPermissions),
                    ],
                ],
            ],
        );

        return redirect()
            ->route('permissions.index')
            ->with('success', 'Permisos del rol actualizados correctamente.');
    }

}
