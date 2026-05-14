@extends('layouts.portal')

@section('title', 'Inicio | '.config('app.name'))

@section('content')
    <section class="overflow-hidden rounded-xxl border border-outline-variant/40 bg-surface-container-lowest shadow-sm">
        <div class="grid gap-0 lg:grid-cols-2 lg:items-center">
            <div class="z-10 space-y-6 p-8 lg:p-12 xl:p-16">
                <h1 class="font-display text-3xl font-bold leading-tight tracking-tight text-primary md:text-4xl lg:text-5xl">
                    Tu futuro profesional comienza aquí
                </h1>
                <p class="max-w-xl text-base leading-relaxed text-on-surface-variant md:text-lg">
                    Postúlate en línea de forma segura. Conoce nuestras carreras, sedes y turnos con vacantes disponibles para asegurar tu ingreso a la Universidad Nacional Pedro Ruiz Gallo.
                </p>
                <div class="flex flex-wrap gap-3 pt-2">
                    <a
                        href="{{ route('registration.start') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-on-primary shadow-md transition active:scale-[0.98] hover:bg-primary-container"
                    >
                        Iniciar inscripción
                    </a>
                    <a
                        href="{{ route('careers') }}"
                        class="inline-flex items-center justify-center rounded-xl border-2 border-secondary bg-transparent px-6 py-3 text-sm font-semibold text-secondary transition hover:bg-secondary-container active:scale-[0.98]"
                    >
                        Ver carreras
                    </a>
                </div>
            </div>
            <div class="relative p-6 pb-12 lg:p-10 lg:pb-16">
                <div class="relative aspect-square w-full overflow-hidden rounded-xxl shadow-xl md:aspect-[4/5]">
                    <img
                        src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&q=80"
                        alt="Estudiantes en un campus universitario"
                        class="h-full w-full object-cover"
                        width="800"
                        height="1000"
                        loading="eager"
                    />
                </div>
                <div
                    class="absolute bottom-4 left-4 flex max-w-[calc(100%-2rem)] items-center gap-3 rounded-xl border border-outline-variant bg-surface-container-highest p-4 shadow-lg md:bottom-8 md:left-8"
                >
                    <div class="rounded-lg bg-primary-container p-2">
                        <span class="material-symbols-outlined text-on-primary-container" style="font-variation-settings: 'FILL' 1, 'wght' 400">school</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-primary">Admisión {{ date('Y') }}</p>
                        <p class="text-xs text-on-surface-variant">Vacantes según turnos publicados</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-12 rounded-xxl border border-outline-variant/50 bg-surface-container-low py-12 px-6 md:px-10">
        <div class="mx-auto grid max-w-6xl gap-6 md:grid-cols-3">
            <div
                class="flex flex-col items-center rounded-xxl border border-outline-variant bg-surface-container-lowest p-8 text-center shadow-sm transition hover:shadow-md"
            >
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary-fixed">
                    <span class="material-symbols-outlined text-3xl text-primary">account_balance</span>
                </div>
                <h2 class="font-display text-xl font-semibold text-primary">{{ $campusCount }} sedes</h2>
                <p class="mt-2 text-sm leading-relaxed text-on-surface-variant">
                    Infraestructura para tu preparación; consulta ubicaciones en el apartado Sedes.
                </p>
            </div>
            <div
                class="flex flex-col items-center rounded-xxl border border-outline-variant bg-surface-container-lowest p-8 text-center shadow-sm transition hover:shadow-md"
            >
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-secondary-container">
                    <span class="material-symbols-outlined text-3xl text-secondary">workspace_premium</span>
                </div>
                <h2 class="font-display text-xl font-semibold text-primary">{{ $careerCount }} carreras</h2>
                <p class="mt-2 text-sm leading-relaxed text-on-surface-variant">
                    Programas activos vinculados al proceso de admisión vigente.
                </p>
            </div>
            <div
                class="flex flex-col items-center rounded-xxl border border-outline-variant bg-surface-container-lowest p-8 text-center shadow-sm transition hover:shadow-md"
            >
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-tertiary-fixed">
                    <span class="material-symbols-outlined text-3xl text-on-tertiary-fixed-variant">trending_up</span>
                </div>
                <h2 class="font-display text-xl font-semibold text-primary">{{ $openSlots }} turnos con cupo</h2>
                <p class="mt-2 text-sm leading-relaxed text-on-surface-variant">
                    Elige ciclo, sede y turno al finalizar tu ficha en línea.
                </p>
            </div>
        </div>
    </section>

    <section class="mt-12 grid gap-10 lg:grid-cols-2 lg:items-center">
        <div class="order-2 grid grid-cols-2 gap-4 lg:order-1">
            <img
                src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=600&q=80"
                alt="Estudiantes en biblioteca"
                class="aspect-square w-full rounded-xl object-cover shadow-md"
                loading="lazy"
            />
            <img
                src="https://images.unsplash.com/photo-1541339907198-e08756dedf13?auto=format&fit=crop&w=600&q=80"
                alt="Campus universitario"
                class="mt-8 aspect-[3/4] w-full rounded-xl object-cover shadow-md"
                loading="lazy"
            />
        </div>
        <div class="order-1 space-y-6 lg:order-2">
            <h2 class="font-display text-2xl font-semibold text-primary md:text-3xl">Prepara tu camino al éxito con los mejores</h2>
            <ul class="space-y-5">
                <li class="flex gap-3">
                    <span class="material-symbols-outlined mt-0.5 shrink-0 text-primary">check_circle</span>
                    <div>
                        <h3 class="text-sm font-bold text-primary">Plana docente especializada</h3>
                        <p class="mt-1 text-sm text-on-surface-variant">Acompañamiento orientado al examen de admisión UNPRG.</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="material-symbols-outlined mt-0.5 shrink-0 text-primary">check_circle</span>
                    <div>
                        <h3 class="text-sm font-bold text-primary">Inscripción clara y segura</h3>
                        <p class="mt-1 text-sm text-on-surface-variant">Formulario guiado por pasos, con validación de cupos al confirmar.</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="material-symbols-outlined mt-0.5 shrink-0 text-primary">check_circle</span>
                    <div>
                        <h3 class="text-sm font-bold text-primary">Información institucional</h3>
                        <p class="mt-1 text-sm text-on-surface-variant">Carreras, sedes y datos del examen de ingreso centralizados.</p>
                    </div>
                </li>
            </ul>
        </div>
    </section>
@endsection
