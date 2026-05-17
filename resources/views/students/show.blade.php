@extends('layouts.app')

@section('title', 'Alumno | '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="font-display text-xl font-bold text-primary md:text-2xl">{{ $student->fullName() }}</h1>
            <p class="text-sm text-on-surface-variant">Ficha administrativa del alumno.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a
                href="{{ route('students.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-outline-variant px-4 py-2 text-sm font-semibold text-primary hover:bg-surface-container-high"
            >
                Volver
            </a>
            @can('downloadRegistrationDocuments', $student)
                <a
                    href="{{ route('students.registration-documents.download', $student) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-outline-variant px-4 py-2 text-sm font-semibold text-primary hover:bg-surface-container-high"
                >
                    Descargar PDFs
                </a>
            @endcan
            @can('viewAny', \App\Models\Student::class)
                <a
                    href="{{ route('students.cards.download', ['student' => $student->id]) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-outline-variant px-4 py-2 text-sm font-semibold text-primary hover:bg-surface-container-high"
                >
                    Carnet PDF
                </a>
            @endcan
            @can('resendRegistrationMail', $student)
                <form method="post" action="{{ route('students.registration-mail.resend', $student) }}">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg border border-outline-variant px-4 py-2 text-sm font-semibold text-primary hover:bg-surface-container-high"
                    >
                        Reenviar correo
                    </button>
                </form>
            @endcan
            @can('update', $student)
                <a
                    href="{{ route('students.edit', $student) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-transparent bg-primary px-4 py-2 text-sm font-semibold text-on-primary shadow-sm transition-colors hover:bg-primary-container"
                >
                    Editar
                </a>
            @endcan
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm">
            <h2 class="text-sm font-bold uppercase tracking-wide text-on-surface-variant">Datos del alumno</h2>
            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="font-semibold text-on-surface-variant">DNI</dt>
                    <dd class="mt-1 text-on-surface">{{ $student->dni }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-on-surface-variant">Correo</dt>
                    <dd class="mt-1 text-on-surface">{{ $student->email }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-on-surface-variant">Telefono</dt>
                    <dd class="mt-1 text-on-surface">{{ $student->phone }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-on-surface-variant">Estado</dt>
                    <dd class="mt-1 text-on-surface">
                        @if ($student->status === \App\Models\Student::STATUS_ACTIVE)
                            Activo
                        @elseif ($student->status === \App\Models\Student::STATUS_REJECTED)
                            Rechazado
                        @else
                            Pendiente
                        @endif
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="font-semibold text-on-surface-variant">Direccion</dt>
                    <dd class="mt-1 text-on-surface">{{ $student->address }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm">
            <h2 class="text-sm font-bold uppercase tracking-wide text-on-surface-variant">Inscripcion</h2>
            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="font-semibold text-on-surface-variant">Carrera</dt>
                    <dd class="mt-1 text-on-surface">{{ $student->career?->name ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-on-surface-variant">Ciclo</dt>
                    <dd class="mt-1 text-on-surface">{{ $student->academicCycle?->name ?? $student->schedule?->academicCycle?->name ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-on-surface-variant">Sede</dt>
                    <dd class="mt-1 text-on-surface">{{ $student->schedule?->campus?->name ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-on-surface-variant">Turno</dt>
                    <dd class="mt-1 text-on-surface">{{ $student->schedule?->shift?->name ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-on-surface-variant">Fecha registro</dt>
                    <dd class="mt-1 text-on-surface">{{ $student->registration_date?->format('Y-m-d') }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-on-surface-variant">Voucher</dt>
                    <dd class="mt-1 text-on-surface">{{ $student->payment_voucher_number ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-on-surface-variant">Agencia</dt>
                    <dd class="mt-1 text-on-surface">{{ $student->payment_agency_number ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-on-surface-variant">Fecha pago</dt>
                    <dd class="mt-1 text-on-surface">{{ $student->payment_date?->format('Y-m-d') ?? '---' }}</dd>
                </div>
            </dl>
        </section>
    </div>
@endsection
