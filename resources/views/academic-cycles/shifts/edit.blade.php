@extends('layouts.app')

@section('title', 'Editar turno | '.config('app.name'))

@section('content')
    <x-academic.nav section="shifts" />

    <div class="mb-6">
        <a href="{{ route('academic-cycles.shifts.index') }}" class="text-sm font-medium text-brand hover:underline">Volver al listado</a>
        <h1 class="mt-2 text-xl font-semibold text-neutral-900">Editar turno</h1>
    </div>

    <form
        method="post"
        action="{{ route('academic-cycles.shifts.update', $shift) }}"
        class="max-w-3xl space-y-6 rounded-lg border border-neutral-200 bg-white p-6 shadow-sm"
    >
        @csrf
        @method('PUT')
        <div class="grid gap-4 sm:grid-cols-2">
            <x-input label="Nombre" name="name" :value="old('name', $shift->name)" />
            <div class="space-y-1">
                <label for="status" class="block text-sm font-medium text-neutral-800">Estado</label>
                <select
                    id="status"
                    name="status"
                    class="block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"
                >
                    <option value="1" @selected((string) old('status', $shift->status ? '1' : '0') === '1')>Activo</option>
                    <option value="0" @selected((string) old('status', $shift->status ? '1' : '0') === '0')>Inactivo</option>
                </select>
                @error('status')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="flex gap-3">
            <x-button type="submit" variant="primary">Actualizar</x-button>
            <a
                href="{{ route('academic-cycles.shifts.index') }}"
                class="inline-flex items-center justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-800 hover:bg-neutral-50"
            >
                Cancelar
            </a>
        </div>
    </form>
@endsection
