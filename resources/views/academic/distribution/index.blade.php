@extends('layouts.app')

@section('title', 'Distribución académica | '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <h1 class="font-display text-2xl font-bold text-primary">Distribución académica de alumnos</h1>
            <p class="text-sm text-on-surface-variant">Ordena por examen de ubicación, respeta capacidad y conserva movimientos manuales.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('academic.classrooms.index') }}" class="rounded-lg border border-outline-variant px-4 py-2 text-sm font-semibold text-primary">Aulas</a>
            <a href="{{ route('academic.grades.index', ['academic_cycle_id' => $cycleId]) }}" class="rounded-lg border border-outline-variant px-4 py-2 text-sm font-semibold text-primary">Notas</a>
        </div>
    </div>

    @if (session('import_errors'))
        <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p class="font-semibold">Observaciones de importación</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach (array_slice(session('import_errors'), 0, 20) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('placement_preview'))
        @php $preview = session('placement_preview'); @endphp
        <x-modal
            id="placement-preview-modal"
            title="Vista previa de importación"
            description="Filas válidas: {{ $preview['validos'] }} · Errores: {{ count($preview['errores']) }} · Omitidas: {{ $preview['omitidos'] }}"
        >
            @if (count($preview['muestra']) > 0)
                <div class="max-h-80 overflow-x-auto overflow-y-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs font-bold uppercase text-on-surface-variant">
                            <tr>
                                <th class="px-3 py-2">DNI</th>
                                <th class="px-3 py-2">Nombre</th>
                                <th class="px-3 py-2">Nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($preview['muestra'] as $row)
                                <tr class="border-t border-outline-variant/50">
                                    <td class="px-3 py-2">{{ $row['dni'] }}</td>
                                    <td class="px-3 py-2">{{ $row['alumno'] }}</td>
                                    <td class="px-3 py-2">{{ number_format((float) $row['nota'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-on-surface-variant">No hay filas válidas para mostrar.</p>
            @endif

            @if (count($preview['errores']) > 0)
                <div class="mt-4 max-h-40 overflow-y-auto rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                    <p class="font-semibold">Errores encontrados</p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach (array_slice($preview['errores'], 0, 20) as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-slot:actions>
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface transition-colors hover:bg-surface-container-high focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    onclick="this.closest('dialog').close()"
                >
                    Cancelar
                </button>
                <form
                    method="post"
                    action="{{ route('academic.distribution.import.confirm') }}"
                    class="contents"
                    data-confirm-title="Confirmar importación"
                    data-confirm-message="¿Deseas guardar todas las notas válidas de la vista previa?"
                    data-confirm-button="Guardar notas"
                >
                    @csrf
                    <x-button type="submit">Confirmar importación</x-button>
                </form>
            </x-slot:actions>
        </x-modal>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    document.getElementById('placement-preview-modal')?.showModal();
                });
            </script>
        @endpush
    @endif

    <form method="get" class="mb-5 grid gap-3 rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-4 md:grid-cols-[1fr_1fr_1fr_auto] md:items-end">
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-on-surface-variant">Ciclo</label>
            <select name="academic_cycle_id" class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm">
                @foreach ($cycles as $cycle)
                    <option value="{{ $cycle->id }}" @selected((int) $cycleId === (int) $cycle->id)>{{ $cycle->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-on-surface-variant">Aula</label>
            <select name="classroom_id" class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm">
                <option value="">Todas</option>
                @foreach ($activeClassrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected((int) ($filters['classroom_id'] ?? 0) === (int) $classroom->id)>{{ $classroom->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-on-surface-variant">Buscar</label>
            <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="DNI o alumno" class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm">
        </div>
        <x-button type="submit">Filtrar</x-button>
    </form>

    @if ($cycleId !== null)
        <div class="mb-5 grid gap-4 lg:grid-cols-2">
            <form method="post" action="{{ route('academic.distribution.import') }}" enctype="multipart/form-data" class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-4">
                @csrf
                <input type="hidden" name="academic_cycle_id" value="{{ $cycleId }}">
                <h2 class="font-semibold text-primary">Importar examen de ubicación</h2>
                <p class="mb-3 text-sm text-on-surface-variant">Excel, CSV o TXT con columnas: DNI y nota.</p>
                <input name="file" type="file" accept=".xlsx,.csv,.txt" class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm" required>
                <x-button type="submit" class="mt-3">Ver vista previa</x-button>
            </form>
            <form
                method="post"
                action="{{ route('academic.distribution.run') }}"
                class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-4"
                data-confirm-title="Ejecutar distribución"
                data-confirm-message="¿Deseas ejecutar la distribución automática para este ciclo?"
                data-confirm-button="Distribuir alumnos"
            >
                @csrf
                <input type="hidden" name="academic_cycle_id" value="{{ $cycleId }}">
                <h2 class="font-semibold text-primary">Distribución automática</h2>
                <p class="mb-3 text-sm text-on-surface-variant">Asigna mejores notas a aulas de mayor prioridad y respeta cupos.</p>
                <label class="mb-3 flex items-center gap-2 text-sm">
                    <input type="checkbox" name="regenerate" value="1">
                    Regenerar distribución no bloqueada
                </label>
                <x-button type="submit">Distribuir alumnos</x-button>
            </form>
        </div>

        <div class="mb-5 grid gap-3 md:grid-cols-3">
            @foreach ($dashboard['classrooms'] as $classroom)
                @php
                    $used = $classroom->assignments_count;
                    $full = $used >= $classroom->capacity;
                @endphp
                <div class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-4">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold">{{ $classroom->name }}</p>
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $full ? 'bg-red-50 text-red-800' : 'bg-green-50 text-green-800' }}">
                            {{ $full ? 'Llena' : 'Disponible' }}
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-on-surface-variant">Capacidad usada: {{ $used }} / {{ $classroom->capacity }}</p>
                    <p class="text-sm text-on-surface-variant">Prioridad: {{ $classroom->academic_priority }}</p>
                </div>
            @endforeach
        </div>

        <x-table.shell>
            @if ($dashboard['students']->hasPages())
                <x-slot:footer>
                    {{ $dashboard['students']->links() }}
                </x-slot:footer>
            @endif
            <thead class="bg-surface-container-high text-xs font-bold uppercase text-on-surface-variant">
                <tr>
                    <th class="px-4 py-3">Alumno</th>
                    <th class="px-4 py-3">DNI</th>
                    <th class="px-4 py-3">Nota ubicación</th>
                    <th class="px-4 py-3">Aula</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">Mover</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/50">
                @forelse ($dashboard['students'] as $assignment)
                    <tr>
                        <td class="px-4 py-3 font-semibold">{{ $assignment->student?->fullName() }}</td>
                        <td class="px-4 py-3">{{ $assignment->student?->dni }}</td>
                        <td class="px-4 py-3">{{ number_format((float) $assignment->placement_score, 2) }}</td>
                        <td class="px-4 py-3">{{ $assignment->classroom?->name ?? 'Sin aula' }}</td>
                        <td class="px-4 py-3">
                            <form method="post" action="{{ route('academic.distribution.lock', $assignment) }}">
                                @csrf
                                <button class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $assignment->distribution_locked ? 'bg-amber-50 text-amber-900' : 'bg-surface-container-high text-on-surface-variant' }}">
                                    {{ $assignment->distribution_locked ? 'Bloqueado' : 'Libre' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="post" action="{{ route('academic.distribution.move') }}" class="inline-flex flex-wrap justify-end gap-2">
                                @csrf
                                <input type="hidden" name="academic_cycle_id" value="{{ $cycleId }}">
                                <input type="hidden" name="student_id" value="{{ $assignment->student_id }}">
                                <select name="classroom_id" class="rounded-lg border border-outline-variant bg-white px-2 py-1 text-sm" required>
                                    @foreach ($activeClassrooms as $classroom)
                                        <option value="{{ $classroom->id }}" @selected((int) $assignment->classroom_id === (int) $classroom->id)>{{ $classroom->name }}</option>
                                    @endforeach
                                </select>
                                <input name="reason" placeholder="Motivo opcional" class="w-40 rounded-lg border border-outline-variant bg-white px-2 py-1 text-sm">
                                <button
                                    class="text-sm font-semibold text-primary"
                                    data-confirm-title="Mover alumno"
                                    data-confirm-message="¿Confirmas mover al alumno al aula seleccionada?"
                                    data-confirm-button="Mover alumno"
                                >
                                    Guardar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-on-surface-variant">No hay alumnos con nota de ubicación importada.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-table.shell>
    @endif
@endsection
