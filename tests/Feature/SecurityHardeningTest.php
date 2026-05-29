<?php

use App\Models\Role;
use App\Models\Staff;
use Spatie\Permission\Models\Permission;

function securityRole(string $name): Role
{
    return Role::query()->firstOrCreate(['name' => $name], ['status' => true]);
}

function securityPermission(string $name): Permission
{
    return Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
}

test('respuestas web incluyen headers de seguridad', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
});

test('docente with academic module access cannot run distribution', function () {
    $docente = Staff::factory()->create([
        'role_id' => securityRole(Role::NAME_DOCENTE)->id,
    ]);

    $this->actingAs($docente)
        ->post(route('academic.distribution.run'), [
            'academic_cycle_id' => 1,
        ])
        ->assertForbidden();
});

test('report viewer cannot export administrative reports without export permission', function () {
    $staff = Staff::factory()->create([
        'role_id' => securityRole('report_viewer')->id,
    ]);
    $staff->givePermissionTo(securityPermission('reports.view'));

    $this->actingAs($staff)
        ->get(route('reports.students.emails'))
        ->assertForbidden();
});

test('student viewer cannot download student cards without documents permission', function () {
    $staff = Staff::factory()->create([
        'role_id' => securityRole('student_viewer')->id,
    ]);
    $staff->givePermissionTo(securityPermission('students.view'));

    $this->actingAs($staff)
        ->get(route('students.cards.download'))
        ->assertForbidden();
});

test('public dni lookup does not expose previous registration profile data', function () {
    $this->getJson(route('registration.dni-lookup', ['dni' => '12345678']))
        ->assertOk()
        ->assertJson([
            'found' => false,
            'profile' => null,
        ]);
});

test('authenticated user without panel permissions cannot view dashboard', function () {
    $staff = Staff::factory()->create([
        'role_id' => securityRole('sin_acceso')->id,
    ]);

    $this->actingAs($staff)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('academic management role can log in to admin panel', function () {
    $docente = Staff::factory()->create([
        'role_id' => securityRole(Role::NAME_DOCENTE)->id,
        'username' => 'docente-login',
        'password' => 'password',
    ]);

    $this->post(route('login'), [
        'username' => $docente->username,
        'password' => 'password',
    ])
        ->assertRedirect(route('dashboard'));
});

test('admin cannot update super admin role permissions', function () {
    $superRole = securityRole(Role::NAME_SUPER_ADMIN);
    $admin = Staff::factory()->create([
        'role_id' => securityRole(Role::NAME_ADMIN)->id,
    ]);
    $admin->givePermissionTo(securityPermission('roles.update'));

    $this->actingAs($admin)
        ->put(route('permissions.update', $superRole), [
            'status' => true,
            'permissions' => [],
        ])
        ->assertForbidden();
});

test('admin cannot grant temporary permissions to super admin account', function () {
    $superAdmin = Staff::factory()->create([
        'role_id' => securityRole(Role::NAME_SUPER_ADMIN)->id,
    ]);
    $admin = Staff::factory()->create([
        'role_id' => securityRole(Role::NAME_ADMIN)->id,
    ]);
    $admin->givePermissionTo(securityPermission('roles.update'));

    $this->actingAs($admin)
        ->put(route('permissions.staff.update', $superAdmin), [
            'direct_permissions' => ['students.delete'],
            'expires_at' => now()->addHour()->toDateTimeString(),
        ])
        ->assertForbidden();
});

test('login administrativo bloquea intentos repetidos por ip y usuario', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login'), [
            'username' => 'admin',
            'password' => 'clave-incorrecta',
        ])->assertSessionHasErrors('username');
    }

    $this->post(route('login'), [
        'username' => 'admin',
        'password' => 'clave-incorrecta',
    ])->assertStatus(429);
});
