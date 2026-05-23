@extends('layouts.app')

@section('title', 'Alumnos | '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="font-display text-xl font-bold text-primary md:text-2xl">Alumnos</h1>
            <p class="text-sm text-on-surface-variant">Postulantes y matrículas por carrera y turno.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('viewAny', \App\Models\Student::class)
                <a
                    href="{{ route('students.cards.create') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-outline-variant px-4 py-2 text-sm font-semibold text-primary hover:bg-surface-container-high"
                >
                    Carnets
                </a>
            @endcan
        @can('create', \App\Models\Student::class)
            <a
                href="{{ route('students.create') }}"
                class="inline-flex items-center justify-center rounded-lg border border-transparent bg-primary px-4 py-2 text-sm font-semibold text-on-primary shadow-sm transition-colors hover:bg-primary-container focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Registrar alumno
            </a>
        @endcan
        </div>
    </div>

    <form method="get" action="{{ route('students.index') }}" class="mb-5 grid gap-3 rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-4 shadow-sm md:grid-cols-[minmax(12rem,2fr)_minmax(9rem,1fr)_minmax(12rem,1.4fr)_auto] md:items-end">
        <div>
            <label for="student-search" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Buscar</label>
            <input
                id="student-search"
                name="search"
                type="search"
                value="{{ $filters['search'] ?? '' }}"
                placeholder="DNI, alumno, voucher, carrera, correo, ciclo o turno"
                class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
        </div>
        <div>
            <label for="student-year" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Año</label>
            <select
                id="student-year"
                name="year"
                class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
                <option value="">Todos</option>
                @foreach ($filterYears as $year)
                    <option value="{{ $year }}" @selected((int) ($filters['year'] ?? 0) === (int) $year)>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="student-cycle" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Ciclo</label>
            <select
                id="student-cycle"
                name="academic_cycle_id"
                class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
                <option value="">Todos los ciclos</option>
                @foreach ($filterCycles as $cycle)
                    <option value="{{ $cycle->id }}" @selected((int) ($filters['academic_cycle_id'] ?? 0) === (int) $cycle->id)>
                        {{ $cycle->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button
                type="submit"
                class="inline-flex flex-1 items-center justify-center rounded-lg border border-transparent bg-primary px-4 py-2 text-sm font-semibold text-on-primary shadow-sm transition-colors hover:bg-primary-container focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 md:flex-none"
            >
                Filtrar
            </button>
            @if (($filters['search'] ?? '') !== '' || ($filters['year'] ?? null) !== null || ($filters['academic_cycle_id'] ?? null) !== null)
                <a
                    href="{{ route('students.index') }}"
                    class="inline-flex flex-1 items-center justify-center rounded-lg border border-outline-variant px-3 py-2 text-sm font-semibold text-primary hover:bg-surface-container-high md:flex-none"
                >
                    Limpiar
                </a>
            @endif
        </div>
    </form>

    <x-table.shell>
        @if ($students->hasPages())
            <x-slot:footer>
                {{ $students->links() }}
            </x-slot:footer>
        @endif
        <thead class="bg-surface-container-high text-xs font-bold uppercase tracking-wide text-on-surface-variant">
            <tr>
                <th class="px-4 py-3">Alumno</th>
                <th class="px-4 py-3">DNI</th>
                <th class="px-4 py-3">Carrera</th>
                <th class="px-4 py-3">Ciclo</th>
                <th class="px-4 py-3">Turno</th>
                <th class="px-4 py-3">Estado</th>
                <th class="px-4 py-3">Fecha registro</th>
                <th class="sticky right-0 bg-surface-container-high px-4 py-3 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/50 text-on-surface">
            @forelse ($students as $row)
                <tr class="hover:bg-surface-container-low/80">
                    <td class="min-w-[10rem] px-4 py-3 font-medium">{{ $row->fullName() }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $row->dni }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $row->career?->name }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $row->academicCycle?->name ?? $row->schedule?->academicCycle?->name }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $row->schedule?->shift?->name }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        @if ($row->status === \App\Models\Student::STATUS_ACTIVE)
                            <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-800">Activo</span>
                        @elseif ($row->status === \App\Models\Student::STATUS_REJECTED)
                            <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-800">Rechazado</span>
                        @else
                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-900">Pendiente</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-on-surface-variant">{{ $row->registration_date?->format('Y-m-d') }}</td>
                    <td
                        class="sticky right-0 whitespace-nowrap bg-surface-container-lowest px-4 py-3 text-right shadow-[-6px_0_8px_-6px_rgba(0,0,0,0.08)]"
                    >
                        @can('view', $row)
                            <a href="{{ route('students.show', $row) }}" class="mr-2 text-sm font-semibold text-on-surface-variant hover:text-primary hover:underline">
                                Ver
                            </a>
                        @endcan
                        @can('update', $row)
                            <a href="{{ route('students.edit', $row) }}" class="mr-2 text-sm font-semibold text-primary hover:underline">
                                Editar
                            </a>
                        @endcan
                        @can('delete', $row)
                            <button
                                type="button"
                                class="text-sm font-semibold text-error hover:underline"
                                onclick="document.getElementById('student-delete-{{ $row->id }}').showModal()"
                            >
                                Eliminar
                            </button>
                            <x-modal
                                id="student-delete-{{ $row->id }}"
                                title="Eliminar alumno"
                                description="Esta acción no se puede deshacer."
                                variant="danger"
                            >
                                <p>Eliminaras a <strong>{{ $row->fullName() }}</strong> y se liberara el cupo del turno.</p>
                                <p class="mt-2 text-on-surface-variant">No se eliminaran colegios o apoderados que todavia esten vinculados a otros alumnos.</p>

                                <x-slot:actions>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface transition-colors hover:bg-surface-container-high focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                        onclick="this.closest('dialog').close()"
                                    >
                                        Cancelar
                                    </button>
                                    <form method="post" action="{{ route('students.destroy', $row) }}" class="contents">
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
                    <td colspan="8" class="px-4 py-8 text-center text-sm text-on-surface-variant">No hay alumnos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </x-table.shell>
@endsection
