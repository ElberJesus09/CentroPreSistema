@extends('layouts.portal')

@section('title', 'Carreras | '.config('app.name'))

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-neutral-900">Carreras disponibles</h1>
        <p class="mt-2 text-sm text-neutral-600">Programas activos para el proceso de admision.</p>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        @forelse ($careers as $career)
            <article class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm transition hover:border-brand/30 hover:shadow-md">
                <h2 class="text-lg font-semibold text-neutral-900">{{ $career->name }}</h2>
                <p class="mt-1 text-xs font-medium uppercase tracking-wide text-neutral-500">Codigo {{ $career->code }}</p>
            </article>
        @empty
            <p class="col-span-full rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                No hay carreras activas en este momento.
            </p>
        @endforelse
    </div>
@endsection
