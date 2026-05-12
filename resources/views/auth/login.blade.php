@extends('layouts.guest')

@section('title', 'Iniciar sesion | '.config('app.name'))

@section('content')
    <div class="rounded-lg border border-neutral-200 bg-white shadow-sm">
        <div class="border-b border-neutral-100 px-6 py-4">
            <h1 class="text-lg font-semibold text-neutral-900">Acceso administrativo</h1>
            <p class="mt-1 text-sm text-neutral-600">Credenciales del personal autorizado.</p>
        </div>
        <form method="post" action="{{ route('login') }}" class="space-y-4 px-6 py-6">
            @csrf
            <x-input label="Usuario" name="username" autocomplete="username" :value="old('username')" />
            <x-input label="Contrasena" name="password" type="password" autocomplete="current-password" />
            <div class="flex items-center gap-2">
                <input
                    id="remember"
                    name="remember"
                    type="checkbox"
                    value="1"
                    class="h-4 w-4 rounded border-neutral-300 text-brand focus:ring-brand"
                    @checked(old('remember'))
                />
                <label for="remember" class="text-sm text-neutral-700">Recordarme</label>
            </div>
            <x-button type="submit" variant="primary" class="w-full justify-center">
                Entrar
            </x-button>
        </form>
        <p class="border-t border-neutral-100 px-6 py-4 text-center text-sm text-neutral-600">
            <a href="{{ route('home') }}" class="font-medium text-brand hover:underline">Volver al portal de admision</a>
        </p>
    </div>
@endsection
