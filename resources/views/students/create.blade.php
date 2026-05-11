@extends('layouts.app')

@section('title', 'Registrar alumno | '.config('app.name'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('students.index') }}" class="text-sm font-medium text-brand hover:underline">Volver al listado</a>
        <h1 class="mt-2 text-xl font-semibold text-neutral-900">Registrar alumno</h1>
        <p class="text-sm text-neutral-600">Mismo formulario que la postulacion publica; puede fijar estado del expediente.</p>
    </div>

    @if ($schedules->isEmpty())
        <div class="mb-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            No hay turnos con cupos disponibles. Configure programacion en Academic Cycles antes de registrar alumnos.
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
