@extends('layouts.app')

@section('title', 'Editar empleado | '.config('app.name'))

@section('content')
    @php
        $canManageDirectPermissions = auth()->user()?->can('roles.update') || auth()->user()?->isSuperAdmin();
        $directPermissions = old('direct_permissions', $staffMember->permissions->pluck('name')->all());
    @endphp

    <div class="mb-6">
        <a href="{{ route('staff.index') }}" class="text-sm font-medium text-brand hover:underline">Volver al listado</a>
        <h1 class="mt-2 text-xl font-semibold text-neutral-900">Editar empleado</h1>
        <p class="text-sm text-neutral-600">{{ $staffMember->username }} - actualiza datos, rol, estado y permisos directos.</p>
    </div>

    <form
        method="post"
        action="{{ route('staff.update', $staffMember) }}"
        class="max-w-5xl space-y-6"
        data-confirm-title="Actualizar empleado"
        data-confirm-message="¿Confirmas guardar los cambios de este empleado?"
        data-confirm-button="Guardar cambios"
    >
        @csrf
        @method('PUT')

        <section class="rounded-lg border border-neutral-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold text-neutral-900">Datos del empleado</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-input label="Nombres" name="first_name" :value="old('first_name', $staffMember->first_name)" />
                <x-input label="Apellido paterno" name="last_name" :value="old('last_name', $staffMember->last_name)" />
                <x-input label="Apellido materno" name="mother_last_name" :value="old('mother_last_name', $staffMember->mother_last_name)" />
                <x-input label="DNI (8 dígitos)" name="dni" :value="old('dni', $staffMember->dni)" />
                <x-input label="Celular (9 dígitos)" name="phone" :value="old('phone', $staffMember->phone)" />
                <x-input label="Correo" name="email" type="email" :value="old('email', $staffMember->email)" />
                <x-input label="Usuario" name="username" :value="old('username', $staffMember->username)" autocomplete="username" />
                <x-input
                    label="Nueva contraseña (opcional)"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                />
                <x-input label="Confirmar contraseña" name="password_confirmation" type="password" autocomplete="new-password" />
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="space-y-1">
                    <label for="role_id" class="block text-sm font-medium text-neutral-800">Rol</label>
                    <select
                        id="role_id"
                        name="role_id"
                        class="block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"
                    >
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected((string) old('role_id', $staffMember->role_id) === (string) $role->id)>
                                {{ $role->displayName() }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-1">
                    <label for="status" class="block text-sm font-medium text-neutral-800">Estado</label>
                    <select
                        id="status"
                        name="status"
                        class="block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"
                    >
                        <option value="1" @selected((string) old('status', $staffMember->status ? '1' : '0') === '1')>Activo</option>
                        <option value="0" @selected((string) old('status', $staffMember->status ? '1' : '0') === '0')>Inactivo</option>
                    </select>
                    @error('status')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        @if ($canManageDirectPermissions)
            <section class="rounded-lg border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-on-surface">Permisos directos</h2>
                    <p class="text-sm text-on-surface-variant">
                        Usa esta sección solo para excepciones. Estos permisos se suman al rol principal del empleado.
                    </p>
                </div>

                @error('direct_permissions')
                    <p class="mb-3 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="grid gap-4 lg:grid-cols-2">
                    @foreach ($permissionGroups as $group)
                        <section class="rounded-lg border border-outline-variant bg-surface p-4">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <h3 class="text-sm font-semibold text-on-surface">{{ $group['label'] }}</h3>
                                <span class="text-xs text-on-surface-variant">{{ count($group['permissions']) }} permiso(s)</span>
                            </div>
                            <div class="space-y-2">
                                @foreach ($group['permissions'] as $permissionName => $label)
                                    <label class="flex items-start gap-3 rounded-md px-2 py-1.5 text-sm text-on-surface hover:bg-surface-container-high">
                                        <input
                                            type="checkbox"
                                            name="direct_permissions[]"
                                            value="{{ $permissionName }}"
                                            @checked(in_array($permissionName, $directPermissions, true))
                                            class="mt-0.5 h-4 w-4 rounded border-outline text-primary focus:ring-primary"
                                        >
                                        <span class="font-medium">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="flex gap-3">
            <x-button type="submit" variant="primary">Actualizar</x-button>
            <a
                href="{{ route('staff.index') }}"
                class="inline-flex items-center justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-800 hover:bg-neutral-50"
            >
                Cancelar
            </a>
        </div>
    </form>
@endsection
