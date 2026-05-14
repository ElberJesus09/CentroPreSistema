@extends('layouts.app')

@section('title', 'Examen de ingreso | '.config('app.name'))

@section('content')
    <div class="mb-6">
        <h1 class="font-display text-xl font-bold text-primary md:text-2xl">Examen de ingreso</h1>
        <p class="text-sm text-on-surface-variant">Estos datos aparecen en el correo de confirmación al postulante.</p>
    </div>

    <div class="max-w-xl rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-6 shadow-sm md:p-8">
        <form method="post" action="{{ route('exam-settings.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="exam_date" class="block text-sm font-semibold text-on-surface-variant">Fecha del examen</label>
                <input
                    type="date"
                    name="exam_date"
                    id="exam_date"
                    value="{{ old('exam_date', optional($examSetting->exam_date)->format('Y-m-d')) }}"
                    class="mt-1 block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                />
            </div>

            <div>
                <label for="exam_time" class="block text-sm font-semibold text-on-surface-variant">Hora del examen</label>
                <input
                    type="text"
                    name="exam_time"
                    id="exam_time"
                    value="{{ old('exam_time', $examSetting->exam_time) }}"
                    placeholder="Ej. 09:00"
                    class="mt-1 block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                />
            </div>

            <div>
                <label for="exam_location" class="block text-sm font-semibold text-on-surface-variant">Lugar del examen</label>
                <input
                    type="text"
                    name="exam_location"
                    id="exam_location"
                    value="{{ old('exam_location', $examSetting->exam_location) }}"
                    class="mt-1 block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                />
            </div>

            <div>
                <label for="institutional_message" class="block text-sm font-semibold text-on-surface-variant">Mensaje institucional / indicaciones</label>
                <textarea
                    name="institutional_message"
                    id="institutional_message"
                    rows="5"
                    class="mt-1 block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >{{ old('institutional_message', $examSetting->institutional_message) }}</textarea>
            </div>

            <div class="flex justify-end border-t border-outline-variant/50 pt-5">
                <x-button type="submit">Guardar cambios</x-button>
            </div>
        </form>
    </div>
@endsection
