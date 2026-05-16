@extends('layouts.portal')

@section('title', 'Sedes | '.config('app.name'))

@section('content')
    <div class="mb-10">
        <h1 class="font-display text-3xl font-bold text-primary md:text-4xl lg:text-5xl">Sedes</h1>
        <p class="mt-3 max-w-2xl text-base text-on-surface-variant md:text-lg">
            Ubicaciones institucionales activas para trámites y preparación.
        </p>
    </div>

    @if ($campuses->isEmpty())
        <p class="rounded-xl border border-secondary-container/40 bg-secondary-container/15 px-5 py-4 text-sm text-on-secondary-container">
            No hay sedes registradas.
        </p>
    @else
        @php
            $first = $campuses->first();
            $rest = $campuses->slice(1);
        @endphp

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <article
                class="group overflow-hidden rounded-xl border border-transparent bg-surface-container-lowest shadow-[0_4px_12px_rgba(0,0,0,0.05)] transition hover:border-primary lg:col-span-7"
            >
                <div class="relative aspect-video overflow-hidden">
                    <img
                        src="{{ asset('images/public/sedes-principal.png') }}"
                        alt="Campus {{ $first->name }}"
                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                        loading="lazy"
                    />
                    <div class="absolute right-4 top-4 rounded-full bg-secondary px-3 py-1 text-xs font-bold uppercase tracking-wide text-on-secondary">
                        Sede principal
                    </div>
                </div>
                <div class="p-8">
                    <div class="mb-2 flex items-center gap-2 text-primary">
                        <span class="material-symbols-outlined">account_balance</span>
                        <h2 class="font-display text-xl font-semibold">{{ $first->name }}</h2>
                    </div>
                    @if ($first->address)
                        <p class="text-sm leading-relaxed text-on-surface-variant">{{ $first->address }}</p>
                    @else
                        <p class="text-sm text-on-surface-variant">Dirección por confirmar en secretaría.</p>
                    @endif
                    <div class="mt-6 grid gap-4 border-t border-outline-variant pt-6 md:grid-cols-2">
                        <div class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-primary">location_on</span>
                            <div>
                                <p class="text-sm font-bold text-primary">Dirección</p>
                                <p class="text-xs text-on-surface-variant">{{ $first->address ?: 'Consultar en campus' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-primary">schedule</span>
                            <div>
                                <p class="text-sm font-bold text-primary">Horario de atención</p>
                                <p class="text-xs text-on-surface-variant">Lun — Vie según calendario institucional</p>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <div class="flex flex-col gap-6 lg:col-span-5">
                @foreach ($rest as $campus)
                    <article
                        class="group overflow-hidden rounded-xl border border-transparent bg-surface-container-lowest shadow-[0_4px_12px_rgba(0,0,0,0.05)] transition hover:border-primary"
                    >
                        <div class="p-6">
                            <div class="mb-2 flex items-center gap-2 text-primary">
                                <span class="material-symbols-outlined">school</span>
                                <h2 class="font-display text-lg font-semibold">{{ $campus->name }}</h2>
                            </div>
                            <p class="text-sm text-on-surface-variant">
                                {{ $campus->address ?: 'Ubicación disponible en administración.' }}
                            </p>
                        </div>
                    </article>
                @endforeach

                <div class="rounded-xl bg-primary-container p-8 text-on-primary-container">
                    <h3 class="font-display text-xl font-semibold">Atención virtual</h3>
                    <p class="mt-2 text-sm opacity-90">
                        ¿No puedes acercarte? Completa tu inscripción en línea o escríbenos por los canales oficiales.
                    </p>
                    <a
                        href="{{ route('registration.start') }}"
                        class="mt-4 inline-flex items-center gap-2 text-sm font-bold underline decoration-on-primary-container/50 underline-offset-4 hover:decoration-on-primary-container"
                    >
                        Ir a inscripción
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>

        <section class="mt-12 flex flex-col gap-8 rounded-2xl bg-surface-container-low px-6 py-10 md:flex-row md:items-center md:px-10 lg:gap-12">
            <div class="flex-1 space-y-4">
                <div class="inline-flex items-center gap-2 rounded-full bg-secondary-container px-3 py-1 text-xs font-bold text-on-secondary-container">
                    <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1">explore</span>
                    GEOLOCALIZACIÓN
                </div>
                <h2 class="font-display text-2xl font-semibold text-primary">Encuentra tu sede</h2>
                <p class="text-sm leading-relaxed text-on-surface-variant">
                    Las sedes están coordinadas con los turnos publicados en el sistema. Al inscribirte podrás elegir la combinación ciclo — sede — turno con cupos disponibles.
                </p>
                <ul class="space-y-2 text-sm text-on-surface">
                    <li class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1">check_circle</span>
                        Información de contacto en cada ficha de sede
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1">check_circle</span>
                        Proceso de postulación en español, paso a paso
                    </li>
                </ul>
            </div>
            <div class="relative aspect-square w-full flex-1 overflow-hidden rounded-2xl border-4 border-surface-container-lowest bg-surface-variant shadow-xl md:aspect-video">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d832.8989196118165!2d-79.84621234365474!3d-6.7750537240223245!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x904cef26c7cf7125%3A0xb25f96c9c4a3c9d4!2sCentro%20Preuniversitario%20%22Francisco%20Aguinaga%20Castro%22!5e0!3m2!1ses!2spe!4v1778890974801!5m2!1ses!2spe"
                    width="600"
                    height="450"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    class="h-full w-full"
                    title="Mapa del Centro Preuniversitario Francisco Aguinaga Castro"
                ></iframe>
            </div>
        </section>
    @endif
@endsection
