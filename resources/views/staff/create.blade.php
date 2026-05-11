@extends('layouts.app')

@section('title', 'Nuevo empleado | '.config('app.name'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('staff.index') }}" class="text-sm font-medium text-brand hover:underline">Volver al listado</a>
        <h1 class="mt-2 text-xl font-semibold text-neutral-900">Nuevo empleado</h1>
        <p class="text-sm text-neutral-600">Complete los datos y asigne rol y estado.</p>
    </div>

    <form method="post" action="{{ route('staff.store') }}" class="max-w-3xl space-y-6 rounded-lg border border-neutral-200 bg-white p-6 shadow-sm">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <x-input label="Nombres" name="first_name" :value="old('first_name')" />
            <x-input label="Apellido paterno" name="last_name" :value="old('last_name')" />
            <x-input label="Apellido materno" name="mother_last_name" :value="old('mother_last_name')" />
            <x-input label="DNI (8 digitos)" name="dni" :value="old('dni')" />
            <x-input label="Celular (9 digitos)" name="phone" :value="old('phone')" />
            <x-input label="Correo" name="email" type="email" :value="old('email')" />
            <x-input label="Usuario" name="username" :value="old('username')" autocomplete="username" />
            <x-input label="Contrasena" name="password" type="password" autocomplete="new-password" />
            <x-input label="Confirmar contrasena" name="password_confirmation" type="password" autocomplete="new-password" />
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1">
                <label for="role_id" class="block text-sm font-medium text-neutral-800">Rol</label>
                <select
                    id="role_id"
                    name="role_id"
                    class="block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"
                >
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>
                            {{ $role->name }}
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
                    <option value="1" @selected(old('status', '1') == '1')>Activo</option>
                    <option value="0" @selected(old('status') === '0')>Inactivo</option>
                </select>
                @error('status')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="flex gap-3">
            <x-button type="submit" variant="primary">Guardar</x-button>
            <a
                href="{{ route('staff.index') }}"
                class="inline-flex items-center justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-800 hover:bg-neutral-50"
            >
                Cancelar
            </a>
        </div>
    </form>
@endsection
