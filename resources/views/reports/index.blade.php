@extends('layouts.app')

@section('title', 'Reportes | '.config('app.name'))

@section('content')
    <div class="mb-8">
        <h1 class="font-display text-2xl font-bold tracking-tight text-primary md:text-3xl">Reportes</h1>
        <p class="mt-1 text-sm text-on-surface-variant">
            Genere reportes administrativos descargables segun ano, ciclo, carrera y turno.
        </p>
    </div>

    <section class="max-w-5xl rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-6 shadow-sm">
        <div class="mb-6 flex items-start gap-3 border-b border-outline-variant/50 pb-4">
            <div class="rounded-lg bg-primary-fixed p-2">
                <span class="material-symbols-outlined text-primary">picture_as_pdf</span>
            </div>
            <div>
                <h2 class="font-display text-xl font-semibold text-primary">Reporte de alumnos</h2>
                <p class="mt-1 text-sm text-on-surface-variant">
                    Incluye totales, distribucion por estado, ranking por carrera/ciclo y ultimos pagos registrados.
                </p>
            </div>
        </div>

        <form method="get" action="{{ route('reports.students.pdf') }}" class="grid gap-4 md:grid-cols-4">
            <div>
                <label for="report-year" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Ano</label>
                <select
                    id="report-year"
                    name="year"
                    class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                    <option value="">Todos los anos</option>
                    @foreach ($filterYears as $year)
                        <option value="{{ $year }}" @selected((int) ($filterYear ?? 0) === (int) $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="report-cycle" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Ciclo</label>
                <select
                    id="report-cycle"
                    name="academic_cycle_id"
                    class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                    <option value="">Todos los ciclos</option>
                    @foreach ($filterCycles as $cycle)
                        <option value="{{ $cycle->id }}" @selected((int) ($filterAcademicCycleId ?? 0) === (int) $cycle->id)>
                            {{ $cycle->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="report-career" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Carrera</label>
                <select
                    id="report-career"
                    name="career_id"
                    class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                    <option value="">Todas las carreras</option>
                    @foreach ($filterCareers as $career)
                        <option value="{{ $career->id }}" @selected((int) ($filterCareerId ?? 0) === (int) $career->id)>
                            {{ $career->name }} ({{ $career->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="report-shift" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Turno</label>
                <select
                    id="report-shift"
                    name="shift_id"
                    class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                    <option value="">Todos los turnos</option>
                    @foreach ($filterShifts as $shift)
                        <option value="{{ $shift->id }}" @selected((int) ($filterShiftId ?? 0) === (int) $shift->id)>
                            {{ $shift->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-outline-variant/50 pt-5 md:col-span-4">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary shadow-sm transition-colors hover:bg-primary-container"
                >
                    <span class="material-symbols-outlined text-lg">download</span>
                    Generar PDF
                </button>
                <button
                    type="submit"
                    formaction="{{ route('reports.students.emails') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface shadow-sm transition-colors hover:bg-surface-container-high"
                >
                    <span class="material-symbols-outlined text-lg">mail</span>
                    Generar TXT de correos
                </button>
                <a
                    href="{{ route('reports.index') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface hover:bg-surface-container-high"
                >
                    Limpiar filtros
                </a>
            </div>
        </form>
    </section>

    @if ($canViewActivityReport)
        <section class="mt-8 max-w-7xl rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-6 shadow-sm">
            <div class="mb-6 flex items-start gap-3 border-b border-outline-variant/50 pb-4">
                <div class="rounded-lg bg-primary-fixed p-2">
                    <span class="material-symbols-outlined text-primary">manage_search</span>
                </div>
                <div>
                    <h2 class="font-display text-xl font-semibold text-primary">Auditoria de usuarios</h2>
                    <p class="mt-1 text-sm text-on-surface-variant">
                        Actividades registradas por usuario, modulo y rango de fechas.
                    </p>
                </div>
            </div>

            <form method="get" action="{{ route('reports.index') }}" class="grid gap-4 md:grid-cols-5">
                <div>
                    <label for="activity-date-from" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Desde</label>
                    <input
                        id="activity-date-from"
                        type="date"
                        name="activity_date_from"
                        value="{{ $activityFilters['date_from'] }}"
                        class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                </div>

                <div>
                    <label for="activity-date-to" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Hasta</label>
                    <input
                        id="activity-date-to"
                        type="date"
                        name="activity_date_to"
                        value="{{ $activityFilters['date_to'] }}"
                        class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                </div>

                <div>
                    <label for="activity-staff" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Usuario</label>
                    <select
                        id="activity-staff"
                        name="activity_staff_id"
                        class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        <option value="">Todos los usuarios</option>
                        @foreach ($activityStaffOptions as $staff)
                            <option value="{{ $staff->id }}" @selected((int) ($activityFilters['staff_id'] ?? 0) === (int) $staff->id)>
                                {{ trim($staff->first_name.' '.$staff->last_name) }} ({{ $staff->username }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="activity-module" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Modulo</label>
                    <select
                        id="activity-module"
                        name="activity_module"
                        class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        <option value="">Todos los modulos</option>
                        @foreach ($activityModuleOptions as $module => $label)
                            <option value="{{ $module }}" @selected(($activityFilters['module'] ?? null) === $module)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-end gap-3">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary shadow-sm transition-colors hover:bg-primary-container"
                    >
                        <span class="material-symbols-outlined text-lg">filter_alt</span>
                        Filtrar
                    </button>
                    <a
                        href="{{ route('reports.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface hover:bg-surface-container-high"
                    >
                        Limpiar
                    </a>
                </div>

                <div class="flex flex-wrap gap-3 border-t border-outline-variant/50 pt-5 md:col-span-5">
                    <button
                        type="submit"
                        formaction="{{ route('reports.activity.pdf') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary shadow-sm transition-colors hover:bg-primary-container"
                    >
                        <span class="material-symbols-outlined text-lg">picture_as_pdf</span>
                        Generar PDF
                    </button>
                </div>
            </form>

            <div class="mt-6 overflow-x-auto rounded-lg border border-outline-variant/60">
                <table class="min-w-full divide-y divide-outline-variant/60 text-sm">
                    <thead class="bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Fecha</th>
                            <th class="px-4 py-3 text-left">Usuario</th>
                            <th class="px-4 py-3 text-left">Modulo</th>
                            <th class="px-4 py-3 text-left">Accion</th>
                            <th class="px-4 py-3 text-left">Detalle</th>
                            <th class="px-4 py-3 text-left">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 text-neutral-800">
                        @forelse ($activityLogs as $log)
                            <tr class="hover:bg-neutral-50/80">
                                <td class="whitespace-nowrap px-4 py-3 text-neutral-600">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    {{ $log->staff ? trim($log->staff->first_name.' '.$log->staff->last_name) : 'Usuario eliminado' }}
                                    @if ($log->staff)
                                        <span class="block text-xs text-neutral-500">{{ $log->staff->username }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $activityModuleOptions[$log->module] ?? $log->module }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    {{ match ($log->action) {
                                        'created' => 'Creado',
                                        'updated' => 'Actualizado',
                                        'deleted' => 'Eliminado',
                                        'login' => 'Inicio sesion',
                                        'logout' => 'Cierre sesion',
                                        'download_pdf' => 'PDF',
                                        'download_txt' => 'TXT',
                                        default => $log->action,
                                    } }}
                                </td>
                                <td class="min-w-80 px-4 py-3">
                                    {{ $log->description }}
                                    @if ($log->changeDetails() !== [])
                                        <ul class="mt-2 space-y-1 text-xs text-neutral-600">
                                            @foreach ($log->changeDetails() as $detail)
                                                <li>{{ $detail }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-neutral-600">{{ $log->ip_address ?? '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-neutral-500">
                                    No hay actividades para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($activityLogs->hasPages())
                <div class="mt-4">
                    {{ $activityLogs->links() }}
                </div>
            @endif
        </section>
    @endif
@endsection
