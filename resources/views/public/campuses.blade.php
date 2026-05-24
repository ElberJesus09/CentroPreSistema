@extends('layouts.portal')

@section('title', 'Sedes | '.config('app.name'))

@section('content')
    <section class="grid gap-8 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full bg-primary-fixed px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-primary">
                <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1">account_balance</span>
                Sedes institucionales
            </span>
            <h1 class="mt-5 font-display text-4xl font-bold leading-tight text-primary md:text-5xl">
                Ubica tu sede y continúa tu inscripción
            </h1>
            <p class="mt-4 max-w-2xl text-base leading-relaxed text-on-surface-variant md:text-lg">
                La sede principal se muestra primero porque concentra la referencia institucional, la dirección y el mapa que los postulantes consultan al iniciar.
            </p>
        </div>
        <div class="overflow-hidden rounded-xl border border-outline-variant/50 bg-surface-container-lowest shadow-xl">
            <img
                src="{{ asset('images/public/sedes-principal.png') }}"
                alt="Centro Preuniversitario Juan Francisco Aguinaga Castro"
                class="aspect-[16/10] w-full object-cover"
                loading="eager"
            />
        </div>
    </section>

    @if ($campuses->isEmpty())
        <p class="mt-10 rounded-xl border border-secondary-container/40 bg-secondary-container/15 px-5 py-4 text-sm text-on-secondary-container">
            No hay sedes registradas.
        </p>
    @else
        @php
            $first = $campuses->first();
            $rest = $campuses->slice(1);
        @endphp

        <section class="mt-10 grid gap-6 lg:grid-cols-12">
            <article class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-6 shadow-sm lg:col-span-5 lg:p-8">
                <div class="flex items-start gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-primary text-on-primary shadow-md">
                        <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1">account_balance</span>
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-secondary">Sede principal</p>
                        <h2 class="mt-1 font-display text-2xl font-bold leading-tight text-primary">{{ $first->name }}</h2>
                    </div>
                </div>

                <div class="mt-8 space-y-4">
                    <div class="flex gap-3 rounded-xl border border-outline-variant/50 bg-surface-container-low p-4">
                        <span class="material-symbols-outlined mt-0.5 text-primary">location_on</span>
                        <div>
                            <p class="text-sm font-bold text-primary">Dirección</p>
                            <p class="mt-1 text-sm leading-relaxed text-on-surface-variant">
                                {{ $first->address ?: 'Av. José Leonardo Ortiz 405 , Chiclayo, Peru' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-3 rounded-xl border border-outline-variant/50 bg-surface-container-low p-4">
                        <span class="material-symbols-outlined mt-0.5 text-primary">schedule</span>
                        <div>
                            <p class="text-sm font-bold text-primary">Horario de atención</p>
                            <p class="mt-1 text-sm leading-relaxed text-on-surface-variant">Lun - Vie según calendario institucional</p>
                        </div>
                    </div>
                </div>

                <a
                    href="{{ route('registration.start') }}"
                    class="mt-8 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-bold text-on-primary shadow-md transition hover:bg-primary-container active:scale-[0.98]"
                >
                    <span class="material-symbols-outlined text-lg">edit_document</span>
                    Inscribirme en línea
                </a>
            </article>

            <div class="overflow-hidden rounded-xl border border-outline-variant/50 bg-surface-container-lowest shadow-xl lg:col-span-7">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d832.8989196118165!2d-79.84621234365474!3d-6.7750537240223245!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x904cef26c7cf7125%3A0xb25f96c9c4a3c9d4!2sCentro%20Preuniversitario%20%22Francisco%20Aguinaga%20Castro%22!5e0!3m2!1ses!2spe!4v1778890974801!5m2!1ses!2spe"
                    width="900"
                    height="560"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    class="h-[28rem] w-full lg:h-full"
                    title="Mapa del Centro Preuniversitario Francisco Aguinaga Castro"
                ></iframe>
            </div>
        </section>

        @if ($rest->isNotEmpty())
            <section class="mt-12">
                <div class="mb-5 flex flex-col justify-between gap-3 md:flex-row md:items-end">
                    <div>
                        <h2 class="font-display text-2xl font-bold text-primary">Otras sedes activas</h2>
                        <p class="mt-1 text-sm text-on-surface-variant">Disponibles según programación de ciclos y turnos.</p>
                    </div>
                    <span class="text-sm font-bold text-secondary">{{ $rest->count() }} sede(s)</span>
                </div>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($rest as $campus)
                        <article class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm transition hover:-translate-y-1 hover:border-primary/50 hover:shadow-lg">
                            <div class="flex items-start gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-primary-fixed text-primary">
                                    <span class="material-symbols-outlined">school</span>
                                </span>
                                <div>
                                    <h3 class="font-display text-lg font-bold text-primary">{{ $campus->name }}</h3>
                                    <p class="mt-2 text-sm leading-relaxed text-on-surface-variant">
                                        {{ $campus->address ?: 'Ubicación disponible en administración.' }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="mt-12 rounded-xl bg-primary p-6 text-on-primary md:p-8">
            <div class="grid gap-6 md:grid-cols-[1fr_auto] md:items-center">
                <div>
                    <h2 class="font-display text-2xl font-bold">Atención virtual disponible</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-primary-fixed/90">
                        Completa tu ficha en línea y el sistema validará la combinación de ciclo, sede y turno con cupos disponibles.
                    </p>
                </div>
                <a
                    href="{{ route('registration.start') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-secondary-container px-5 py-3 text-sm font-bold text-on-secondary-container transition hover:bg-secondary-fixed active:scale-[0.98]"
                >
                    <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    Ir a inscripción
                </a>
            </div>
        </section>
    @endif
@endsection
