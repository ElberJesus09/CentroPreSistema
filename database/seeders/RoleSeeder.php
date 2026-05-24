<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Support\Permissions\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionCatalog::names() as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        foreach (
            [
                Role::NAME_SUPER_ADMIN,
                Role::NAME_ADMIN,
                Role::NAME_TRABAJADOR,
                Role::NAME_DOCENTE,
                Role::NAME_ASISTENTE,
            ] as $name
        ) {
            /** @var Role $role */
            $role = Role::query()->firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web', 'status' => true]
            );
            $role->forceFill(['guard_name' => 'web', 'status' => true])->save();
            $role->syncPermissions(PermissionCatalog::defaultsForRole($name));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
