@extends('layouts.app')

@section('title', 'Registrar alumno | '.config('app.name'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('students.index') }}" class="text-sm font-semibold text-primary hover:underline">Volver al listado</a>
        <h1 class="font-display mt-2 text-xl font-bold text-primary md:text-2xl">Registrar alumno</h1>
        <p class="text-sm text-on-surface-variant">Mismo formulario que la postulación pública; puede fijar el estado del expediente.</p>
    </div>

    @if ($schedules->isEmpty())
        <div class="mb-6 rounded-md border border-secondary-container/50 bg-secondary-container/15 px-4 py-3 text-sm text-on-secondary-container">
            No hay turnos con cupos disponibles. Configure la programación en <strong>Ciclos académicos</strong> antes de registrar alumnos.
        </div>
    @endif

    @if (! $schedules->isEmpty())
    @include('students.partials.registration-form', [
        'action' => route('students.store'),
        'method' => 'post',
        'student' => null,
        'schedules' => $schedules,
        'careers' => $careers,
        'showStatusField' => true,
        'submitLabel' => 'Guardar alumno',
        'cancelUrl' => route('students.index'),
    ])
    @endif
@endsection
