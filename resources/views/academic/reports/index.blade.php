@extends('layouts.app')

@section('title', 'Reportes académicos | '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <h1 class="font-display text-2xl font-bold text-primary">Reportes académicos</h1>
            <p class="text-sm text-on-surface-variant">Rankings, promedios, destacados y desaprobados.</p>
        </div>
        @if ($cycleId)
            <div class="flex gap-2">
                <a href="{{ route('academic.reports.excel', ['academic_cycle_id' => $cycleId]) }}" class="rounded-lg border border-outline-variant px-4 py-2 text-sm font-semibold text-primary">Exportar Excel</a>
                <a href="{{ route('academic.reports.pdf', ['academic_cycle_id' => $cycleId]) }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-on-primary">Exportar PDF</a>
            </div>
        @endif
    </div>
    <form method="get" class="mb-5 rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-4">
        <label class="mb-1 block text-xs font-bold uppercase text-on-surface-variant">Ciclo</label>
        <div class="flex gap-2">
            <select name="academic_cycle_id" class="block w-full max-w-sm rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm">
                @foreach ($cycles as $cycle)
                    <option value="{{ $cycle->id }}" @selected((int) $cycleId === (int) $cycle->id)>{{ $cycle->name }}</option>
                @endforeach
            </select>
            <x-button type="submit">Ver reporte</x-button>
        </div>
    </form>
    @if ($report)
        <div class="mb-5 grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-4"><p class="text-sm text-on-surface-variant">Promedio general</p><p class="text-2xl font-bold text-primary">{{ number_format($report['promedio_general'], 2) }}</p></div>
            <div class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-4"><p class="text-sm text-on-surface-variant">Alumnos destacados</p><p class="text-2xl font-bold text-green-800">{{ $report['destacados']->count() }}</p></div>
            <div class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-4"><p class="text-sm text-on-surface-variant">Alumnos desaprobados</p><p class="text-2xl font-bold text-red-800">{{ $report['desaprobados']->count() }}</p></div>
        </div>
        <x-table.shell>
            <thead class="bg-surface-container-high text-xs font-bold uppercase text-on-surface-variant"><tr><th class="px-4 py-3">Ranking</th><th class="px-4 py-3">DNI</th><th class="px-4 py-3">Alumno</th><th class="px-4 py-3">Carrera</th><th class="px-4 py-3">Aula</th><th class="px-4 py-3">Promedio</th></tr></thead>
            <tbody class="divide-y divide-outline-variant/50">
                @foreach ($report['ranking_general'] as $row)
                    <tr><td class="px-4 py-3 font-bold">{{ $row->ranking }}</td><td class="px-4 py-3">{{ $row->dni }}</td><td class="px-4 py-3">{{ $row->alumno }}</td><td class="px-4 py-3">{{ $row->carrera }}</td><td class="px-4 py-3">{{ $row->aula }}</td><td class="px-4 py-3">{{ number_format($row->promedio, 2) }}</td></tr>
                @endforeach
            </tbody>
        </x-table.shell>
    @endif
@endsection
