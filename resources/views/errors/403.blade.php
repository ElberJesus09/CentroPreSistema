@extends('layouts.guest')

@section('title', '403 | Acceso no autorizado')

@section('content')
    <div class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-8 text-center shadow-sm">
        <p class="text-sm font-bold text-primary">403</p>
        <h1 class="mt-2 font-display text-lg font-bold text-on-surface">Acceso no autorizado</h1>
        <p class="mt-2 text-sm text-on-surface-variant">No tiene permiso para acceder a este recurso.</p>
        <div class="mt-6">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="text-sm font-semibold text-primary hover:underline">
                Volver
            </a>
        </div>
    </div>
@endsection
