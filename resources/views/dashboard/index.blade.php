@extends('layouts.app')

@section('title', 'Dashboard | '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-xl font-semibold text-neutral-900">Dashboard</h1>
            <p class="text-sm text-neutral-600">Resumen general y metricas academicas (sin graficos).</p>
        </div>
    </div>

    @if ($academicMetrics !== null)
        <div class="mb-8">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-neutral-500">Academic cycles</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-neutral-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-medium text-neutral-500">Programaciones activas</p>
                    <p class="mt-1 text-2xl font-semibold text-neutral-900">{{ $academicMetrics['active_schedules'] }}</p>
                </div>
                <div class="rounded-lg border border-neutral-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-medium text-neutral-500">Capacidad total</p>
                    <p class="mt-1 text-2xl font-semibold text-neutral-900">{{ $academicMetrics['total_capacity'] }}</p>
                </div>
                <div class="rounded-lg border border-neutral-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-medium text-neutral-500">Matriculados (suma)</p>
                    <p class="mt-1 text-2xl font-semibold text-neutral-900">{{ $academicMetrics['total_enrolled'] }}</p>
                </div>
                <div class="rounded-lg border border-neutral-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-medium text-neutral-500">Cupos disponibles</p>
                    <p class="mt-1 text-2xl font-semibold text-neutral-900">{{ $academicMetrics['available_spots'] }}</p>
                </div>
            </div>
            <p class="mt-3 text-sm text-neutral-600">
                Ocupacion global:
                <span class="font-semibold text-neutral-900">{{ $academicMetrics['occupancy_percent'] }}%</span>
                (base: programaciones con estado activo).
            </p>
        </div>
    @endif

    <div class="rounded-lg border border-dashed border-neutral-200 bg-white p-8 text-center text-sm text-neutral-500">
        @if ($academicMetrics === null)
            Sin widgets adicionales para su rol. Los administradores ven metricas academicas arriba cuando existan datos.
        @else
            Espacio reservado para asistencia, reportes y estadisticas adicionales.
        @endif
    </div>
@endsection
