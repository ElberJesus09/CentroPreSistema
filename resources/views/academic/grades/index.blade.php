@extends('layouts.app')

@section('title', 'Notas Académicas | '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <h1 class="font-display text-2xl font-bold text-primary">Notas Académicas</h1>
            <p class="text-sm text-on-surface-variant">Evaluaciones flexibles, promedios ponderados y rankings.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('academic.distribution.index', ['academic_cycle_id' => $cycleId]) }}" class="rounded-lg border border-outline-variant px-4 py-2 text-sm font-semibold text-primary">Distribución</a>
            <a href="{{ route('academic.reports.index', ['academic_cycle_id' => $cycleId]) }}" class="rounded-lg border border-outline-variant px-4 py-2 text-sm font-semibold text-primary">Reportes</a>
            <a href="{{ route('academic.classrooms.index') }}" class="rounded-lg border border-outline-variant px-4 py-2 text-sm font-semibold text-primary">Aulas</a>
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

    @if (session('grades_preview'))
        @php $preview = session('grades_preview'); @endphp
        <x-modal
            id="grades-preview-modal"
            title="Vista previa de importación"
            description="Filas válidas: {{ $preview['validos'] }} · Errores: {{ count($preview['errores']) }} · Omitidas: {{ $preview['omitidos'] }}"
        >
            <div class="hidden">
                <div>
                    <h2 class="font-semibold text-primary">Vista previa de importación</h2>
                    <p class="text-sm text-on-surface-variant">
                        Filas válidas: {{ $preview['validos'] }} · Errores: {{ count($preview['errores']) }} · Omitidas: {{ $preview['omitidos'] }}
                    </p>
                </div>
                <form method="post" action="{{ route('academic.grades.import.confirm') }}">
                    @csrf
                    <x-button type="submit">Confirmar importación</x-button>
                </form>
            </div>

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
                    action="{{ route('academic.grades.import.confirm') }}"
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
                    document.getElementById('grades-preview-modal')?.showModal();
                });
            </script>
        @endpush
    @endif

    <form method="get" class="mb-5 grid gap-3 rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-4 md:grid-cols-4 lg:grid-cols-7 md:items-end">
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-on-surface-variant">Ciclo</label>
            <select name="academic_cycle_id" class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm">
                @foreach ($cycles as $cycle)
                    <option value="{{ $cycle->id }}" @selected((int) $cycleId === (int) $cycle->id)>{{ $cycle->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-on-surface-variant">Carrera</label>
            <select name="career_id" class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm">
                <option value="">Todas</option>
                @foreach ($careers as $career)
                    <option value="{{ $career->id }}" @selected((int) ($filters['career_id'] ?? 0) === (int) $career->id)>{{ $career->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-on-surface-variant">Turno</label>
            <select name="shift_id" class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm">
                <option value="">Todos</option>
                @foreach ($shifts as $shift)
                    <option value="{{ $shift->id }}" @selected((int) ($filters['shift_id'] ?? 0) === (int) $shift->id)>{{ $shift->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-on-surface-variant">Aula</label>
            <select name="classroom_id" class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm">
                <option value="">Todas</option>
                @foreach ($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected((int) ($filters['classroom_id'] ?? 0) === (int) $classroom->id)>{{ $classroom->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-on-surface-variant">Buscar</label>
            <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="DNI o nombre" class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-on-surface-variant">Registros</label>
            <select name="per_page" class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm">
                @foreach ([50, 100, 200] as $size)
                    <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 50) === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </div>
        <x-button type="submit">Filtrar</x-button>
    </form>

    @if ($cycleId !== null)
        <div class="mb-5 grid gap-4 lg:grid-cols-2">
            <form method="post" action="{{ route('academic.grades.import') }}" enctype="multipart/form-data" class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-4">
                @csrf
                <input type="hidden" name="academic_cycle_id" value="{{ $cycleId }}">
                <h2 class="font-semibold text-primary">Importación masiva de notas</h2>
                <p class="mb-3 text-sm text-on-surface-variant">Excel, CSV o TXT con columnas: DNI, evaluación y nota.</p>
                <input name="file" type="file" accept=".xlsx,.csv,.txt" class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm" required>
                <x-button type="submit" class="mt-3">Ver vista previa</x-button>
            </form>

            @can('create', \App\Models\Evaluation::class)
                <form method="post" action="{{ route('academic.evaluations.store') }}" class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-4">
                    @csrf
                    <input type="hidden" name="academic_cycle_id" value="{{ $cycleId }}">
                    <div class="mb-3 rounded-lg border border-primary/15 bg-primary-fixed/30 p-3 text-sm text-on-surface">
                        <p class="font-semibold text-primary">Guía rápida</p>
                        <p class="mt-1"><strong>Nombre:</strong> cómo aparecerá la evaluación, por ejemplo “Simulacro 1”.</p>
                        <p><strong>Tipo:</strong> categoría interna; puedes dejar “regular”.</p>
                        <p><strong>Peso:</strong> 1 vale normal, 2 cuenta el doble, 0 no suma al promedio.</p>
                        <p><strong>Decimales:</strong> cantidad de decimales para mostrar el promedio.</p>
                    </div>
                    <h2 class="font-semibold text-primary">Nueva evaluación</h2>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        <label class="space-y-1">
                            <span class="block text-xs font-bold uppercase text-on-surface-variant">Nombre</span>
                            <input name="name" placeholder="Ejemplo: Simulacro 1" class="w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm" required>
                        </label>
                        <label class="space-y-1">
                            <span class="block text-xs font-bold uppercase text-on-surface-variant">Tipo</span>
                            <input name="type" placeholder="Ejemplo: simulacro" value="regular" class="w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm" required>
                        </label>
                        <label class="space-y-1">
                            <span class="block text-xs font-bold uppercase text-on-surface-variant">Peso</span>
                            <input name="weight" type="number" step="0.01" min="0" max="100" value="1" class="w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm" required>
                        </label>
                        <label class="space-y-1">
                            <span class="block text-xs font-bold uppercase text-on-surface-variant">Promedio</span>
                            <select name="counts_for_average" class="w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm"><option value="1">Cuenta para promedio</option><option value="0">No cuenta para promedio</option></select>
                        </label>
                        <label class="space-y-1">
                            <span class="block text-xs font-bold uppercase text-on-surface-variant">Decimales</span>
                            <input name="rounding_decimals" type="number" min="0" max="4" value="2" class="w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm" required>
                        </label>
                        <label class="space-y-1">
                            <span class="block text-xs font-bold uppercase text-on-surface-variant">Estado</span>
                            <select name="status" class="w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm"><option value="1">Activa</option><option value="0">Inactiva</option></select>
                        </label>
                    </div>
                    <x-button type="submit" class="mt-3">Guardar evaluación</x-button>
                </form>
            @endcan
        </div>

        <div class="mb-5 rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-4">
            <h2 class="mb-3 font-semibold text-primary">Evaluaciones del ciclo</h2>
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($overview['evaluations'] as $evaluation)
                    <div class="rounded-lg border border-outline-variant/60 p-3">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold">{{ $evaluation->name }}</p>
                            <span class="rounded-full bg-surface-container-high px-2 py-0.5 text-xs">{{ $evaluation->counts_for_average ? 'Promedia' : 'Excluida' }}</span>
                        </div>
                        <p class="text-sm text-on-surface-variant">Tipo: {{ $evaluation->type }} · Peso: {{ $evaluation->weight }}</p>
                    </div>
                @empty
                    <p class="text-sm text-on-surface-variant">Aún no hay evaluaciones registradas.</p>
                @endforelse
            </div>
        </div>

        <x-table.shell>
            @if ($overview['students']->hasPages())
                <x-slot:footer>
                    {{ $overview['students']->links() }}
                </x-slot:footer>
            @endif
            <thead class="bg-surface-container-high text-xs font-bold uppercase text-on-surface-variant">
                <tr>
                    <th class="px-4 py-3">DNI</th>
                    <th class="px-4 py-3">Alumno</th>
                    <th class="px-4 py-3">Carrera</th>
                    <th class="px-4 py-3">Turno</th>
                    <th class="px-4 py-3">Aula</th>
                    @foreach ($overview['evaluations'] as $evaluation)
                        <th class="px-4 py-3">{{ $evaluation->name }}</th>
                    @endforeach
                    <th class="px-4 py-3">Promedio</th>
                    <th class="px-4 py-3">Ranking</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/50">
                @forelse ($overview['students'] as $student)
                    @php $grades = $student->grades->keyBy('evaluation_id'); $rank = $overview['rankings'][$student->id] ?? ['promedio' => 0, 'ranking' => null]; @endphp
                    <tr>
                        <td class="px-4 py-3">{{ $student->dni }}</td>
                        <td class="min-w-[12rem] px-4 py-3 font-semibold">{{ $student->fullName() }}</td>
                        <td class="px-4 py-3">{{ $student->career?->name }}</td>
                        <td class="px-4 py-3">{{ $student->schedule?->shift?->name }}</td>
                        <td class="px-4 py-3">{{ $student->classroomAssignments->first()?->classroom?->name ?? 'Sin aula' }}</td>
                        @foreach ($overview['evaluations'] as $evaluation)
                            <td class="px-4 py-3">{{ isset($grades[$evaluation->id]) ? number_format((float) $grades[$evaluation->id]->score, 2) : '—' }}</td>
                        @endforeach
                        <td class="px-4 py-3 font-bold">{{ number_format((float) $rank['promedio'], 2) }}</td>
                        <td class="px-4 py-3">{{ $rank['ranking'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ 7 + $overview['evaluations']->count() }}" class="px-4 py-8 text-center text-sm text-on-surface-variant">No hay alumnos para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </x-table.shell>
    @endif
@endsection
