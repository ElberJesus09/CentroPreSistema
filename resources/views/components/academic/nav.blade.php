@props(['section' => 'schedules'])

@php
    $base = 'rounded-lg px-3 py-2 text-sm font-semibold transition-colors';
    $active = $base.' border border-primary bg-primary-fixed text-primary';
    $idle = $base.' border border-transparent text-on-surface-variant hover:bg-surface-container-high';
@endphp

<nav class="mb-6 flex flex-wrap gap-2 border-b border-outline-variant pb-4" aria-label="Ciclos académicos">
    <a href="{{ route('academic-cycles.index') }}" class="{{ $section === 'schedules' ? $active : $idle }}">
        Programación
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
