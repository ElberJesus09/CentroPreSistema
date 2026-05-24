@php
    $name = request()->route()?->getName() ?? '';
    $current = match (true) {
        str_starts_with($name, 'registration') => 'registration',
        $name === 'careers' => 'careers',
        $name === 'campuses' => 'campuses',
        $name === 'home' => 'home',
        default => null,
    };
    $linkBase = 'rounded-full px-3 py-2 text-sm font-semibold transition-all duration-200';
    $linkIdle = 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary';
    $linkActive = 'bg-primary-fixed text-primary shadow-sm';
@endphp

<header
    class="sticky top-0 z-50 border-b border-outline-variant/70 bg-surface/90 shadow-sm backdrop-blur-xl"
    role="banner"
>
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-margin-mobile py-3 lg:px-margin-desktop">
        <a href="{{ route('home') }}" class="group flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-on-primary shadow-sm">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1">account_balance</span>
            </span>
            <span>
                <span class="block font-display text-base font-bold leading-tight text-primary sm:text-lg">{{ config('app.name') }}</span>
                <span class="hidden text-xs font-medium text-on-surface-variant sm:block">Admisión UNPRG</span>
            </span>
        </a>

        <nav class="hidden flex-wrap items-center gap-1 rounded-full border border-outline-variant/60 bg-surface-container-lowest/80 p-1 shadow-sm md:flex" aria-label="Principal">
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
                class="hidden items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary shadow-md shadow-primary/20 transition active:scale-[0.98] hover:bg-primary-container md:inline-flex"
            >
                <span class="material-symbols-outlined text-lg">edit_document</span>
                Pre-Inscríbete
            </a>

            <details class="relative md:hidden">
                <summary
                    class="flex cursor-pointer list-none items-center justify-center rounded-xl border border-outline-variant bg-surface-container-lowest p-2 text-primary shadow-sm [&::-webkit-details-marker]:hidden"
                    aria-label="Abrir menú"
                >
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </summary>
                <div
                    class="absolute right-0 z-50 mt-2 min-w-[13rem] overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest py-2 shadow-xl"
                >
                    <a href="{{ route('home') }}" class="block px-4 py-2.5 text-sm font-medium text-on-surface hover:bg-surface-container-high">Inicio</a>
                    <a href="{{ route('registration.start') }}" class="block px-4 py-2.5 text-sm font-medium text-on-surface hover:bg-surface-container-high">Inscripción</a>
                    <a href="{{ route('careers') }}" class="block px-4 py-2.5 text-sm font-medium text-on-surface hover:bg-surface-container-high">Carreras</a>
                    <a href="{{ route('campuses') }}" class="block px-4 py-2.5 text-sm font-medium text-on-surface hover:bg-surface-container-high">Sedes</a>
                    <a
                        href="{{ route('registration.start') }}"
                        class="mx-2 mt-2 flex items-center justify-center gap-2 rounded-lg bg-primary py-2 text-center text-sm font-semibold text-on-primary"
                    >
                        <span class="material-symbols-outlined text-lg">edit_document</span>
                        Pre-Inscríbete
                    </a>
                </div>
            </details>
        </div>
    </div>
</header>
