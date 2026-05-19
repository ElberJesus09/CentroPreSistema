@extends('layouts.app')

@section('title', 'Sedes | '.config('app.name'))

@section('content')
    <x-academic.nav section="campuses" />

    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-xl font-semibold text-neutral-900">Sedes</h1>
            <p class="text-sm text-neutral-600">Campus disponibles para programacion de turnos.</p>
        </div>
        @can('create', \App\Models\Campus::class)
            <a
                href="{{ route('academic-cycles.campuses.create') }}"
                class="inline-flex items-center justify-center rounded-md border border-transparent bg-brand px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-brand/90 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2"
            >
                Nueva sede
            </a>
        @endcan
    </div>

    <x-table.shell>
        @if ($campuses->hasPages())
            <x-slot:footer>
                {{ $campuses->links() }}
            </x-slot:footer>
        @endif
        <thead class="bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-600">
            <tr>
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">Nombre</th>
                <th class="px-4 py-3">Direccion</th>
                <th class="px-4 py-3">Estado</th>
                <th class="sticky right-0 bg-neutral-50 px-4 py-3 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 text-neutral-800">
            @forelse ($campuses as $row)
                <tr class="hover:bg-neutral-50/80">
                    <td class="whitespace-nowrap px-4 py-3 text-neutral-600">{{ $row->id }}</td>
                    <td class="whitespace-nowrap px-4 py-3 font-medium">{{ $row->name }}</td>
                    <td class="max-w-xs truncate px-4 py-3 text-neutral-600">{{ $row->address ?? '—' }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        @if ($row->status)
                            <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-800">Activo</span>
                        @else
                            <span class="rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-700">Inactivo</span>
                        @endif
                    </td>
                    <td
                        class="sticky right-0 whitespace-nowrap bg-white px-4 py-3 text-right shadow-[-6px_0_8px_-6px_rgba(0,0,0,0.08)]"
                    >
                        @can('update', $row)
                            <a href="{{ route('academic-cycles.campuses.edit', $row) }}" class="mr-2 text-sm font-medium text-brand hover:underline">
                                Editar
                            </a>
                        @endcan
                        @can('delete', $row)
                            <button
                                type="button"
                                class="text-sm font-medium text-red-600 hover:underline"
                                onclick="document.getElementById('campus-delete-{{ $row->id }}').showModal()"
                            >
                                Eliminar
                            </button>
                            <x-modal
                                id="campus-delete-{{ $row->id }}"
                                title="Eliminar sede"
                                description="Esta accion no se puede deshacer."
                                variant="danger"
                            >
                                <p>Eliminaras la sede <strong>{{ $row->name }}</strong>.</p>
                                <p class="mt-2 text-on-surface-variant">No podra eliminarse si esta asignada en programaciones.</p>

                                <x-slot:actions>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface transition-colors hover:bg-surface-container-high focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                        onclick="this.closest('dialog').close()"
                                    >
                                        Cancelar
                                    </button>
                                    <form method="post" action="{{ route('academic-cycles.campuses.destroy', $row) }}" class="contents">
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
                    <td colspan="5" class="px-4 py-8 text-center text-sm text-neutral-500">No hay sedes registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </x-table.shell>
@endsection
