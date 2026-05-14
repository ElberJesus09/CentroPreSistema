@extends('layouts.app')

@section('title', 'Panel | '.config('app.name'))

@section('content')
    <div class="mb-8 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
        <div>
            <h1 class="font-display text-2xl font-bold tracking-tight text-primary md:text-3xl">Panel</h1>
            <p class="mt-1 text-sm text-on-surface-variant">
                Resumen operativo de postulantes y, si aplica, ocupación de cupos por sede.
            </p>
        </div>
        @if ($filterYears->isNotEmpty() || $filterCareers->isNotEmpty())
            <form method="get" action="{{ route('dashboard') }}" class="flex flex-wrap items-end gap-3 rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-4 shadow-sm">
                <div class="min-w-[10rem] flex-1">
                    <label for="dash-year" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Año (inscripción)</label>
                    <select
                        id="dash-year"
                        name="year"
                        class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        onchange="this.form.submit()"
                    >
                        <option value="">Todos los años</option>
                        @foreach ($filterYears as $y)
                            <option value="{{ $y }}" @selected((int) ($filterYear ?? 0) === (int) $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[12rem] flex-[2]">
                    <label for="dash-career" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Carrera</label>
                    <select
                        id="dash-career"
                        name="career_id"
                        class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        onchange="this.form.submit()"
                    >
                        <option value="">Todas las carreras</option>
                        @foreach ($filterCareers as $c)
                            <option value="{{ $c->id }}" @selected((int) ($filterCareerId ?? 0) === (int) $c->id)>
                                {{ $c->name }} ({{ $c->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @if ($filterYear !== null || $filterCareerId !== null)
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-outline-variant px-3 py-2 text-sm font-semibold text-primary hover:bg-surface-container-high"
                    >
                        Limpiar filtros
                    </a>
                @endif
            </form>
        @endif
    </div>

    @isset($chartData['kpis'])
        <section class="mb-10">
            <h2 class="mb-4 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Alumnos</h2>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-5 shadow-sm">
                    <p class="text-xs font-semibold text-on-surface-variant">Total postulantes</p>
                    <p class="mt-2 text-3xl font-bold tabular-nums text-primary">{{ $chartData['kpis']['students_total'] }}</p>
                </div>
                <div class="rounded-xl border border-secondary-container/50 bg-secondary-container/20 p-5 shadow-sm">
                    <p class="text-xs font-semibold text-on-secondary-container">Pendientes</p>
                    <p class="mt-2 text-3xl font-bold tabular-nums text-on-secondary-container">{{ $chartData['kpis']['students_pending'] }}</p>
                </div>
                <div class="rounded-xl border border-emerald-200/60 bg-emerald-50/40 p-5 shadow-sm">
                    <p class="text-xs font-semibold text-emerald-900/80">Activos</p>
                    <p class="mt-2 text-3xl font-bold tabular-nums text-emerald-950">{{ $chartData['kpis']['students_active'] }}</p>
                </div>
                <div class="rounded-xl border border-error/30 bg-error-container/50 p-5 shadow-sm">
                    <p class="text-xs font-semibold text-error">Rechazados</p>
                    <p class="mt-2 text-3xl font-bold tabular-nums text-error">{{ $chartData['kpis']['students_rejected'] }}</p>
                </div>
            </div>
        </section>
    @endisset

    @if ($academicMetrics !== null)
        <section class="mb-10">
            <h2 class="mb-4 text-xs font-bold uppercase tracking-wider text-on-surface-variant">Ciclos académicos</h2>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-5 shadow-sm">
                    <p class="text-xs font-semibold text-on-surface-variant">Programaciones activas</p>
                    <p class="mt-2 text-3xl font-bold tabular-nums text-primary">{{ $academicMetrics['active_schedules'] }}</p>
                </div>
                <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-5 shadow-sm">
                    <p class="text-xs font-semibold text-on-surface-variant">Capacidad total</p>
                    <p class="mt-2 text-3xl font-bold tabular-nums text-primary">{{ $academicMetrics['total_capacity'] }}</p>
                </div>
                <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-5 shadow-sm">
                    <p class="text-xs font-semibold text-on-surface-variant">Matriculados (suma)</p>
                    <p class="mt-2 text-3xl font-bold tabular-nums text-primary">{{ $academicMetrics['total_enrolled'] }}</p>
                </div>
                <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-5 shadow-sm">
                    <p class="text-xs font-semibold text-on-surface-variant">Cupos disponibles</p>
                    <p class="mt-2 text-3xl font-bold tabular-nums text-primary">{{ $academicMetrics['available_spots'] }}</p>
                </div>
            </div>
            <p class="mt-4 text-sm text-on-surface-variant">
                Ocupación global en turnos activos:
                <span class="font-bold text-secondary">{{ $academicMetrics['occupancy_percent'] }}%</span>
            </p>
        </section>
    @endif

    @if ($showCharts ?? false)
        <div class="grid gap-6 lg:grid-cols-2">
            @isset($chartPayload['student_status'])
                <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-primary">Estado de postulantes</h3>
                    <p class="mt-0.5 text-xs text-on-surface-variant">Distribución actual por estado</p>
                    <div class="relative mt-4 h-64">
                        <canvas id="chart-student-status" aria-label="Gráfico estado de postulantes"></canvas>
                    </div>
                </div>
            @endisset

            @isset($chartPayload['careers'])
                <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-primary">Top carreras</h3>
                    <p class="mt-0.5 text-xs text-on-surface-variant">Hasta 8 carreras con más postulantes</p>
                    <div class="relative mt-4 h-64">
                        <canvas id="chart-careers" aria-label="Gráfico postulantes por carrera"></canvas>
                    </div>
                </div>
            @endisset

            @isset($chartPayload['registrations'])
                <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-6 shadow-sm lg:col-span-2">
                    <h3 class="text-sm font-bold text-primary">Inscripciones por mes</h3>
                    <p class="mt-0.5 text-xs text-on-surface-variant">Últimos 6 meses según fecha de inscripción</p>
                    <div class="relative mt-4 h-72">
                        <canvas id="chart-registrations" aria-label="Gráfico inscripciones por mes"></canvas>
                    </div>
                </div>
            @endisset

            @isset($chartPayload['occupancy'])
                <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-primary">Cupos globales</h3>
                    <p class="mt-0.5 text-xs text-on-surface-variant">Turnos activos: matriculados vs disponibles</p>
                    <div class="relative mt-4 h-64">
                        <canvas id="chart-occupancy" aria-label="Gráfico ocupación global"></canvas>
                    </div>
                </div>
            @endisset

            @isset($chartPayload['campus_load'])
                <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-primary">Carga por sede</h3>
                    <p class="mt-0.5 text-xs text-on-surface-variant">Matriculados y cupos libres por sede</p>
                    <div class="relative mt-4 h-64">
                        <canvas id="chart-campus-load" aria-label="Gráfico carga por sede"></canvas>
                    </div>
                </div>
            @endisset
        </div>

        <script type="application/json" id="dashboard-chart-payload">@json($chartPayload)</script>
    @elseif ($academicMetrics === null && empty($chartData['kpis'] ?? null))
        <div class="rounded-xl border border-dashed border-outline-variant bg-surface-container-lowest p-10 text-center text-sm text-on-surface-variant">
            No hay widgets configurados para su rol o aún no tiene acceso a los módulos de alumnos o ciclos académicos.
        </div>
    @endif
@endsection

@if ($showCharts ?? false)
    @push('scripts')
        @vite(['resources/js/dashboard.js'])
    @endpush
@endif
