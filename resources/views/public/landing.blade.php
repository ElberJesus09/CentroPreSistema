@extends('layouts.portal')

@section('title', 'Inicio | '.config('app.name'))

@section('content')
    <section class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] -mt-6 w-screen overflow-hidden bg-primary text-on-primary lg:-mt-10">
        <div class="absolute inset-0">
            <img
                src="{{ asset('images/public/inicio-hero.png') }}"
                alt="Campus de la Universidad Nacional Pedro Ruiz Gallo"
                class="h-full w-full object-cover"
                width="1600"
                height="1000"
                loading="eager"
            />
            <div class="absolute inset-0 bg-primary/60"></div>
        </div>

        <div class="relative mx-auto flex min-h-[calc(100vh-7rem)] max-w-7xl flex-col justify-end px-margin-mobile pb-12 pt-24 lg:min-h-[38rem] lg:px-margin-desktop lg:pb-16">
            <div class="max-w-4xl">
                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-secondary-fixed backdrop-blur">
                    <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1">workspace_premium</span>
                    Admisión {{ date('Y') }}
                </div>
                <h1 class="font-display text-4xl font-bold leading-[1.05] text-white drop-shadow md:text-6xl lg:text-7xl">
                    Centro Preuniversitario Juan Francisco Aguinaga Castro
                </h1>
                <p class="mt-6 max-w-2xl text-base leading-relaxed text-primary-fixed md:text-xl">
                    Prepárate para postular a la Universidad Nacional Pedro Ruiz Gallo con una inscripción clara, sedes activas y carreras organizadas para elegir tu camino.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a
                        href="{{ route('registration.start') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-secondary-container px-6 py-3 text-sm font-bold text-on-secondary-container shadow-lg shadow-black/20 transition hover:bg-secondary-fixed active:scale-[0.98]"
                    >
                        <span class="material-symbols-outlined text-lg">edit_document</span>
                        Iniciar inscripción
                    </a>
                    <a
                        href="{{ route('careers') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/40 bg-white/10 px-6 py-3 text-sm font-bold text-white backdrop-blur transition hover:bg-white/20 active:scale-[0.98]"
                    >
                        <span class="material-symbols-outlined text-lg">school</span>
                        Ver carreras
                    </a>
                    @if ($publicResultsEnabled)
                        <a
                            href="{{ route('public.results') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/40 bg-white/10 px-6 py-3 text-sm font-bold text-white backdrop-blur transition hover:bg-white/20 active:scale-[0.98]"
                        >
                            <span class="material-symbols-outlined text-lg">leaderboard</span>
                            Consultar resultados
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="relative z-10 -mt-8 grid gap-4 md:grid-cols-3">
        <article class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-6 shadow-lg shadow-primary/5">
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-fixed text-primary">
                    <span class="material-symbols-outlined text-2xl">account_balance</span>
                </span>
                <div>
                    <p class="font-display text-2xl font-bold text-primary">{{ $campusCount }}</p>
                    <p class="text-sm font-semibold text-on-surface-variant">Sedes activas</p>
                </div>
            </div>
        </article>
        <article class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-6 shadow-lg shadow-primary/5">
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-secondary-container text-secondary">
                    <span class="material-symbols-outlined text-2xl">workspace_premium</span>
                </span>
                <div>
                    <p class="font-display text-2xl font-bold text-primary">{{ $careerCount }}</p>
                    <p class="text-sm font-semibold text-on-surface-variant">Carreras disponibles</p>
                </div>
            </div>
        </article>
        <article class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-6 shadow-lg shadow-primary/5">
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-tertiary-fixed text-on-tertiary-fixed-variant">
                    <span class="material-symbols-outlined text-2xl">trending_up</span>
                </span>
                <div>
                    <p class="font-display text-2xl font-bold text-primary">{{ $openSlots }}</p>
                    <p class="text-sm font-semibold text-on-surface-variant">Turnos con cupo</p>
                </div>
            </div>
        </article>
    </section>

    <section class="mt-16 grid gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
        <div class="space-y-6">
            <span class="inline-flex items-center gap-2 rounded-full bg-primary-fixed px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-primary">
                <span class="material-symbols-outlined text-base">verified</span>
                Preparación enfocada
            </span>
            <h2 class="font-display text-3xl font-bold leading-tight text-primary md:text-4xl">
                Un recorrido ordenado desde la postulación hasta la elección de turno
            </h2>
            <p class="text-base leading-relaxed text-on-surface-variant">
                El portal concentra la información pública que un postulante necesita: carreras, sedes, horarios institucionales y un formulario guiado por pasos para reducir errores.
            </p>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ([
                    ['check_circle', 'Formulario paso a paso'],
                    ['payments', 'Voucher de pago guiado'],
                    ['location_on', 'Sede principal visible'],
                    ['support_agent', 'Apoyo institucional'],
                ] as [$icon, $label])
                    <div class="flex items-center gap-3 rounded-xl border border-outline-variant/50 bg-surface-container-lowest px-4 py-3">
                        <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1">{{ $icon }}</span>
                        <span class="text-sm font-semibold text-on-surface">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4">
            <img
                src="{{ asset('images/public/inicio-centropre.png') }}"
                alt="Centro preuniversitario"
                class="col-span-7 aspect-[4/5] w-full rounded-xl object-cover shadow-xl"
                loading="lazy"
            />
            <div class="col-span-5 flex flex-col gap-4 pt-8">
                <img
                    src="{{ asset('images/public/inicio-alumnos.png') }}"
                    alt="Alumnos del centro preuniversitario"
                    class="aspect-[3/4] w-full rounded-xl object-cover shadow-lg"
                    loading="lazy"
                />
                <div class="rounded-xl bg-primary p-5 text-on-primary shadow-lg">
                    <span class="material-symbols-outlined text-secondary-fixed">school</span>
                    <p class="mt-3 text-sm font-semibold leading-relaxed">
                        Acompañamiento orientado al examen de admisión UNPRG.
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
