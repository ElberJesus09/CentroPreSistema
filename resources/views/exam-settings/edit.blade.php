@extends('layouts.app')

@section('title', 'Examen de ingreso | '.config('app.name'))

@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-neutral-900">Examen de ingreso</h1>
        <p class="text-sm text-neutral-600">Estos datos aparecen en el correo de confirmación al postulante.</p>
    </div>

    <div class="max-w-xl rounded-lg border border-neutral-200 bg-white p-6 shadow-sm">
        <form method="post" action="{{ route('exam-settings.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="exam_date" class="block text-sm font-medium text-neutral-700">Fecha del examen</label>
                <input
                    type="date"
                    name="exam_date"
                    id="exam_date"
                    value="{{ old('exam_date', optional($examSetting->exam_date)->format('Y-m-d')) }}"
                    class="mt-1 block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"
                />
            </div>

            <div>
                <label for="exam_time" class="block text-sm font-medium text-neutral-700">Hora del examen</label>
                <input
                    type="text"
                    name="exam_time"
                    id="exam_time"
                    value="{{ old('exam_time', $examSetting->exam_time) }}"
                    placeholder="Ej. 09:00"
                    class="mt-1 block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"
                />
            </div>

            <div>
                <label for="exam_location" class="block text-sm font-medium text-neutral-700">Lugar del examen</label>
                <input
                    type="text"
                    name="exam_location"
                    id="exam_location"
                    value="{{ old('exam_location', $examSetting->exam_location) }}"
                    class="mt-1 block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"
                />
            </div>

            <div>
                <label for="institutional_message" class="block text-sm font-medium text-neutral-700">Mensaje institucional / indicaciones</label>
                <textarea
                    name="institutional_message"
                    id="institutional_message"
                    rows="5"
                    class="mt-1 block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"
                >{{ old('institutional_message', $examSetting->institutional_message) }}</textarea>
            </div>

            <div class="flex justify-end">
                <x-button type="submit">Guardar cambios</x-button>
            </div>
        </form>
    </div>
@endsection
