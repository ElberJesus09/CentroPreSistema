<?php

use App\Models\Role;
use App\Models\Staff;

function createRole(string $name): Role
{
    return Role::query()->create([
        'name' => $name,
        'status' => true,
    ]);
}

function createStaffWithRole(Role $role, string $username): Staff
{
    return Staff::factory()->create([
        'role_id' => $role->id,
        'username' => $username,
    ]);
}

test('admin only sees trabajador role when creating staff', function () {
    createRole(Role::NAME_SUPER_ADMIN);
    $adminRole = createRole(Role::NAME_ADMIN);
    createRole(Role::NAME_TRABAJADOR);
    $admin = createStaffWithRole($adminRole, 'admin-role-test');

    $response = $this->actingAs($admin)->get(route('staff.create'));

    $response->assertOk()
        ->assertSee('Trabajador')
        ->assertDontSee('Administrador')
        ->assertDontSee('Super administrador');
});

test('super admin sees admin and trabajador roles when creating staff', function () {
    $superAdminRole = createRole(Role::NAME_SUPER_ADMIN);
    createRole(Role::NAME_ADMIN);
    createRole(Role::NAME_TRABAJADOR);
    $superAdmin = createStaffWithRole($superAdminRole, 'super-admin-role-test');

    $response = $this->actingAs($superAdmin)->get(route('staff.create'));

    $response->assertOk()
        ->assertSee('Administrador')
        ->assertSee('Trabajador')
        ->assertDontSee('Super administrador');
});

test('admin cannot create another admin by posting directly', function () {
    createRole(Role::NAME_SUPER_ADMIN);
    $adminRole = createRole(Role::NAME_ADMIN);
    createRole(Role::NAME_TRABAJADOR);
    $admin = createStaffWithRole($adminRole, 'admin-post-role-test');

    $response = $this->actingAs($admin)->post(route('staff.store'), [
        'first_name' => 'Carlos',
        'last_name' => 'Perez',
        'mother_last_name' => 'Rios',
        'dni' => '12345678',
        'phone' => '987654321',
        'email' => 'carlos@example.test',
        'username' => 'carlos',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role_id' => $adminRole->id,
        'status' => true,
    ]);

    $response->assertSessionHasErrors('role_id');
});

test('admin does not see super admin in staff list', function () {
    $superAdminRole = createRole(Role::NAME_SUPER_ADMIN);
    $adminRole = createRole(Role::NAME_ADMIN);
    createRole(Role::NAME_TRABAJADOR);
    createStaffWithRole($superAdminRole, 'hidden-superadmin-list-test');
    $admin = createStaffWithRole($adminRole, 'admin-list-test');

    $response = $this->actingAs($admin)->get(route('staff.index'));

    $response->assertOk()
        ->assertSee('admin-list-test')
        ->assertDontSee('hidden-superadmin-list-test');
});

test('super admin sees super admin in staff list', function () {
    $superAdminRole = createRole(Role::NAME_SUPER_ADMIN);
    createRole(Role::NAME_ADMIN);
    createRole(Role::NAME_TRABAJADOR);
    $superAdmin = createStaffWithRole($superAdminRole, 'visible-superadmin-list-test');

    $response = $this->actingAs($superAdmin)->get(route('staff.index'));

    $response->assertOk()
        ->assertSee('visible-superadmin-list-test');
});
