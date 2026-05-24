<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.cpu-head')
</head>
<body class="min-h-screen bg-surface font-sans text-on-surface antialiased">
    <x-public.nav />
    <main class="mx-auto w-full max-w-7xl px-margin-mobile py-6 lg:px-margin-desktop lg:py-10">
        <x-alert />
        @yield('content')
    </main>
    <footer class="mt-8 border-t border-outline-variant bg-primary py-12 text-on-primary">
        <div class="mx-auto flex max-w-7xl flex-col gap-8 px-margin-mobile lg:flex-row lg:justify-between lg:px-margin-desktop">
            <div class="max-w-sm">
                <p class="font-display text-lg font-bold text-on-primary">{{ config('app.name') }}</p>
                <p class="mt-2 text-sm leading-relaxed text-primary-fixed/90">
                    Centro Preuniversitario Juan Francisco Aguinaga Castro — preparación hacia la UNPRG.
                </p>
            </div>
            <div class="flex flex-wrap gap-8 text-sm">
                <div>
                    <p class="mb-2 font-semibold text-secondary-fixed">Institucional</p>
                    <a href="{{ route('careers') }}" class="block text-primary-fixed/85 hover:text-secondary-fixed-dim">Carreras</a>
                    <a href="{{ route('campuses') }}" class="mt-1 block text-primary-fixed/85 hover:text-secondary-fixed-dim">Sedes</a>
                </div>
                <div>
                    <p class="mb-2 font-semibold text-secondary-fixed">Postulación</p>
                    <a href="{{ route('registration.start') }}" class="block text-primary-fixed/85 hover:text-secondary-fixed-dim">Inscripción en línea</a>
                    <a href="{{ route('home') }}" class="mt-1 block text-primary-fixed/85 hover:text-secondary-fixed-dim">Inicio</a>
                </div>
            </div>
        </div>
        <p class="mx-auto mt-10 max-w-7xl border-t border-on-primary-fixed-variant/30 px-margin-mobile pt-6 text-center text-xs text-primary-fixed/70 lg:px-margin-desktop lg:text-left">
            © {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
        </p>
    </footer>
</body>
</html>
