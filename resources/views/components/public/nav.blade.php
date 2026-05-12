@php
    $name = request()->route()?->getName() ?? '';
    $current = match (true) {
        str_starts_with($name, 'registration') => 'registration',
        $name === 'careers' => 'careers',
        $name === 'campuses' => 'campuses',
        $name === 'home' => 'home',
        default => null,
    };
@endphp

<header class="sticky top-0 z-40 border-b border-neutral-200/90 bg-white/95 shadow-sm backdrop-blur">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-4 lg:px-6">
        <a href="{{ route('home') }}" class="group flex items-baseline gap-2">
            <span class="text-sm font-bold tracking-tight text-brand">{{ config('app.name') }}</span>
            <span class="hidden text-xs text-neutral-500 sm:inline">Admision</span>
        </a>
        <nav class="flex flex-wrap items-center gap-1 text-sm font-medium text-neutral-700" aria-label="Principal">
            <a
                href="{{ route('home') }}"
                @class([
                    'rounded-md px-3 py-2 transition-colors',
                    'bg-brand/10 text-brand' => $current === 'home',
                    'hover:bg-neutral-100' => $current !== 'home',
                ])
            >
                Inicio
            </a>
            <a
                href="{{ route('registration.start') }}"
                @class([
                    'rounded-md px-3 py-2 transition-colors',
                    'bg-brand/10 text-brand' => $current === 'registration',
                    'hover:bg-neutral-100' => $current !== 'registration',
                ])
            >
                Inscripcion
            </a>
            <a
                href="{{ route('careers') }}"
                @class([
                    'rounded-md px-3 py-2 transition-colors',
                    'bg-brand/10 text-brand' => $current === 'careers',
                    'hover:bg-neutral-100' => $current !== 'careers',
                ])
            >
                Carreras
            </a>
            <a
                href="{{ route('campuses') }}"
                @class([
                    'rounded-md px-3 py-2 transition-colors',
                    'bg-brand/10 text-brand' => $current === 'campuses',
                    'hover:bg-neutral-100' => $current !== 'campuses',
                ])
            >
                Sedes
            </a>
        </nav>
    </div>
</header>
