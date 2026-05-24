<?php

namespace App\Support\Permissions;

use App\Models\Role;

class PermissionCatalog
{
    /**
     * @return array<string, array{label: string, permissions: array<string, string>}>
     */
    public static function groups(): array
    {
        return [
            'panel' => [
                'label' => 'Panel principal',
                'permissions' => [
                    'dashboard.view' => 'Ver panel principal',
                ],
            ],
            'staff' => [
                'label' => 'Personal',
                'permissions' => [
                    'staff.view' => 'Ver empleados',
                    'staff.create' => 'Crear empleados',
                    'staff.update' => 'Editar empleados',
                    'staff.delete' => 'Eliminar empleados',
                ],
            ],
            'roles' => [
                'label' => 'Roles y permisos',
                'permissions' => [
                    'roles.view' => 'Ver roles y permisos',
                    'roles.update' => 'Editar permisos de roles',
                ],
            ],
            'academic_cycles' => [
                'label' => 'Ciclos académicos',
                'permissions' => [
                    'academic-cycles.view' => 'Ver ciclos, sedes y turnos',
                    'academic-cycles.manage' => 'Gestionar ciclos, sedes y turnos',
                ],
            ],
            'students' => [
                'label' => 'Alumnos',
                'permissions' => [
                    'students.view' => 'Ver alumnos',
                    'students.create' => 'Registrar alumnos',
                    'students.update' => 'Editar alumnos',
                    'students.delete' => 'Eliminar alumnos',
                    'students.documents' => 'Descargar documentos de alumnos',
                ],
            ],
            'academic' => [
                'label' => 'Gestión académica',
                'permissions' => [
                    'academic.view' => 'Ver modulo academico',
                    'academic.classrooms.manage' => 'Gestionar aulas',
                    'academic.distribution.manage' => 'Distribuir alumnos',
                    'academic.grades.manage' => 'Gestionar notas y evaluaciones',
                    'academic.imports.manage' => 'Importar notas academicas',
                    'academic.reports.view' => 'Ver reportes academicos',
                    'academic.reports.export' => 'Exportar reportes academicos',
                ],
            ],
            'reports' => [
                'label' => 'Reportes administrativos',
                'permissions' => [
                    'reports.view' => 'Ver reportes administrativos',
                    'reports.export' => 'Exportar reportes administrativos',
                ],
            ],
            'settings' => [
                'label' => 'Configuración',
                'permissions' => [
                    'exam-settings.update' => 'Editar mensaje de correo',
                ],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return collect(self::groups())
            ->flatMap(fn (array $group) => array_keys($group['permissions']))
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function defaultsForRole(string $roleName): array
    {
        return match ($roleName) {
            Role::NAME_SUPER_ADMIN, Role::NAME_ADMIN => self::names(),
            Role::NAME_TRABAJADOR => [
                'dashboard.view',
                'students.view',
                'students.create',
                'students.update',
                'students.documents',
                'academic.view',
                'academic.imports.manage',
            ],
            Role::NAME_DOCENTE => [
                'dashboard.view',
                'academic.view',
                'academic.grades.manage',
                'academic.reports.view',
            ],
            Role::NAME_ASISTENTE => [
                'dashboard.view',
                'students.view',
                'students.create',
                'students.update',
                'academic.view',
                'academic.imports.manage',
            ],
            default => ['dashboard.view'],
        };
    }
}
