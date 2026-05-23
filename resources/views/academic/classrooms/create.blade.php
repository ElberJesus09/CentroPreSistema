@extends('layouts.app')

@section('title', 'Nueva aula | '.config('app.name'))

@section('content')
    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-primary">Nueva aula</h1>
        <p class="text-sm text-on-surface-variant">Registra un aula académica y su prioridad de distribución.</p>
    </div>
    <form method="post" action="{{ route('academic.classrooms.store') }}" class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm">
        @include('academic.classrooms._form')
    </form>
@endsection
