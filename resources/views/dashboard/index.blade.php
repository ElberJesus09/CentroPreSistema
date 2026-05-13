@extends('layouts.app')

@section('title', 'Dashboard | '.config('app.name'))

@section('content')
    <div class="mb-8 flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-neutral-900">Dashboard</h1>
            <p class="mt-1 text-sm text-neutral-600">
                Resumen operativo de postulantes y, si aplica, ocupación de cupos por sede.
            </p>
        </div>
    </div>

    @isset($chartData['kpis'])
        <section class="mb-10">
            <h2 class="mb-4 text-xs font-semibold uppercase tracking-wider text-neutral-500">Alumnos</h2>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-neutral-200/80 bg-white p-5 shadow-sm ring-1 ring-black/[0.02]">
                    <p class="text-xs font-medium text-neutral-500">Total postulantes</p>
                    <p class="mt-2 text-3xl font-semibold tabular-nums text-neutral-900">{{ $chartData['kpis']['students_total'] }}</p>
                </div>
                <div class="rounded-xl border border-amber-200/60 bg-amber-50/50 p-5 shadow-sm">
                    <p class="text-xs font-medium text-amber-900/80">Pendientes</p>
                    <p class="mt-2 text-3xl font-semibold tabular-nums text-amber-950">{{ $chartData['kpis']['students_pending'] }}</p>
                </div>
                <div class="rounded-xl border border-emerald-200/60 bg-emerald-50/40 p-5 shadow-sm">
                    <p class="text-xs font-medium text-emerald-900/80">Activos</p>
                    <p class="mt-2 text-3xl font-semibold tabular-nums text-emerald-950">{{ $chartData['kpis']['students_active'] }}</p>
                </div>
                <div class="rounded-xl border border-red-200/60 bg-red-50/40 p-5 shadow-sm">
                    <p class="text-xs font-medium text-red-900/80">Rechazados</p>
                    <p class="mt-2 text-3xl font-semibold tabular-nums text-red-950">{{ $chartData['kpis']['students_rejected'] }}</p>
                </div>
            </div>
        </section>
    @endisset

    @if ($academicMetrics !== null)
        <section class="mb-10">
            <h2 class="mb-4 text-xs font-semibold uppercase tracking-wider text-neutral-500">Ciclos académicos</h2>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-neutral-200/80 bg-white p-5 shadow-sm ring-1 ring-black/[0.02]">
                    <p class="text-xs font-medium text-neutral-500">Programaciones activas</p>
                    <p class="mt-2 text-3xl font-semibold tabular-nums text-neutral-900">{{ $academicMetrics['active_schedules'] }}</p>
                </div>
                <div class="rounded-xl border border-neutral-200/80 bg-white p-5 shadow-sm ring-1 ring-black/[0.02]">
                    <p class="text-xs font-medium text-neutral-500">Capacidad total</p>
                    <p class="mt-2 text-3xl font-semibold tabular-nums text-neutral-900">{{ $academicMetrics['total_capacity'] }}</p>
                </div>
                <div class="rounded-xl border border-neutral-200/80 bg-white p-5 shadow-sm ring-1 ring-black/[0.02]">
                    <p class="text-xs font-medium text-neutral-500">Matriculados (suma)</p>
                    <p class="mt-2 text-3xl font-semibold tabular-nums text-neutral-900">{{ $academicMetrics['total_enrolled'] }}</p>
                </div>
                <div class="rounded-xl border border-neutral-200/80 bg-white p-5 shadow-sm ring-1 ring-black/[0.02]">
                    <p class="text-xs font-medium text-neutral-500">Cupos disponibles</p>
                    <p class="mt-2 text-3xl font-semibold tabular-nums text-neutral-900">{{ $academicMetrics['available_spots'] }}</p>
                </div>
            </div>
            <p class="mt-4 text-sm text-neutral-600">
                Ocupación global en turnos activos:
                <span class="font-semibold text-brand">{{ $academicMetrics['occupancy_percent'] }}%</span>
            </p>
        </section>
    @endif

    @if ($showCharts ?? false)
        <div class="grid gap-6 lg:grid-cols-2">
            @isset($chartPayload['student_status'])
                <div class="rounded-xl border border-neutral-200/80 bg-white p-6 shadow-sm ring-1 ring-black/[0.02]">
                    <h3 class="text-sm font-semibold text-neutral-900">Estado de postulantes</h3>
                    <p class="mt-0.5 text-xs text-neutral-500">Distribución actual por estado</p>
                    <div class="relative mt-4 h-64">
                        <canvas id="chart-student-status" aria-label="Gráfico estado de postulantes"></canvas>
                    </div>
                </div>
            @endisset

            @isset($chartPayload['careers'])
                <div class="rounded-xl border border-neutral-200/80 bg-white p-6 shadow-sm ring-1 ring-black/[0.02]">
                    <h3 class="text-sm font-semibold text-neutral-900">Top carreras</h3>
                    <p class="mt-0.5 text-xs text-neutral-500">Hasta 8 carreras con más postulantes</p>
                    <div class="relative mt-4 h-64">
                        <canvas id="chart-careers" aria-label="Gráfico postulantes por carrera"></canvas>
                    </div>
                </div>
            @endisset

            @isset($chartPayload['registrations'])
                <div class="rounded-xl border border-neutral-200/80 bg-white p-6 shadow-sm ring-1 ring-black/[0.02] lg:col-span-2">
                    <h3 class="text-sm font-semibold text-neutral-900">Inscripciones por mes</h3>
                    <p class="mt-0.5 text-xs text-neutral-500">Últimos 6 meses según fecha de inscripción</p>
                    <div class="relative mt-4 h-72">
                        <canvas id="chart-registrations" aria-label="Gráfico inscripciones por mes"></canvas>
                    </div>
                </div>
            @endisset

            @isset($chartPayload['occupancy'])
                <div class="rounded-xl border border-neutral-200/80 bg-white p-6 shadow-sm ring-1 ring-black/[0.02]">
                    <h3 class="text-sm font-semibold text-neutral-900">Cupos globales</h3>
                    <p class="mt-0.5 text-xs text-neutral-500">Turnos activos: matriculados vs disponibles</p>
                    <div class="relative mt-4 h-64">
                        <canvas id="chart-occupancy" aria-label="Gráfico ocupación global"></canvas>
                    </div>
                </div>
            @endisset

            @isset($chartPayload['campus_load'])
                <div class="rounded-xl border border-neutral-200/80 bg-white p-6 shadow-sm ring-1 ring-black/[0.02]">
                    <h3 class="text-sm font-semibold text-neutral-900">Carga por sede</h3>
                    <p class="mt-0.5 text-xs text-neutral-500">Matriculados y cupos libres por sede</p>
                    <div class="relative mt-4 h-64">
                        <canvas id="chart-campus-load" aria-label="Gráfico carga por sede"></canvas>
                    </div>
                </div>
            @endisset
        </div>

        <script type="application/json" id="dashboard-chart-payload">@json($chartPayload)</script>
    @elseif ($academicMetrics === null && empty($chartData['kpis'] ?? null))
        <div class="rounded-xl border border-dashed border-neutral-200 bg-white p-10 text-center text-sm text-neutral-500">
            No hay widgets configurados para su rol o aún no tiene acceso a los módulos de alumnos o ciclos académicos.
        </div>
    @endif
@endsection

@if ($showCharts ?? false)
    @push('scripts')
        @vite(['resources/js/dashboard.js'])
    @endpush
@endif
