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
    <div class="flex min-h-screen">
        <x-sidebar :current="request()->route()?->getName()" />
        <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
            <x-navbar />
            <main class="flex-1 p-6 lg:p-8">
                <x-alert />
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
