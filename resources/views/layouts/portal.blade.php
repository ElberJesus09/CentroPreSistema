<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-neutral-50 to-white font-sans text-neutral-900 antialiased">
    <x-public.nav />
    <main class="mx-auto max-w-5xl px-4 py-8 lg:px-6 lg:py-12">
        <x-alert />
        @yield('content')
    </main>
    <footer class="border-t border-neutral-200/80 bg-white/80 py-8 text-center text-xs text-neutral-500">
        <p>{{ config('app.name') }} — {{ date('Y') }}</p>
    </footer>
</body>
</html>
