@extends('layouts.portal')

@section('title', 'Carreras | '.config('app.name'))

@section('content')
    <section class="grid gap-8 rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-6 shadow-sm md:p-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-end">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full bg-secondary-container px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-on-secondary-container">
                <span class="material-symbols-outlined text-base">workspace_premium</span>
                Catálogo UNPRG
            </span>
            <h1 class="mt-5 font-display text-4xl font-bold leading-tight text-primary md:text-5xl">
                Carreras para elegir con claridad
            </h1>
            <p class="mt-4 max-w-2xl text-base leading-relaxed text-on-surface-variant md:text-lg">
                Explora las opciones con mayor demanda y luego revisa el catálogo completo. Al inscribirte podrás seleccionar carrera, ciclo, sede y turno disponible.
            </p>
        </div>
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl bg-primary p-5 text-on-primary">
                <span class="material-symbols-outlined text-secondary-fixed">school</span>
                <p class="mt-6 font-display text-3xl font-bold">{{ $featuredCareers->count() }}</p>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary-fixed/85">Destacadas</p>
            </div>
            <div class="rounded-xl bg-secondary-container p-5 text-on-secondary-container">
                <span class="material-symbols-outlined">format_list_bulleted</span>
                <p class="mt-6 font-display text-3xl font-bold">{{ $otherCareers->count() }}</p>
                <p class="text-xs font-semibold uppercase tracking-wide">Adicionales</p>
            </div>
            <a href="{{ route('registration.start') }}" class="group rounded-xl border border-outline-variant/70 bg-surface-container-low p-5 text-primary transition hover:border-primary hover:bg-primary-fixed">
                <span class="material-symbols-outlined transition group-hover:translate-x-1">arrow_forward</span>
                <p class="mt-6 text-sm font-bold">Comenzar inscripción</p>
            </a>
        </div>
    </section>

    <section class="mt-10 grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($featuredCareers as $index => $career)
            @php
                $careerImages = [
                    'MED' => 'medicina-humana.png',
                    'ICV' => 'ingenieria-civil.png',
                    'ISI' => 'ingenieria-sistemas.png',
                    'ICI' => 'ingenieria-computacion.png',
                    'ARC' => 'arquitectura.png',
                    'MVE' => 'medicina-veterinaria.png',
                    'DER' => 'derecho.png',
                    'ENF' => 'enfermeria.png',
                    'PSI' => 'psicologia.png',
                    'IAG' => 'ingenieria-agricola.png',
                ];

                $careerDescriptions = [
                    'MED' => 'Refuerza biología, química y razonamiento científico para postular a una carrera enfocada en salud.',
                    'ICV' => 'Prepárate en matemática, física y análisis espacial para diseñar infraestructura al servicio de la comunidad.',
                    'ISI' => 'Fortalece lógica, algoritmos y pensamiento analítico para crear sistemas que resuelvan problemas reales.',
                    'ICI' => 'Desarrolla bases en computación, programación y redes para construir soluciones digitales seguras.',
                    'ARC' => 'Potencia creatividad, geometría y visión espacial para proyectar espacios funcionales y humanos.',
                    'MVE' => 'Afianza ciencias naturales y vocación de servicio para cuidar la salud animal y el bienestar productivo.',
                    'DER' => 'Entrena comprensión lectora, argumentación y análisis social para formarte en justicia pública.',
                    'ENF' => 'Consolida biología, comunicación y criterio humano para una profesión centrada en el cuidado.',
                    'PSI' => 'Refuerza lectura crítica, biología y ciencias sociales para comprender la conducta humana.',
                    'IAG' => 'Prepárate en matemática, física y ciencias agrarias para optimizar agua, suelos y tecnología.',
                ];

                $careerImage = $careerImages[$career->code] ?? null;
                $careerDescription = $careerDescriptions[$career->code] ?? 'Prepárate con contenidos clave para postular con más seguridad a esta carrera de la UNPRG.';
            @endphp
            <article
                class="group overflow-hidden rounded-xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm transition duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-xl"
            >
                <div class="relative aspect-[16/10] overflow-hidden bg-surface-variant">
                    @if ($careerImage)
                        <img
                            src="{{ asset('images/careers/'.$careerImage) }}"
                            alt="{{ $career->name }}"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            loading="lazy"
                        />
                    @else
                        <div class="absolute inset-0 flex items-center justify-center bg-primary-fixed">
                            <span class="material-symbols-outlined text-6xl text-primary">school</span>
                        </div>
                    @endif
                </div>
                <div class="flex min-h-52 flex-col p-6">
                    <h2 class="font-display text-xl font-bold leading-tight text-primary">{{ $career->name }}</h2>
                    <p class="mt-3 flex-1 text-sm leading-relaxed text-on-surface-variant">
                        {{ $careerDescription }}
                    </p>
                    <div class="mt-5 flex items-center justify-between border-t border-outline-variant/50 pt-4">
                        <span class="text-xs font-bold uppercase tracking-wide text-secondary">Preparacion CPU</span>
                        <a
                            href="{{ route('registration.start') }}"
                            class="inline-flex items-center gap-1 text-sm font-bold text-primary transition group-hover:text-secondary"
                        >
                            Inscribirse
                            <span class="material-symbols-outlined text-lg transition group-hover:translate-x-1">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="mt-14 grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
        <div class="space-y-4">
            <span class="inline-flex items-center gap-2 rounded-full bg-primary-fixed px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-primary">
                <span class="material-symbols-outlined text-base">search</span>
                Catálogo completo
            </span>
            <h2 class="font-display text-3xl font-bold text-primary">Más alternativas para postular</h2>
            <p class="text-sm leading-relaxed text-on-surface-variant md:text-base">
                El Centro Preuniversitario prepara postulantes en facultades de ciencias, ingenierías, salud, humanidades y educación. La carrera final se confirma en el formulario.
            </p>
            <a
                href="{{ route('campuses') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-secondary px-5 py-3 text-sm font-bold text-secondary transition hover:bg-secondary-container active:scale-[0.98]"
            >
                <span class="material-symbols-outlined text-lg">location_on</span>
                Ver sedes
            </a>
        </div>

        @if ($otherCareers->isNotEmpty())
            <div class="max-h-[28rem] overflow-y-auto rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-3 shadow-inner">
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($otherCareers as $career)
                        <div
                            class="flex min-w-0 items-center gap-3 rounded-lg border border-outline-variant/40 bg-surface-container-low px-3 py-2.5"
                            title="{{ $career->name }}"
                        >
                            <span class="truncate text-sm font-semibold text-on-surface">{{ $career->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection
