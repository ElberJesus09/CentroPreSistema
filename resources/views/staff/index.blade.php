@extends('layouts.app')

@section('title', 'Personal | '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="font-display text-xl font-bold text-primary md:text-2xl">Empleados</h1>
            <p class="text-sm text-on-surface-variant">Gestión de personal con roles y accesos.</p>
        </div>
        @can('create', \App\Models\Staff::class)
            <a
                href="{{ route('staff.create') }}"
                class="inline-flex items-center justify-center rounded-lg border border-transparent bg-primary px-4 py-2 text-sm font-semibold text-on-primary shadow-sm transition-colors hover:bg-primary-container focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
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
                            <td class="whitespace-nowrap px-4 py-3">{{ $row->role?->displayName() ?? 'Sin rol' }}</td>
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
                                    <button
                                        type="button"
                                        class="text-sm font-medium text-red-600 hover:underline"
                                        onclick="document.getElementById('staff-delete-{{ $row->id }}').showModal()"
                                    >
                                        Eliminar
                                    </button>
                                    <x-modal
                                        id="staff-delete-{{ $row->id }}"
                                        title="Eliminar empleado"
                                        description="Esta acción no se puede deshacer."
                                        variant="danger"
                                    >
                                        <p>Eliminaras el acceso de <strong>{{ $row->first_name }} {{ $row->last_name }}</strong>.</p>
                                        <p class="mt-2 text-on-surface-variant">El empleado dejara de ingresar al panel administrativo.</p>

                                        <x-slot:actions>
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface transition-colors hover:bg-surface-container-high focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                                onclick="this.closest('dialog').close()"
                                            >
                                                Cancelar
                                            </button>
                                            <form method="post" action="{{ route('staff.destroy', $row) }}" class="contents">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="danger">Confirmar eliminacion</x-button>
                                            </form>
                                        </x-slot:actions>
                                    </x-modal>
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
