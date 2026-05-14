@props([
    'step' => 1,
    'steps' => [
        1 => 'Personales',
        2 => 'Apoderado',
        3 => 'Colegio',
        4 => 'Académico',
        5 => 'Confirmación',
    ],
])

<nav class="mb-10" aria-label="Progreso del formulario">
    <ol class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-start sm:justify-between sm:gap-2">
        @foreach ($steps as $n => $label)
            <li class="flex min-w-0 flex-1 items-center gap-2 sm:flex-col sm:items-center sm:gap-2">
                <span
                    @class([
                        'flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold shadow-sm transition-colors',
                        'bg-primary text-on-primary' => $step === $n,
                        'bg-primary-container text-on-primary-container' => $step > $n,
                        'border border-outline-variant bg-surface-container-high text-on-surface-variant' => $step < $n,
                    ])
                >
                    {{ $n }}
                </span>
                <span
                    @class([
                        'text-sm font-semibold sm:text-center',
                        'text-primary' => $step === $n,
                        'text-primary-container' => $step > $n,
                        'text-on-surface-variant opacity-70' => $step < $n,
                    ])
                >
                    {{ $label }}
                </span>
            </li>
        @endforeach
    </ol>
    <div class="relative mt-6 h-0.5 w-full overflow-hidden rounded-full bg-surface-container-high">
        <div
            class="h-full rounded-full bg-primary transition-all duration-300 ease-out"
            style="width: {{ ($step - 1) / max(count($steps) - 1, 1) * 100 }}%"
        ></div>
    </div>
</nav>
