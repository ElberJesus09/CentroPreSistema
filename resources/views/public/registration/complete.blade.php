@extends('layouts.portal')

@section('title', 'Inscripción completada | '.config('app.name'))

@section('content')
    <div class="rounded-xl border border-neutral-200 bg-white p-8 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-brand">Registro exitoso</p>
        <h1 class="mt-2 text-2xl font-semibold text-neutral-900">¡Gracias, {{ e($summary['student_name'] ?? '') }}!</h1>
        <p class="mt-4 text-sm leading-relaxed text-neutral-600">
            Su postulación fue registrada correctamente en el sistema.
        </p>

        @if (! empty($summary['mail_sent']))
            <div class="mt-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900">
                Le enviamos un correo de confirmación con los PDFs institucionales y los datos del examen de ingreso.
            </div>
        @elseif (! empty($summary['mail_message']))
            <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                {{ e($summary['mail_message']) }}
            </div>
        @else
            <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                No pudimos enviar el correo automático. Guarde su número de expediente y contacte a secretaría si lo requiere.
            </div>
        @endif

        <div class="mt-8 border-t border-neutral-100 pt-6">
            <h2 class="text-sm font-semibold text-neutral-900">Examen de ingreso</h2>
            <dl class="mt-3 grid gap-2 text-sm text-neutral-600">
                <div class="flex gap-2">
                    <dt class="w-24 shrink-0 text-neutral-500">Fecha</dt>
                    <dd>{{ $exam->exam_date ? $exam->exam_date->format('d/m/Y') : 'Por confirmar' }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="w-24 shrink-0 text-neutral-500">Hora</dt>
                    <dd>{{ $exam->exam_time ? e($exam->exam_time) : 'Por confirmar' }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="w-24 shrink-0 text-neutral-500">Lugar</dt>
                    <dd>{{ $exam->exam_location ? e($exam->exam_location) : 'Por confirmar' }}</dd>
                </div>
            </dl>
            @if ($exam->institutional_message)
                <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-neutral-700">{{ e($exam->institutional_message) }}</p>
            @endif
        </div>

        <div class="mt-10">
            <a
                href="{{ route('home') }}"
                class="inline-flex items-center justify-center rounded-md border border-transparent bg-brand px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-brand/90"
            >
                Volver al inicio
            </a>
        </div>
    </div>
@endsection
