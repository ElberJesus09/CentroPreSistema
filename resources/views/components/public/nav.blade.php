@php
    $name = request()->route()?->getName() ?? '';
    $current = match (true) {
        str_starts_with($name, 'registration') => 'registration',
        $name === 'careers' => 'careers',
        $name === 'campuses' => 'campuses',
        $name === 'home' => 'home',
        default => null,
    };
    $linkBase = 'text-sm font-semibold transition-colors duration-200';
    $linkIdle = 'text-on-surface-variant hover:text-secondary';
    $linkActive = 'border-b-2 border-secondary pb-1 text-primary';
@endphp

<header
    class="sticky top-0 z-50 border-b border-outline-variant bg-surface/95 shadow-sm backdrop-blur"
    role="banner"
>
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-margin-mobile py-4 lg:px-margin-desktop">
        <a href="{{ route('home') }}" class="group flex items-baseline gap-2">
            <span class="font-display text-lg font-bold text-primary">{{ config('app.name') }}</span>
            <span class="hidden text-xs text-on-surface-variant sm:inline">Admisión UNPRG</span>
        </a>

        <nav class="hidden flex-wrap items-center gap-6 md:flex" aria-label="Principal">
            <a href="{{ route('home') }}" class="{{ $linkBase }} {{ $current === 'home' ? $linkActive : $linkIdle }}">
                Inicio
            </a>
            <a href="{{ route('registration.start') }}" class="{{ $linkBase }} {{ $current === 'registration' ? $linkActive : $linkIdle }}">
                Inscripción
            </a>
            <a href="{{ route('careers') }}" class="{{ $linkBase }} {{ $current === 'careers' ? $linkActive : $linkIdle }}">
                Carreras
            </a>
            <a href="{{ route('campuses') }}" class="{{ $linkBase }} {{ $current === 'campuses' ? $linkActive : $linkIdle }}">
                Sedes
            </a>
        </nav>

        <div class="flex items-center gap-3">
            <a
                href="{{ route('registration.start') }}"
                class="hidden rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-on-primary shadow-sm transition active:scale-[0.98] hover:bg-primary-container md:inline-flex"
            >
                Pre-Inscríbete
            </a>

            <details class="relative md:hidden">
                <summary
                    class="flex cursor-pointer list-none items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest p-2 text-primary [&::-webkit-details-marker]:hidden"
                    aria-label="Abrir menú"
                >
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </summary>
                <div
                    class="absolute right-0 z-50 mt-2 min-w-[12rem] rounded-xl border border-outline-variant bg-surface-container-lowest py-2 shadow-lg"
                >
                    <a href="{{ route('home') }}" class="block px-4 py-2.5 text-sm font-medium text-on-surface hover:bg-surface-container-high">Inicio</a>
                    <a href="{{ route('registration.start') }}" class="block px-4 py-2.5 text-sm font-medium text-on-surface hover:bg-surface-container-high">Inscripción</a>
                    <a href="{{ route('careers') }}" class="block px-4 py-2.5 text-sm font-medium text-on-surface hover:bg-surface-container-high">Carreras</a>
                    <a href="{{ route('campuses') }}" class="block px-4 py-2.5 text-sm font-medium text-on-surface hover:bg-surface-container-high">Sedes</a>
                    <a
                        href="{{ route('registration.start') }}"
                        class="mx-2 mt-2 block rounded-lg bg-primary py-2 text-center text-sm font-semibold text-on-primary"
                    >
                        Pre-Inscríbete
                    </a>
                </div>
            </details>
        </div>
    </div>
</header>
