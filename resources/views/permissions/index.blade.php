@extends('layouts.app')

@section('title', 'Roles y permisos | '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="font-display text-xl font-bold text-primary md:text-2xl">Roles y permisos</h1>
            <p class="text-sm text-on-surface-variant">Elige un rol o un empleado y edita sus accesos desde una sola matriz.</p>
        </div>
        <a
            href="{{ route('staff.index') }}"
            class="inline-flex items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2 text-sm font-semibold text-on-surface transition-colors hover:bg-surface-container-high focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
        >
            Ver empleados
        </a>
    </div>

    <section class="rounded-lg border border-outline-variant bg-surface-container-lowest shadow-sm">
        <div class="border-b border-outline-variant px-5 py-4">
            <h2 class="text-lg font-semibold text-on-surface">Editor general de permisos</h2>
            <p class="mt-1 text-sm text-on-surface-variant">
                Rol: cambia permisos para todo el grupo. Empleado: muestra permisos heredados por rol y permite permisos temporales extra.
            </p>
        </div>

        <div class="border-b border-outline-variant px-5 py-4">
            <div class="inline-flex rounded-lg border border-outline-variant bg-surface p-1">
                <a
                    href="{{ route('permissions.index', ['mode' => 'role', 'role_id' => $selectedRole?->id]) }}"
                    @class([
                        'rounded-md px-4 py-2 text-sm font-semibold transition-colors',
                        'bg-primary text-on-primary' => $mode === 'role',
                        'text-on-surface hover:bg-surface-container-high' => $mode !== 'role',
                    ])
                >
                    Permisos por rol
                </a>
                <a
                    href="{{ route('permissions.index', ['mode' => 'staff']) }}"
                    @class([
                        'rounded-md px-4 py-2 text-sm font-semibold transition-colors',
                        'bg-primary text-on-primary' => $mode === 'staff',
                        'text-on-surface hover:bg-surface-container-high' => $mode !== 'staff',
                    ])
                >
                    Permisos por empleado
                </a>
            </div>
        </div>

        @if ($mode === 'role')
            @php
                $rolePermissions = $selectedRole?->permissions->pluck('name')->all() ?? [];
                $isSuperAdmin = $selectedRole?->name === \App\Models\Role::NAME_SUPER_ADMIN;
            @endphp

            <form method="get" action="{{ route('permissions.index') }}" class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto]">
                <input type="hidden" name="mode" value="role">
                <div>
                    <label for="role_id" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Rol a editar</label>
                    <select
                        id="role_id"
                        name="role_id"
                        class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected($selectedRole?->id === $role->id)>
                                {{ $role->displayName() }} - {{ $role->staff_count }} empleado(s)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <x-button type="submit" variant="primary">Cargar permisos</x-button>
                </div>
            </form>

            @if ($selectedRole)
                <form
                    method="post"
                    action="{{ route('permissions.update', $selectedRole) }}"
                    class="border-t border-outline-variant p-5"
                    data-confirm-title="Actualizar permisos"
                    data-confirm-message="¿Confirmas guardar los permisos de {{ $selectedRole->displayName() }}?"
                    data-confirm-button="Guardar permisos"
                >
                    @csrf
                    @method('PUT')

                    <div class="mb-4 rounded-lg bg-surface-container-high px-4 py-3 text-sm text-on-surface">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold">{{ $selectedRole->displayName() }}</p>
                                <p class="text-on-surface-variant">Los permisos marcados aplican a todos los empleados con este rol.</p>
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-on-surface">
                                <input type="hidden" name="status" value="0">
                                <input
                                    type="checkbox"
                                    name="status"
                                    value="1"
                                    @checked($selectedRole->status)
                                    @disabled($isSuperAdmin)
                                    class="h-4 w-4 rounded border-outline text-primary focus:ring-primary"
                                >
                                Rol activo
                            </label>
                        </div>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        @foreach ($permissionGroups as $group)
                            <section class="rounded-lg border border-outline-variant bg-surface p-4">
                                <h3 class="mb-3 text-sm font-semibold text-on-surface">{{ $group['label'] }}</h3>
                                <div class="space-y-2">
                                    @foreach ($group['permissions'] as $permissionName => $label)
                                        <label class="flex items-start gap-3 rounded-md px-2 py-1.5 text-sm text-on-surface hover:bg-surface-container-high">
                                            <input
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permissionName }}"
                                                @checked($isSuperAdmin || in_array($permissionName, $rolePermissions, true))
                                                @disabled($isSuperAdmin)
                                                class="mt-0.5 h-4 w-4 rounded border-outline text-primary focus:ring-primary"
                                            >
                                            <span class="font-medium">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>

                    <div class="mt-4 flex justify-end">
                        <x-button type="submit" variant="primary">Guardar permisos del rol</x-button>
                    </div>
                </form>
            @endif
        @else
            <form method="get" action="{{ route('permissions.index') }}" class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_1fr_auto]">
                <input type="hidden" name="mode" value="staff">
                <div>
                    <label for="staff_search" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Buscar empleado</label>
                    <input
                        id="staff_search"
                        name="staff_search"
                        value="{{ $staffSearch }}"
                        placeholder="DNI, nombre o usuario"
                        class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                </div>
                <div>
                    <label for="staff_id" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Empleado</label>
                    <select
                        id="staff_id"
                        name="staff_id"
                        class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        <option value="">Selecciona un empleado</option>
                        @foreach ($staffOptions as $staff)
                            <option value="{{ $staff->id }}" @selected($selectedStaff?->id === $staff->id)>
                                {{ trim($staff->first_name.' '.$staff->last_name.' '.$staff->mother_last_name) }} - {{ $staff->username }} - {{ $staff->role?->displayName() ?? 'Sin rol' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <x-button type="submit" variant="primary">Cargar permisos</x-button>
                    <a
                        href="{{ route('permissions.index', ['mode' => 'staff']) }}"
                        class="inline-flex items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface transition-colors hover:bg-surface-container-high focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        Limpiar
                    </a>
                </div>
            </form>

            @if (! $selectedStaff)
                <div class="border-t border-outline-variant px-5 py-6 text-sm text-on-surface-variant">
                    Busca y selecciona un empleado para ver todos sus permisos.
                </div>
            @else
                @php
                    $rolePermissionNames = $selectedStaff->role?->permissions->pluck('name')->all() ?? [];
                    $temporaryGrants = $selectedStaff->temporaryPermissionGrants
                        ->filter(fn ($grant) => $grant->expires_at->isFuture())
                        ->keyBy('permission.name');
                    $temporaryPermissionNames = $temporaryGrants->keys()->all();
                    $defaultExpiration = optional($temporaryGrants->first()?->expires_at)->format('Y-m-d\TH:i')
                        ?? now()->addDays(7)->format('Y-m-d\TH:i');
                @endphp

                <form
                    method="post"
                    action="{{ route('permissions.staff.update', $selectedStaff) }}"
                    class="border-t border-outline-variant p-5"
                    data-confirm-title="Actualizar permisos temporales"
                    data-confirm-message="¿Confirmas guardar los permisos temporales de {{ $selectedStaff->username }}?"
                    data-confirm-button="Guardar permisos"
                >
                    @csrf
                    @method('PUT')

                    <div class="mb-4 rounded-lg bg-surface-container-high px-4 py-3 text-sm text-on-surface">
                        <p class="font-semibold">{{ trim($selectedStaff->first_name.' '.$selectedStaff->last_name.' '.$selectedStaff->mother_last_name) }}</p>
                        <p class="text-on-surface-variant">
                            Usuario: {{ $selectedStaff->username }} | Rol actual: {{ $selectedStaff->role?->displayName() ?? 'Sin rol' }}
                        </p>
                        <p class="mt-2 text-xs text-on-surface-variant">
                            Los permisos con etiqueta “Por rol” ya los tiene por su rol y no se editan aquí. Marca permisos extra y define hasta cuándo estarán activos.
                        </p>
                    </div>

                    <div class="mb-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="duration_minutes" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Duración rápida</label>
                            <select
                                id="duration_minutes"
                                name="duration_minutes"
                                class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                <option value="">Usar fecha y hora</option>
                                <option value="5">5 minutos</option>
                                <option value="10">10 minutos</option>
                                <option value="15">15 minutos</option>
                                <option value="30">30 minutos</option>
                                <option value="60">1 hora</option>
                                <option value="120">2 horas</option>
                                <option value="1440">1 día</option>
                                <option value="10080">7 días</option>
                            </select>
                            @error('duration_minutes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                        <label for="expires_at" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Vigente hasta</label>
                        <input
                            id="expires_at"
                            name="expires_at"
                            type="datetime-local"
                            value="{{ old('expires_at', $defaultExpiration) }}"
                            class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                        @error('expires_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        @foreach ($permissionGroups as $group)
                            <section class="rounded-lg border border-outline-variant bg-surface p-4">
                                <h3 class="mb-3 text-sm font-semibold text-on-surface">{{ $group['label'] }}</h3>
                                <div class="space-y-2">
                                    @foreach ($group['permissions'] as $permissionName => $label)
                                        @php
                                            $hasByRole = in_array($permissionName, $rolePermissionNames, true);
                                            $hasTemporary = in_array($permissionName, $temporaryPermissionNames, true);
                                        @endphp
                                        <label class="flex items-start justify-between gap-3 rounded-md px-2 py-1.5 text-sm text-on-surface hover:bg-surface-container-high">
                                            <span class="flex items-start gap-3">
                                                <input
                                                    type="checkbox"
                                                    name="direct_permissions[]"
                                                    value="{{ $permissionName }}"
                                                    @checked($hasByRole || $hasTemporary)
                                                    @disabled($hasByRole)
                                                    class="mt-0.5 h-4 w-4 rounded border-outline text-primary focus:ring-primary"
                                                >
                                                <span class="font-medium">{{ $label }}</span>
                                            </span>
                                            @if ($hasByRole)
                                                <span class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary">Por rol</span>
                                            @elseif ($hasTemporary)
                                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Temporal</span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>

                    <div class="mt-4 flex justify-end">
                        <x-button type="submit" variant="primary">Guardar permisos temporales</x-button>
                    </div>
                </form>
            @endif
        @endif
    </section>
@endsection
