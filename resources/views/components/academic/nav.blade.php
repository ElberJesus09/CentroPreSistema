@props(['section' => 'schedules'])

@php
    $base = 'rounded-md px-3 py-2 text-sm font-medium transition-colors';
    $active = $base.' border border-brand bg-brand/5 text-brand';
    $idle = $base.' border border-transparent text-neutral-600 hover:bg-neutral-100';
@endphp

<nav class="mb-6 flex flex-wrap gap-2 border-b border-neutral-200 pb-4" aria-label="Modulo academic cycles">
    <a href="{{ route('academic-cycles.index') }}" class="{{ $section === 'schedules' ? $active : $idle }}">
        Programacion
    </a>
    <a href="{{ route('academic-cycles.cycles.index') }}" class="{{ $section === 'cycles' ? $active : $idle }}">
        Ciclos
    </a>
    <a href="{{ route('academic-cycles.campuses.index') }}" class="{{ $section === 'campuses' ? $active : $idle }}">
        Sedes
    </a>
    <a href="{{ route('academic-cycles.shifts.index') }}" class="{{ $section === 'shifts' ? $active : $idle }}">
        Turnos
    </a>
</nav>
