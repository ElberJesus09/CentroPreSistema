<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permissions\UpdateStaffDirectPermissionsRequest;
use App\Models\Staff;
use App\Models\StaffTemporaryPermissionGrant;
use App\Services\ActivityLogService;
use App\Support\Permissions\FormatsPermissionChanges;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class StaffDirectPermissionController extends Controller
{
    use FormatsPermissionChanges;

    public function update(UpdateStaffDirectPermissionsRequest $request, Staff $staff, ActivityLogService $activityLogService): RedirectResponse
    {
        $staff->load('temporaryPermissionGrants.permission');
        $beforePermissions = $staff->temporaryPermissionGrants
            ->filter(fn (StaffTemporaryPermissionGrant $grant) => $grant->expires_at->isFuture())
            ->pluck('permission.name')
            ->sort()
            ->values()
            ->all();

        $permissionNames = $request->permissionNames();
        $expiresAt = $request->expiresAt() ? CarbonImmutable::parse($request->expiresAt()) : null;

        DB::transaction(function () use ($staff, $permissionNames, $expiresAt): void {
            $permissionIds = Permission::query()
                ->whereIn('name', $permissionNames)
                ->where('guard_name', 'web')
                ->pluck('id', 'name');

            StaffTemporaryPermissionGrant::query()
                ->where('staff_id', $staff->id)
                ->whereNotIn('permission_id', $permissionIds->values()->all())
                ->delete();

            foreach ($permissionIds as $permissionId) {
                StaffTemporaryPermissionGrant::query()->updateOrCreate(
                    [
                        'staff_id' => $staff->id,
                        'permission_id' => $permissionId,
                    ],
                    [
                        'granted_by' => auth()->id(),
                        'expires_at' => $expiresAt,
                    ],
                );
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $staff->refresh()->load('temporaryPermissionGrants.permission');
        $afterPermissions = $staff->temporaryPermissionGrants
            ->filter(fn (StaffTemporaryPermissionGrant $grant) => $grant->expires_at->isFuture())
            ->pluck('permission.name')
            ->sort()
            ->values()
            ->all();

        if ($beforePermissions !== $afterPermissions) {
            $activityLogService->record(
                'roles_permissions',
                'updated',
                'Actualizó permisos temporales del empleado: '.(trim($staff->first_name.' '.$staff->last_name) ?: $staff->username),
                $staff,
                [
                    'changed' => [
                        'temporary_permissions' => [
                            'before' => null,
                            'after' => $this->permissionDiff($beforePermissions, $afterPermissions),
                        ],
                        'expires_at' => [
                            'before' => null,
                            'after' => $expiresAt?->format('Y-m-d H:i'),
                        ],
                    ],
                ],
            );
        }

        return redirect()
            ->route('permissions.index', ['mode' => 'staff', 'staff_id' => $staff->id])
            ->with('success', 'Permisos temporales del empleado actualizados correctamente.');
    }
}
