@extends('layouts.guest')

@section('title', 'Iniciar sesión | '.config('app.name'))

@section('content')
    <div class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm">
        <div class="border-b border-outline-variant/50 px-6 py-5">
            <h1 class="font-display text-lg font-bold text-primary">Acceso administrativo</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Credenciales del personal autorizado.</p>
        </div>
        <form method="post" action="{{ route('login') }}" class="space-y-4 px-6 py-6">
            @csrf
            <x-input label="Usuario" name="username" autocomplete="username" :value="old('username')" />
            <x-input label="Contraseña" name="password" type="password" autocomplete="current-password" />
            <div class="flex items-center gap-2">
                <input
                    id="remember"
                    name="remember"
                    type="checkbox"
                    value="1"
                    class="h-4 w-4 rounded border-outline-variant text-primary focus:ring-primary"
                    @checked(old('remember'))
                />
                <label for="remember" class="text-sm text-on-surface">Recordarme</label>
            </div>
            <x-button type="submit" variant="primary" class="w-full justify-center rounded-lg py-2.5">
                Entrar
            </x-button>
        </form>
        <p class="border-t border-outline-variant/50 px-6 py-4 text-center text-sm text-on-surface-variant">
            <a href="{{ route('home') }}" class="font-semibold text-primary hover:text-secondary hover:underline">Volver al portal de admisión</a>
        </p>
    </div>
@endsection
