@extends('layouts.app')

@section('title', 'Editar alumno | '.config('app.name'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('students.index') }}" class="text-sm font-semibold text-primary hover:underline">Volver al listado</a>
        <h1 class="font-display mt-2 text-xl font-bold text-primary md:text-2xl">Editar alumno</h1>
        <p class="text-sm text-on-surface-variant">DNI {{ $student->dni }} — al cambiar de turno se validan cupos automáticamente.</p>
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
