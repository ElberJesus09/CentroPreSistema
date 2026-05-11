@extends('layouts.public')

@section('title', 'Postulacion | '.config('app.name'))

@section('heading', 'Registro de postulante')

@section('content')
    <p class="mb-6 text-sm text-neutral-600">
        Complete el formulario. No requiere cuenta de usuario. Los cupos se confirman al enviar segun disponibilidad en tiempo real.
    </p>

    @if ($schedules->isEmpty())
        <div class="mb-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            En este momento no hay turnos con vacantes disponibles. Intente mas tarde o contacte a la institucion.
        </div>
    @endif

    @if (! $schedules->isEmpty())
        @include('students.partials.registration-form', [
            'action' => route('pre-registration.store'),
            'method' => 'post',
            'student' => null,
            'schedules' => $schedules,
            'careers' => $careers,
            'showStatusField' => false,
            'submitLabel' => 'Enviar postulacion',
        ])
    @endif
@endsection
