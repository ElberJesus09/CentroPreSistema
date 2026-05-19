<?php

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\Staff;

function createAuditRole(string $name): Role
{
    return Role::query()->create([
        'name' => $name,
        'status' => true,
    ]);
}

function createAuditStaff(Role $role, string $username): Staff
{
    return Staff::factory()->create([
        'role_id' => $role->id,
        'username' => $username,
    ]);
}

test('activity report is visible only for super admin in reports page', function () {
    $superAdminRole = createAuditRole(Role::NAME_SUPER_ADMIN);
    $adminRole = createAuditRole(Role::NAME_ADMIN);
    createAuditRole(Role::NAME_TRABAJADOR);
    $admin = createAuditStaff($adminRole, 'audit-admin-test');
    $superAdmin = createAuditStaff($superAdminRole, 'audit-superadmin-test');

    ActivityLog::query()->create([
        'staff_id' => $admin->id,
        'module' => 'staff',
        'action' => 'created',
        'description' => 'Creo empleado de prueba.',
    ]);

    $this->actingAs($admin)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertDontSee('Auditoria de usuarios')
        ->assertDontSee('Creo empleado de prueba.');

    $this->actingAs($superAdmin)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertSee('Auditoria de usuarios')
        ->assertSee('Creo empleado de prueba.');
});

test('admin cannot download activity report pdf directly', function () {
    createAuditRole(Role::NAME_SUPER_ADMIN);
    $adminRole = createAuditRole(Role::NAME_ADMIN);
    createAuditRole(Role::NAME_TRABAJADOR);
    $admin = createAuditStaff($adminRole, 'audit-admin-pdf-test');

    $this->actingAs($admin)
        ->get(route('reports.activity.pdf'))
        ->assertForbidden();
});

test('activity report shows specific updated fields', function () {
    $superAdminRole = createAuditRole(Role::NAME_SUPER_ADMIN);
    $adminRole = createAuditRole(Role::NAME_ADMIN);
    createAuditRole(Role::NAME_TRABAJADOR);
    $superAdmin = createAuditStaff($superAdminRole, 'audit-detail-superadmin-test');
    $admin = createAuditStaff($adminRole, 'audit-detail-admin-test');

    $this->actingAs($superAdmin);
    $admin->update(['phone' => '912345678']);

    $this->get(route('reports.index'))
        ->assertOk()
        ->assertSee('Celular: ')
        ->assertSee('912345678');
});
