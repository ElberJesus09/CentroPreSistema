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
@endsection
