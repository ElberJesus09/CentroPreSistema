@extends('layouts.app')

@section('title', 'Editar alumno | '.config('app.name'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('students.index') }}" class="text-sm font-medium text-brand hover:underline">Volver al listado</a>
        <h1 class="mt-2 text-xl font-semibold text-neutral-900">Editar alumno</h1>
        <p class="text-sm text-neutral-600">DNI {{ $student->dni }} — al cambiar de turno se validan cupos automaticamente.</p>
    </div>

    @include('students.partials.registration-form', [
        'action' => route('students.update', $student),
        'method' => 'put',
        'student' => $student,
        'schedules' => $schedules,
        'careers' => $careers,
        'showStatusField' => true,
        'submitLabel' => 'Actualizar alumno',
        'cancelUrl' => route('students.index'),
    ])
@endsection
