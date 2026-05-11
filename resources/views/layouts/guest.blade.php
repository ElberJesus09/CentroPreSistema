<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white font-sans text-neutral-900 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <div class="mb-8 text-center">
            <p class="text-sm font-semibold tracking-wide text-brand">{{ config('app.name') }}</p>
        </div>
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </div>
</body>
</html>
