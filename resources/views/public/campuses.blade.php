@extends('layouts.portal')

@section('title', 'Sedes | '.config('app.name'))

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-neutral-900">Sedes</h1>
        <p class="mt-2 text-sm text-neutral-600">Ubicaciones institucionales activas.</p>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        @forelse ($campuses as $campus)
            <article class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm transition hover:border-brand/30 hover:shadow-md">
                <h2 class="text-lg font-semibold text-neutral-900">{{ $campus->name }}</h2>
                @if ($campus->address)
                    <p class="mt-2 text-sm text-neutral-600">{{ $campus->address }}</p>
                @endif
            </article>
        @empty
            <p class="col-span-full rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                No hay sedes registradas.
            </p>
        @endforelse
    </div>
@endsection
