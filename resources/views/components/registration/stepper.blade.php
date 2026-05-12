@props([
    'step' => 1,
    'steps' => [
        1 => 'Personales',
        2 => 'Apoderado',
        3 => 'Colegio',
        4 => 'Academico',
        5 => 'Confirmacion',
    ],
])

<nav class="mb-8" aria-label="Progreso del formulario">
    <ol class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between sm:gap-2">
        @foreach ($steps as $n => $label)
            <li class="flex min-w-0 flex-1 items-center gap-2">
                <span
                    @class([
                        'flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold transition-colors',
                        'bg-brand text-white' => $step === $n,
                        'bg-green-600 text-white' => $step > $n,
                        'border border-neutral-300 bg-white text-neutral-500' => $step < $n,
                    ])
                >
                    {{ $n }}
                </span>
                <span
                    @class([
                        'truncate text-xs font-medium sm:text-sm',
                        'text-brand' => $step === $n,
                        'text-green-800' => $step > $n,
                        'text-neutral-500' => $step < $n,
                    ])
                >
                    {{ $label }}
                </span>
            </li>
        @endforeach
    </ol>
    <div class="mt-4 h-1.5 w-full overflow-hidden rounded-full bg-neutral-200">
        <div
            class="h-full rounded-full bg-brand transition-all duration-300 ease-out"
            style="width: {{ ($step - 1) / max(count($steps) - 1, 1) * 100 }}%"
        ></div>
    </div>
</nav>
