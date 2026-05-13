@extends('layouts.app')

@section('title', 'Alumnos | '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-xl font-semibold text-neutral-900">Alumnos</h1>
            <p class="text-sm text-neutral-600">Postulantes y matriculas por carrera y turno.</p>
        </div>
        @can('create', \App\Models\Student::class)
            <a
                href="{{ route('students.create') }}"
                class="inline-flex items-center justify-center rounded-md border border-transparent bg-brand px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-brand/90 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2"
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
        <thead class="bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-600">
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
                <th class="sticky right-0 bg-neutral-50 px-4 py-3 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 text-neutral-800">
            @forelse ($students as $row)
                <tr class="hover:bg-neutral-50/80">
                    <td class="whitespace-nowrap px-4 py-3 text-neutral-600">{{ $row->id }}</td>
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
                    <td class="whitespace-nowrap px-4 py-3 text-neutral-600">{{ $row->registration_date?->format('Y-m-d') }}</td>
                    <td
                        class="sticky right-0 whitespace-nowrap bg-white px-4 py-3 text-right shadow-[-6px_0_8px_-6px_rgba(0,0,0,0.08)]"
                    >
        @can('resendRegistrationMail', $row)
                            <form method="post" action="{{ route('students.registration-mail.resend', $row) }}" class="mr-2 inline">
                                @csrf
                                <button type="submit" class="text-sm font-medium text-neutral-700 hover:underline">
                                    Resend Email
                                </button>
                            </form>
                        @endcan
                        @can('update', $row)
                            <a href="{{ route('students.edit', $row) }}" class="mr-2 text-sm font-medium text-brand hover:underline">
                                Editar
                            </a>
                        @endcan
                        @can('delete', $row)
                            <button
                                type="button"
                                class="text-sm font-medium text-red-600 hover:underline"
                                onclick="document.getElementById('student-delete-{{ $row->id }}').showModal()"
                            >
                                Eliminar
                            </button>
                            <x-modal id="student-delete-{{ $row->id }}">
                                <p class="text-sm text-neutral-700">Eliminar a <strong>{{ $row->fullName() }}</strong>? Se liberara el cupo del turno.</p>
                                <form method="post" action="{{ route('students.destroy', $row) }}" class="mt-4 flex gap-2">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="danger">Confirmar eliminacion</x-button>
                                </form>
                            </x-modal>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-4 py-8 text-center text-sm text-neutral-500">No hay alumnos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </x-table.shell>
@endsection
