@extends('layouts.app')

@section('title', 'Editar aula | '.config('app.name'))

@section('content')
    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-primary">Editar aula</h1>
        <p class="text-sm text-on-surface-variant">Actualiza capacidad, estado y prioridad académica.</p>
    </div>
    <form method="post" action="{{ route('academic.classrooms.update', $classroom) }}" class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm">
        @method('PUT')
        @include('academic.classrooms._form')
    </form>
@endsection
