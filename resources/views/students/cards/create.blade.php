@extends('layouts.app')

@section('title', 'Carnets de estudiantes | '.config('app.name'))

@section('content')
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="font-display text-2xl font-bold tracking-tight text-primary md:text-3xl">Carnets de estudiantes</h1>
            <p class="mt-1 text-sm text-on-surface-variant">
                Genere carnets PDF por ciclo, carrera y turno, o por un alumno específico.
            </p>
        </div>
        <a
            href="{{ route('students.index') }}"
            class="inline-flex items-center justify-center rounded-lg border border-outline-variant px-4 py-2 text-sm font-semibold text-primary hover:bg-surface-container-high"
        >
            Volver a alumnos
        </a>
    </div>

    @error('filters')
        <div class="mb-5 rounded-lg border border-error/30 bg-error-container/50 px-4 py-3 text-sm text-error">
            {{ $message }}
        </div>
    @enderror

    <section class="max-w-5xl rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-6 shadow-sm">
        <div class="mb-6 flex items-start gap-3 border-b border-outline-variant/50 pb-4">
            <div class="rounded-lg bg-primary-fixed p-2">
                <span class="material-symbols-outlined text-primary">badge</span>
            </div>
            <div>
                <h2 class="font-display text-xl font-semibold text-primary">Generar PDF</h2>
                <p class="mt-1 text-sm text-on-surface-variant">
                    Si escribe ID o DNI de un alumno, se genera solo ese carnet y se ignoran los filtros.
                </p>
            </div>
        </div>

        <form method="get" action="{{ route('students.cards.download') }}" class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-3">
                <label for="student-card-specific" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Alumno específico</label>
                <input
                    id="student-card-specific"
                    name="student"
                    type="search"
                    value="{{ $filters['student'] ?? '' }}"
                    placeholder="ID o DNI del alumno"
                    class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
            </div>

            <div>
                <label for="student-card-cycle" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Ciclo</label>
                <select
                    id="student-card-cycle"
                    name="academic_cycle_id"
                    class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                    <option value="">Todos los ciclos</option>
                    @foreach ($cycles as $cycle)
                        <option value="{{ $cycle->id }}" @selected((int) ($filters['academic_cycle_id'] ?? 0) === (int) $cycle->id)>
                            {{ $cycle->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="student-card-career" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Carrera</label>
                <select
                    id="student-card-career"
                    name="career_id"
                    class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                    <option value="">Todas las carreras</option>
                    @foreach ($careers as $career)
                        <option value="{{ $career->id }}" @selected((int) ($filters['career_id'] ?? 0) === (int) $career->id)>
                            {{ $career->name }} ({{ $career->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="student-card-shift" class="mb-1 block text-xs font-bold uppercase tracking-wide text-on-surface-variant">Turno</label>
                <select
                    id="student-card-shift"
                    name="shift_id"
                    class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                    <option value="">Todos los turnos</option>
                    @foreach ($shifts as $shift)
                        <option value="{{ $shift->id }}" @selected((int) ($filters['shift_id'] ?? 0) === (int) $shift->id)>
                            {{ $shift->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-outline-variant/50 pt-5 md:col-span-3">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary shadow-sm transition-colors hover:bg-primary-container"
                >
                    <span class="material-symbols-outlined text-lg">download</span>
                    Generar carnets PDF
                </button>
                <a
                    href="{{ route('students.cards.create') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface hover:bg-surface-container-high"
                >
                    Limpiar filtros
                </a>
            </div>
        </form>
    </section>
@endsection
