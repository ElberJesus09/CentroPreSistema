<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.cpu-head')
</head>
<body class="min-h-screen bg-surface font-sans text-on-surface antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <div class="mb-8 text-center">
            <p class="font-display text-sm font-bold tracking-wide text-primary">{{ config('app.name') }}</p>
            <p class="mt-1 text-xs text-on-surface-variant">Acceso interno</p>
        </div>
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </div>
</body>
</html>
