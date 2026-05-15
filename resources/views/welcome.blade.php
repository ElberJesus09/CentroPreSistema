<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CPU-UNPRG') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface font-sans text-on-surface antialiased">
    <main class="mx-auto flex min-h-screen max-w-4xl flex-col items-center justify-center px-6 text-center">
        <p class="text-sm font-semibold uppercase tracking-wide text-secondary">Centro Preuniversitario</p>
        <h1 class="mt-3 font-display text-4xl font-bold text-primary md:text-5xl">CPU-UNPRG</h1>
        <p class="mt-4 max-w-2xl text-base text-on-surface-variant">
            Plataforma institucional para inscripción, gestión académica y administración de postulantes.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a
                href="{{ route('home') }}"
                class="inline-flex items-center justify-center rounded-lg border border-transparent bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary shadow-sm hover:bg-primary-container"
            >
                Ir al portal
            </a>
            <a
                href="{{ route('login') }}"
                class="inline-flex items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest px-5 py-2.5 text-sm font-semibold text-primary hover:bg-surface-container-high"
            >
                Panel administrativo
            </a>
        </div>
    </main>
</body>
</html>
