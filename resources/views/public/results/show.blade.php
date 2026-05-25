@extends('layouts.portal')

@section('title', 'Consulta de resultados | '.config('app.name'))

@section('content')
    <section class="mx-auto max-w-3xl">
        <div class="mb-6">
            <span class="inline-flex items-center gap-2 rounded-full bg-primary-fixed px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-primary">
                <span class="material-symbols-outlined text-base">leaderboard</span>
                Resultados academicos
            </span>
            <h1 class="mt-4 font-display text-3xl font-bold text-primary md:text-4xl">Consulta tu promedio y puesto</h1>
            <p class="mt-2 text-sm leading-relaxed text-on-surface-variant">
                Ingresa tu DNI para ver tu promedio academico y tu puesto dentro de tu carrera.
            </p>
        </div>

        <form method="get" action="{{ route('public.results') }}" class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm">
            <label for="dni" class="block text-sm font-semibold text-on-surface-variant">DNI</label>
            <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                <input
                    id="dni"
                    name="dni"
                    value="{{ old('dni', $dni) }}"
                    inputmode="numeric"
                    maxlength="8"
                    pattern="\d{8}"
                    placeholder="12345678"
                    class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    required
                />
                <x-button type="submit">Consultar</x-button>
            </div>
            @error('dni')
                <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </form>

        @if ($searched && $result === null)
            <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                No se encontro un alumno activo con ese DNI para el ciclo academico disponible.
            </div>
        @endif

        @if ($result !== null)
            @php $student = $result['student']; @endphp
            <section class="mt-6 overflow-hidden rounded-xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm">
                <div class="border-b border-outline-variant/50 p-5">
                    <p class="text-sm text-on-surface-variant">Alumno</p>
                    <h2 class="font-display text-2xl font-bold text-primary">{{ $student->fullName() }}</h2>
                    <p class="mt-1 text-sm text-on-surface-variant">{{ $student->career?->name }} - {{ $student->academicCycle?->name }}</p>
                </div>
                <div class="grid gap-0 sm:grid-cols-2">
                    <div class="border-b border-outline-variant/50 p-5 sm:border-b-0 sm:border-r">
                        <p class="text-sm text-on-surface-variant">Promedio</p>
                        <p class="mt-1 text-3xl font-bold text-primary">{{ number_format((float) $result['average'], 2) }}</p>
                    </div>
                    <div class="p-5">
                        <p class="text-sm text-on-surface-variant">Puesto por carrera</p>
                        <p class="mt-1 text-3xl font-bold text-primary">{{ $result['rank'] ?? '-' }}</p>
                    </div>
                </div>
                <div class="border-t border-outline-variant/50 p-5">
                    <h3 class="font-semibold text-primary">Notas registradas</h3>
                    <div class="mt-3 overflow-hidden rounded-lg border border-outline-variant bg-white">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-surface-container-high text-xs font-bold uppercase text-on-surface-variant">
                                <tr>
                                    <th class="px-4 py-3">Evaluacion</th>
                                    <th class="px-4 py-3">Nota</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/70">
                                @foreach ($result['grades'] as $grade)
                                    <tr>
                                        <td class="px-4 py-3">{{ $grade['name'] }}</td>
                                        <td class="px-4 py-3 font-semibold">{{ $grade['score'] === null ? 'Sin nota' : number_format((float) $grade['score'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif
    </section>
@endsection
