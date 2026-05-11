@extends('layouts.guest')

@section('title', '403 | Unauthorized')

@section('content')
    <div class="rounded-lg border border-neutral-200 bg-white p-8 text-center shadow-sm">
        <p class="text-sm font-semibold text-brand">403</p>
        <h1 class="mt-2 text-lg font-semibold text-neutral-900">Unauthorized</h1>
        <p class="mt-2 text-sm text-neutral-600">No tiene permiso para acceder a este recurso.</p>
        <div class="mt-6">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="text-sm font-medium text-brand hover:underline">
                Volver
            </a>
        </div>
    </div>
@endsection
