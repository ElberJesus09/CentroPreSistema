@extends('layouts.portal')

@section('title', 'Carreras | '.config('app.name'))

@section('content')
    <div class="mb-10 text-center md:text-left">
        <h1 class="font-display text-3xl font-bold text-primary md:text-4xl lg:text-5xl">Carreras destacadas</h1>
        <p class="mx-auto mt-3 max-w-2xl text-base text-on-surface-variant md:mx-0 md:text-lg">
            Programas de la UNPRG con mayor demanda en nuestro centro. Explora el catálogo completo más abajo.
        </p>
    </div>

    {{-- Carreras premium (grid atractivo) --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
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
                    'MED' => 'Refuerza biologia, quimica y razonamiento cientifico para postular a una carrera enfocada en el cuidado integral de la salud.',
                    'ICV' => 'Preparate en matematica, fisica y analisis espacial para disenar soluciones de infraestructura al servicio de la comunidad.',
                    'ISI' => 'Fortalece logica, algoritmos y pensamiento analitico para crear sistemas que resuelvan problemas reales con tecnologia.',
                    'ICI' => 'Desarrolla bases en computacion, programacion y redes para construir soluciones digitales seguras y eficientes.',
                    'ARC' => 'Potencia tu creatividad, geometria y vision espacial para proyectar espacios funcionales, sostenibles y humanos.',
                    'MVE' => 'Afianza ciencias naturales y vocacion de servicio para cuidar la salud animal y aportar al bienestar productivo y social.',
                    'DER' => 'Entrena comprension lectora, argumentacion y analisis social para formarte en defensa juridica y justicia publica.',
                    'ENF' => 'Consolida biologia, comunicacion y criterio humano para una profesion centrada en el cuidado directo de las personas.',
                    'PSI' => 'Refuerza lectura critica, biologia y ciencias sociales para comprender la conducta y acompanar procesos de cambio.',
                    'IAG' => 'Preparate en matematica, fisica y ciencias agrarias para optimizar el uso del agua, suelos y tecnologia en el campo.',
                ];

                $careerImage = $careerImages[$career->code] ?? null;
                $careerDescription = $careerDescriptions[$career->code] ?? 'Preparate con contenidos clave para postular con mas seguridad a esta carrera de la UNPRG.';
            @endphp
            <article
                class="group relative flex flex-col overflow-hidden rounded-2xl border border-outline-variant/30 bg-surface-container-lowest shadow-[0_8px_24px_rgba(0,0,0,0.08)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_12px_32px_rgba(0,0,0,0.12)]"
            >
                <div class="relative h-40 overflow-hidden bg-surface-variant">
                    @if ($careerImage)
                        <img
                            src="{{ asset('images/careers/'.$careerImage) }}"
                            alt="{{ $career->name }}"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            loading="lazy"
                        />
                    @else
                        <div class="absolute inset-0 flex items-center justify-center bg-primary-container opacity-90">
                            <span class="material-symbols-outlined text-6xl text-primary">school</span>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-primary/45 via-transparent to-transparent"></div>
                    <div class="absolute left-4 top-4 rounded-full bg-secondary-container px-3 py-1 text-xs font-bold text-on-secondary-container shadow-sm">
                        {{ $career->code }}
                    </div>
                </div>
                <div class="flex flex-1 flex-col p-6">
                    <h2 class="font-display text-xl font-bold text-primary">{{ $career->name }}</h2>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-on-surface-variant">
                        {{ $careerDescription }}
                    </p>
                    <a
                        href="{{ route('registration.start') }}"
                        class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-primary transition group-hover:gap-3 group-hover:text-secondary"
                    >
                        Inscribirse
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </a>
                </div>
            </article>
        @endforeach
    </div>

    {{-- Resto del catálogo --}}
    <section class="mt-16 rounded-2xl border border-outline-variant/40 bg-surface-container-low px-6 py-10 md:px-10 md:py-12">
        <div class="mx-auto max-w-3xl text-center">
            <h2 class="font-display text-2xl font-bold text-primary md:text-3xl">Más de {{ $otherCareers->count() }} carreras adicionales</h2>
            <p class="mt-3 text-sm leading-relaxed text-on-surface-variant md:text-base">
                El Centro Preuniversitario Juan Francisco Aguinaga Castro prepara postulantes en todas las facultades de la UNPRG:
                ciencias, ingenierías, salud, humanidades, educación y más. Elige la tuya en el formulario de inscripción.
            </p>
        </div>

        @if ($otherCareers->isNotEmpty())
            <div
                class="mx-auto mt-8 flex max-h-72 flex-wrap justify-center gap-2 overflow-y-auto rounded-xl border border-outline-variant/30 bg-surface-container-lowest/80 p-4 md:max-h-96"
            >
                @foreach ($otherCareers as $career)
                    <span
                        class="inline-flex items-center rounded-full border border-outline-variant/50 bg-white px-3 py-1.5 text-xs font-semibold text-on-surface shadow-sm"
                        title="{{ $career->name }}"
                    >
                        <span class="mr-1.5 text-secondary">{{ $career->code }}</span>
                        <span class="max-w-[14rem] truncate">{{ $career->name }}</span>
                    </span>
                @endforeach
            </div>
        @endif

        <div class="mx-auto mt-10 flex flex-wrap justify-center gap-3">
            <a
                href="{{ route('registration.start') }}"
                class="inline-flex items-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-bold text-on-primary shadow-md transition hover:bg-primary-container active:scale-[0.98]"
            >
                <span class="material-symbols-outlined text-lg">edit_document</span>
                Comenzar inscripción
            </a>
            <a
                href="{{ route('campuses') }}"
                class="inline-flex items-center justify-center rounded-full border-2 border-secondary px-6 py-3 text-sm font-bold text-secondary transition hover:bg-secondary-container active:scale-[0.98]"
            >
                Ver sedes
            </a>
        </div>
    </section>
@endsection
