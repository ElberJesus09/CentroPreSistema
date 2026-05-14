@extends('layouts.app')

@section('title', 'Alumnos | '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="font-display text-xl font-bold text-primary md:text-2xl">Alumnos</h1>
            <p class="text-sm text-on-surface-variant">Postulantes y matrículas por carrera y turno.</p>
        </div>
        @can('create', \App\Models\Student::class)
            <a
                href="{{ route('students.create') }}"
                class="inline-flex items-center justify-center rounded-lg border border-transparent bg-primary px-4 py-2 text-sm font-semibold text-on-primary shadow-sm transition-colors hover:bg-primary-container focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Registrar alumno
            </a>
        @endcan
    </div>

    <x-table.shell>
        @if ($students->hasPages())
            <x-slot:footer>
                {{ $students->links() }}
            </x-slot:footer>
        @endif
        <thead class="bg-surface-container-high text-xs font-bold uppercase tracking-wide text-on-surface-variant">
            <tr>
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">Alumno</th>
                <th class="px-4 py-3">DNI</th>
                <th class="px-4 py-3">Carrera</th>
                <th class="px-4 py-3">Ciclo</th>
                <th class="px-4 py-3">Sede</th>
                <th class="px-4 py-3">Turno</th>
                <th class="px-4 py-3">Estado</th>
                <th class="px-4 py-3">Fecha registro</th>
                <th class="sticky right-0 bg-surface-container-high px-4 py-3 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/50 text-on-surface">
            @forelse ($students as $row)
                <tr class="hover:bg-surface-container-low/80">
                    <td class="whitespace-nowrap px-4 py-3 text-on-surface-variant">{{ $row->id }}</td>
                    <td class="min-w-[10rem] px-4 py-3 font-medium">{{ $row->fullName() }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $row->dni }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $row->career?->name }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $row->schedule?->academicCycle?->name }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $row->schedule?->campus?->name }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $row->schedule?->shift?->name }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        @if ($row->status === \App\Models\Student::STATUS_ACTIVE)
                            <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-800">Activo</span>
                        @elseif ($row->status === \App\Models\Student::STATUS_REJECTED)
                            <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-800">Rechazado</span>
                        @else
                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-900">Pendiente</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-on-surface-variant">{{ $row->registration_date?->format('Y-m-d') }}</td>
                    <td
                        class="sticky right-0 whitespace-nowrap bg-surface-container-lowest px-4 py-3 text-right shadow-[-6px_0_8px_-6px_rgba(0,0,0,0.08)]"
                    >
                        @can('downloadRegistrationDocuments', $row)
                            <a
                                href="{{ route('students.registration-documents.download', $row) }}"
                                class="mr-2 text-sm font-semibold text-on-surface-variant hover:text-primary hover:underline"
                            >
                                Descargar PDFs
                            </a>
                        @endcan
                        @can('resendRegistrationMail', $row)
                            <form method="post" action="{{ route('students.registration-mail.resend', $row) }}" class="mr-2 inline">
                                @csrf
                                <button type="submit" class="text-sm font-semibold text-on-surface-variant hover:text-primary hover:underline">
                                    Reenviar correo
                                </button>
                            </form>
                        @endcan
                        @can('update', $row)
                            <a href="{{ route('students.edit', $row) }}" class="mr-2 text-sm font-semibold text-primary hover:underline">
                                Editar
                            </a>
                        @endcan
                        @can('delete', $row)
                            <button
                                type="button"
                                class="text-sm font-semibold text-error hover:underline"
                                onclick="document.getElementById('student-delete-{{ $row->id }}').showModal()"
                            >
                                Eliminar
                            </button>
                            <x-modal id="student-delete-{{ $row->id }}">
                                <p class="text-sm text-on-surface">¿Eliminar a <strong>{{ $row->fullName() }}</strong>? Se liberará el cupo del turno.</p>
                                <form method="post" action="{{ route('students.destroy', $row) }}" class="mt-4 flex gap-2">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="danger">Confirmar eliminación</x-button>
                                </form>
                            </x-modal>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-4 py-8 text-center text-sm text-on-surface-variant">No hay alumnos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </x-table.shell>
@endsection
