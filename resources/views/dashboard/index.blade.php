@extends('layouts.app')

@section('title', 'Dashboard | '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-xl font-semibold text-neutral-900">Dashboard</h1>
            <p class="text-sm text-neutral-600">Resumen general (estructura preparada para metricas futuras).</p>
        </div>
    </div>
    <div class="rounded-lg border border-dashed border-neutral-200 bg-white p-8 text-center text-sm text-neutral-500">
        Sin widgets configurados. Aqui podra incorporar asistencia, reportes y estadisticas.
    </div>
@endsection
