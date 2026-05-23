@extends('layouts.app')

@section('title', 'Aulas | '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <h1 class="font-display text-2xl font-bold text-primary">Gestión de aulas</h1>
            <p class="text-sm text-on-surface-variant">Capacidad, prioridad y disponibilidad para distribución académica.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('academic.distribution.index') }}" class="rounded-lg border border-outline-variant px-4 py-2 text-sm font-semibold text-primary">Distribución</a>
            @can('create', \App\Models\Classroom::class)
                <a href="{{ route('academic.classrooms.create') }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-on-primary">Nueva aula</a>
            @endcan
        </div>
    </div>

    <form method="get" class="mb-5 grid gap-3 rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-4 md:grid-cols-[1fr_1fr_auto] md:items-end">
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-on-surface-variant">Buscar</label>
            <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre o código" class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-on-surface-variant">Ciclo</label>
            <select name="academic_cycle_id" class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm">
                <option value="">Todos los ciclos</option>
                @foreach ($cycles as $cycle)
                    <option value="{{ $cycle->id }}" @selected((int) ($filters['academic_cycle_id'] ?? 0) === (int) $cycle->id)>{{ $cycle->name }}</option>
                @endforeach
            </select>
        </div>
        <x-button type="submit">Filtrar</x-button>
    </form>

    <x-table.shell>
        @if ($classrooms->hasPages())
            <x-slot:footer>
                {{ $classrooms->links() }}
            </x-slot:footer>
        @endif
        <thead class="bg-surface-container-high text-xs font-bold uppercase text-on-surface-variant">
            <tr>
                <th class="px-4 py-3">Aula</th>
                <th class="px-4 py-3">Ciclo</th>
                <th class="px-4 py-3">Piso</th>
                <th class="px-4 py-3">Capacidad</th>
                <th class="px-4 py-3">Prioridad</th>
                <th class="px-4 py-3">Estado</th>
                <th class="px-4 py-3 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/50">
            @forelse ($classrooms as $row)
                <tr class="hover:bg-surface-container-low/80">
                    <td class="px-4 py-3">
                        <span class="font-semibold">{{ $row->name }}</span><br>
                        <span class="text-xs text-on-surface-variant">{{ $row->code }}</span>
                    </td>
                    <td class="px-4 py-3">{{ $row->academicCycle?->name }}</td>
                    <td class="px-4 py-3">{{ $row->floor }}</td>
                    <td class="px-4 py-3">{{ $row->assignments_count }} / {{ $row->capacity }}</td>
                    <td class="px-4 py-3">{{ $row->academic_priority }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $row->status ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }}">
                            {{ $row->status ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @can('update', $row)
                            <a href="{{ route('academic.classrooms.edit', $row) }}" class="mr-2 text-sm font-semibold text-primary">Editar</a>
                        @endcan
                        @can('delete', $row)
                            <button
                                type="button"
                                class="text-sm font-semibold text-error hover:underline"
                                onclick="document.getElementById('classroom-delete-{{ $row->id }}').showModal()"
                            >
                                Eliminar
                            </button>
                            <x-modal
                                id="classroom-delete-{{ $row->id }}"
                                title="Eliminar aula"
                                description="Esta acción liberará el código del aula para poder usarlo nuevamente."
                                variant="danger"
                            >
                                <p>Eliminarás <strong>{{ $row->name }}</strong>. Esta acción no se puede deshacer desde el panel.</p>
                                <p class="mt-2 text-on-surface-variant">No se permitirá eliminar aulas con alumnos asignados.</p>

                                <x-slot:actions>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface transition-colors hover:bg-surface-container-high focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                        onclick="this.closest('dialog').close()"
                                    >
                                        Cancelar
                                    </button>
                                    <form method="post" action="{{ route('academic.classrooms.destroy', $row) }}" class="contents">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" variant="danger">Confirmar eliminación</x-button>
                                    </form>
                                </x-slot:actions>
                            </x-modal>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-sm text-on-surface-variant">No hay aulas registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </x-table.shell>
@endsection
