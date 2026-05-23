@extends('layouts.app')

@section('title', 'Ciclos académicos | '.config('app.name'))

@section('content')
    <x-academic.nav section="schedules" />

    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="font-display text-xl font-bold text-primary md:text-2xl">Ciclos académicos</h1>
            <p class="text-sm text-on-surface-variant">Programación de turnos por ciclo y sede (capacidad y matriculados).</p>
        </div>
        @can('create', \App\Models\AcademicCycleShift::class)
            <a
                href="{{ route('academic-cycles.schedules.create') }}"
                class="inline-flex items-center justify-center rounded-lg border border-transparent bg-primary px-4 py-2 text-sm font-semibold text-on-primary shadow-sm transition-colors hover:bg-primary-container focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Nueva programación
            </a>
        @endcan
    </div>

    <x-table.shell>
        @if ($schedules->hasPages())
            <x-slot:footer>
                {{ $schedules->links() }}
            </x-slot:footer>
        @endif
        <thead class="bg-surface-container-high text-xs font-bold uppercase tracking-wide text-on-surface-variant">
            <tr>
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">Ciclo</th>
                <th class="px-4 py-3">Sede</th>
                <th class="px-4 py-3">Turno</th>
                <th class="px-4 py-3">Capacidad</th>
                <th class="px-4 py-3">Matriculados</th>
                <th class="px-4 py-3">Estado</th>
                <th class="sticky right-0 bg-surface-container-high px-4 py-3 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/50 text-on-surface">
            @forelse ($schedules as $row)
                <tr class="hover:bg-surface-container-low/80">
                    <td class="whitespace-nowrap px-4 py-3 text-on-surface-variant">{{ $row->id }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $row->academicCycle?->name }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $row->campus?->name }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $row->shift?->name }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $row->capacity }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $row->enrolled }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        @if ($row->status)
                            <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-800">Activo</span>
                        @else
                            <span class="rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-700">Inactivo</span>
                        @endif
                    </td>
                    <td
                        class="sticky right-0 whitespace-nowrap bg-surface-container-lowest px-4 py-3 text-right shadow-[-6px_0_8px_-6px_rgba(0,0,0,0.08)]"
                    >
                        @can('update', $row)
                            <a
                                href="{{ route('academic-cycles.schedules.edit', $row) }}"
                                class="mr-2 text-sm font-semibold text-primary hover:underline"
                            >
                                Editar
                            </a>
                        @endcan
                        @can('delete', $row)
                            <button
                                type="button"
                                class="text-sm font-semibold text-error hover:underline"
                                onclick="document.getElementById('schedule-delete-{{ $row->id }}').showModal()"
                            >
                                Eliminar
                            </button>
                            <x-modal
                                id="schedule-delete-{{ $row->id }}"
                                title="Eliminar programacion"
                                description="Esta acción no se puede deshacer."
                                variant="danger"
                            >
                                <p>Eliminaras la programacion de <strong>{{ $row->academicCycle?->name }}</strong>, sede <strong>{{ $row->campus?->name }}</strong>, turno <strong>{{ $row->shift?->name }}</strong>.</p>
                                <p class="mt-2 text-on-surface-variant">No podra eliminarse si tiene alumnos matriculados.</p>

                                <x-slot:actions>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface transition-colors hover:bg-surface-container-high focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                        onclick="this.closest('dialog').close()"
                                    >
                                        Cancelar
                                    </button>
                                    <form method="post" action="{{ route('academic-cycles.schedules.destroy', $row) }}" class="contents">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" variant="danger">Confirmar eliminacion</x-button>
                                    </form>
                                </x-slot:actions>
                            </x-modal>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-sm text-on-surface-variant">
                        No hay programaciones. Cree ciclos, sedes y turnos, luego registre la programación.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table.shell>
@endsection
