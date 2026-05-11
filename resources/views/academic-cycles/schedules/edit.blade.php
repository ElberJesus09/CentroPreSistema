@extends('layouts.app')

@section('title', 'Editar programacion | '.config('app.name'))

@section('content')
    <x-academic.nav section="schedules" />

    <div class="mb-6">
        <a href="{{ route('academic-cycles.index') }}" class="text-sm font-medium text-brand hover:underline">Volver al listado</a>
        <h1 class="mt-2 text-xl font-semibold text-neutral-900">Editar programacion</h1>
        <p class="text-sm text-neutral-600">ID {{ $schedule->id }} — ajuste capacidad o matriculados sin superar el limite.</p>
    </div>

    <form
        method="post"
        action="{{ route('academic-cycles.schedules.update', $schedule) }}"
        class="max-w-3xl space-y-6 rounded-lg border border-neutral-200 bg-white p-6 shadow-sm"
    >
        @csrf
        @method('PUT')
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1 sm:col-span-2">
                <label for="academic_cycle_id" class="block text-sm font-medium text-neutral-800">Ciclo academico</label>
                <select
                    id="academic_cycle_id"
                    name="academic_cycle_id"
                    class="block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"
                >
                    @foreach ($cycles as $c)
                        <option value="{{ $c->id }}" @selected((string) old('academic_cycle_id', $schedule->academic_cycle_id) === (string) $c->id)>
                            {{ $c->name }}{{ $c->status ? '' : ' (inactivo)' }}
                        </option>
                    @endforeach
                </select>
                @error('academic_cycle_id')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-1">
                <label for="campus_id" class="block text-sm font-medium text-neutral-800">Sede</label>
                <select
                    id="campus_id"
                    name="campus_id"
                    class="block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"
                >
                    @foreach ($campuses as $campus)
                        <option value="{{ $campus->id }}" @selected((string) old('campus_id', $schedule->campus_id) === (string) $campus->id)>
                            {{ $campus->name }}{{ $campus->status ? '' : ' (inactivo)' }}
                        </option>
                    @endforeach
                </select>
                @error('campus_id')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-1">
                <label for="shift_id" class="block text-sm font-medium text-neutral-800">Turno</label>
                <select
                    id="shift_id"
                    name="shift_id"
                    class="block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"
                >
                    @foreach ($shifts as $shift)
                        <option value="{{ $shift->id }}" @selected((string) old('shift_id', $schedule->shift_id) === (string) $shift->id)>
                            {{ $shift->name }}{{ $shift->status ? '' : ' (inactivo)' }}
                        </option>
                    @endforeach
                </select>
                @error('shift_id')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <x-input label="Capacidad maxima" name="capacity" type="number" :value="old('capacity', $schedule->capacity)" />
            <x-input label="Matriculados actuales" name="enrolled" type="number" :value="old('enrolled', $schedule->enrolled)" />
            <div class="space-y-1 sm:col-span-2">
                <label for="status" class="block text-sm font-medium text-neutral-800">Estado</label>
                <select
                    id="status"
                    name="status"
                    class="block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand sm:max-w-xs"
                >
                    <option value="1" @selected((string) old('status', $schedule->status ? '1' : '0') === '1')>Activo</option>
                    <option value="0" @selected((string) old('status', $schedule->status ? '1' : '0') === '0')>Inactivo</option>
                </select>
                @error('status')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="flex gap-3">
            <x-button type="submit" variant="primary">Actualizar</x-button>
            <a
                href="{{ route('academic-cycles.index') }}"
                class="inline-flex items-center justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-800 hover:bg-neutral-50"
            >
                Cancelar
            </a>
        </div>
    </form>
@endsection
