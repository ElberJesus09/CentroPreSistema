@extends('layouts.app')

@section('title', 'Staff | '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-xl font-semibold text-neutral-900">Empleados</h1>
            <p class="text-sm text-neutral-600">Gestion de personal con roles y accesos.</p>
        </div>
        @can('create', \App\Models\Staff::class)
            <a
                href="{{ route('staff.create') }}"
                class="inline-flex items-center justify-center rounded-md border border-transparent bg-brand px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-brand/90 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2"
            >
                Nuevo empleado
            </a>
        @endcan
    </div>

    <x-table.shell>
        @if ($staffList->hasPages())
            <x-slot:footer>
                {{ $staffList->links() }}
            </x-slot:footer>
        @endif
        <thead class="bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-600">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Nombres</th>
                        <th class="px-4 py-3">Apellido paterno</th>
                        <th class="px-4 py-3">Apellido materno</th>
                        <th class="px-4 py-3">DNI</th>
                        <th class="px-4 py-3">Celular</th>
                        <th class="px-4 py-3">Correo</th>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="px-4 py-3">Rol</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Ultimo acceso</th>
                        <th class="sticky right-0 bg-neutral-50 px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 text-neutral-800">
                    @forelse ($staffList as $row)
                        <tr class="hover:bg-neutral-50/80">
                            <td class="whitespace-nowrap px-4 py-3 text-neutral-600">{{ $row->id }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $row->first_name }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $row->last_name }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $row->mother_last_name }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $row->dni }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $row->phone }}</td>
                            <td class="px-4 py-3">{{ $row->email }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $row->username }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $row->role?->name }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($row->status)
                                    <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-800">
                                        Activo
                                    </span>
                                @else
                                    <span class="rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-700">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-neutral-600">
                                {{ $row->last_login_at?->format('Y-m-d H:i') ?? '—' }}
                            </td>
                            <td
                                class="sticky right-0 whitespace-nowrap bg-white px-4 py-3 text-right shadow-[-6px_0_8px_-6px_rgba(0,0,0,0.08)]"
                            >
                                @can('update', $row)
                                    <a
                                        href="{{ route('staff.edit', $row) }}"
                                        class="mr-2 text-sm font-medium text-brand hover:underline"
                                    >
                                        Editar
                                    </a>
                                @endcan
                                @can('delete', $row)
                                    <form
                                        method="post"
                                        action="{{ route('staff.destroy', $row) }}"
                                        class="inline"
                                        onsubmit="return confirm('Eliminar este empleado?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="text-sm font-medium text-red-600 hover:underline"
                                        >
                                            Eliminar
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-4 py-8 text-center text-sm text-neutral-500">
                                No hay empleados registrados.
                            </td>
                        </tr>
                    @endforelse
        </tbody>
    </x-table.shell>
@endsection
