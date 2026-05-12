@extends('layouts.portal')

@section('title', 'Inicio | '.config('app.name'))

@section('content')
    <div class="overflow-hidden rounded-2xl border border-neutral-200/80 bg-white shadow-sm">
        <div class="grid gap-0 lg:grid-cols-2">
            <div class="border-b border-neutral-100 p-8 lg:border-b-0 lg:border-r lg:p-12">
                <p class="text-sm font-semibold uppercase tracking-wider text-brand">Bienvenido</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight text-neutral-900 lg:text-4xl">
                    Tu futuro profesional comienza aqui
                </h1>
                <p class="mt-4 text-sm leading-relaxed text-neutral-600 lg:text-base">
                    Postulate en linea de forma segura. Conoce nuestras carreras, sedes y turnos con vacantes disponibles.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a
                        href="{{ route('registration.start') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand/90"
                    >
                        Iniciar inscripcion
                    </a>
                    <a
                        href="{{ route('careers') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-neutral-300 bg-white px-5 py-2.5 text-sm font-semibold text-neutral-800 transition hover:bg-neutral-50"
                    >
                        Ver carreras
                    </a>
                </div>
            </div>
            <div class="grid gap-4 p-8 lg:p-12">
                <div class="rounded-xl border border-neutral-100 bg-neutral-50/80 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Resumen</p>
                    <dl class="mt-4 grid grid-cols-3 gap-4 text-center">
                        <div>
                            <dt class="text-2xl font-bold text-brand">{{ $careerCount }}</dt>
                            <dd class="text-xs text-neutral-600">Carreras</dd>
                        </div>
                        <div>
                            <dt class="text-2xl font-bold text-brand">{{ $campusCount }}</dt>
                            <dd class="text-xs text-neutral-600">Sedes</dd>
                        </div>
                        <div>
                            <dt class="text-2xl font-bold text-brand">{{ $openSlots }}</dt>
                            <dd class="text-xs text-neutral-600">Turnos con cupo</dd>
                        </div>
                    </dl>
                </div>
                <ul class="space-y-3 text-sm text-neutral-600">
                    <li class="flex gap-2">
                        <span class="mt-0.5 text-green-600" aria-hidden="true">&#10003;</span>
                        <span>Formulario guiado paso a paso, sin saturar la pantalla.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="mt-0.5 text-green-600" aria-hidden="true">&#10003;</span>
                        <span>Cupos validados al momento de confirmar su postulacion.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="mt-0.5 text-green-600" aria-hidden="true">&#10003;</span>
                        <span>Proceso optimizado para dispositivos moviles.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection
