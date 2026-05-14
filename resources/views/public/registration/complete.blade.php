@extends('layouts.portal')

@section('title', 'Inscripción completada | '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-2xl rounded-xl border border-outline-variant/40 bg-surface-container-lowest p-8 shadow-[0_4px_12px_rgba(0,0,0,0.05)] md:p-10">
        <p class="text-xs font-bold uppercase tracking-wide text-secondary">Registro exitoso</p>
        <h1 class="font-display mt-2 text-2xl font-bold text-primary md:text-3xl">
            ¡Gracias, {{ e($summary['student_name'] ?? '') }}!
        </h1>
        <p class="mt-4 text-sm leading-relaxed text-on-surface-variant md:text-base">
            Su postulación fue registrada correctamente en el sistema.
        </p>

        @if (! empty($summary['mail_sent']))
            <div class="mt-6 rounded-lg border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-950">
                Le enviamos un correo de confirmación con los PDF institucionales y los datos del examen de ingreso.
            </div>
        @elseif (! empty($summary['mail_message']))
            <div class="mt-6 rounded-lg border border-secondary-container/50 bg-secondary-container/20 px-4 py-3 text-sm text-on-secondary-container">
                {{ e($summary['mail_message']) }}
            </div>
        @else
            <div class="mt-6 rounded-lg border border-secondary-container/50 bg-secondary-container/20 px-4 py-3 text-sm text-on-secondary-container">
                No pudimos enviar el correo automático. Guarde su número de expediente y contacte a secretaría si lo requiere.
            </div>
        @endif

        <div class="mt-8 border-t border-outline-variant/50 pt-6">
            <h2 class="font-display text-lg font-semibold text-primary">Examen de ingreso</h2>
            <dl class="mt-4 grid gap-2 text-sm text-on-surface-variant">
                <div class="flex gap-2">
                    <dt class="w-24 shrink-0 font-medium text-on-surface">Fecha</dt>
                    <dd>{{ $exam->exam_date ? $exam->exam_date->format('d/m/Y') : 'Por confirmar' }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="w-24 shrink-0 font-medium text-on-surface">Hora</dt>
                    <dd>{{ $exam->exam_time ? e($exam->exam_time) : 'Por confirmar' }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="w-24 shrink-0 font-medium text-on-surface">Lugar</dt>
                    <dd>{{ $exam->exam_location ? e($exam->exam_location) : 'Por confirmar' }}</dd>
                </div>
            </dl>
            @if ($exam->institutional_message)
                <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-on-surface">{{ e($exam->institutional_message) }}</p>
            @endif
        </div>

        <div class="mt-10">
            <a
                href="{{ route('home') }}"
                class="inline-flex items-center justify-center rounded-lg bg-primary px-6 py-2.5 text-sm font-semibold text-on-primary shadow-md transition hover:bg-primary-container active:scale-[0.98]"
            >
                Volver al inicio
            </a>
        </div>
    </div>
@endsection
