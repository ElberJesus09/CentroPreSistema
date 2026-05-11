<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-neutral-50 font-sans text-neutral-900 antialiased">
    <div class="mx-auto max-w-4xl px-4 py-8 lg:px-6 lg:py-10">
        <header class="mb-8 flex flex-col justify-between gap-4 border-b border-neutral-200 pb-6 sm:flex-row sm:items-center">
            <div>
                <p class="text-sm font-semibold tracking-wide text-brand">{{ config('app.name') }}</p>
                <h1 class="mt-1 text-lg font-semibold text-neutral-900">@yield('heading', 'Postulacion')</h1>
            </div>
            <a href="{{ route('login') }}" class="text-sm font-medium text-brand hover:underline">Acceso personal</a>
        </header>
        <x-alert />
        <main>
            @yield('content')
        </main>
    </div>
</body>
</html>
