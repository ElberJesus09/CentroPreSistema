{{-- Modal nativo (dialog). Cerrar con el boton, Escape o programaticamente .close() --}}
@props([
    'id' => 'modal',
    'title' => null,
    'description' => null,
    'variant' => 'default',
])

@php
    $iconClass = $variant === 'danger'
        ? 'bg-error-container text-error'
        : 'bg-primary-fixed text-primary';
@endphp

<dialog
    id="{{ $id }}"
    class="fixed inset-0 z-50 m-auto w-[calc(100%-2rem)] max-w-lg overflow-hidden whitespace-normal rounded-lg border border-outline-variant bg-surface-container-lowest p-0 text-left shadow-2xl backdrop:bg-black/50 backdrop:backdrop-blur-[2px]"
    @if ($title) aria-labelledby="{{ $id }}-title" @endif
    @if ($description) aria-describedby="{{ $id }}-description" @endif
    onclick="if (event.target === this) this.close()"
>
    <div class="flex items-start gap-4 p-5 sm:p-6">
        <div class="{{ $iconClass }} flex h-11 w-11 shrink-0 items-center justify-center rounded-full">
            <span class="material-symbols-outlined" aria-hidden="true">
                {{ $variant === 'danger' ? 'warning' : 'info' }}
            </span>
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-3">
                <div>
                    @if ($title)
                        <h2 id="{{ $id }}-title" class="font-display text-lg font-bold text-on-surface">{{ $title }}</h2>
                    @endif
                    @if ($description)
                        <p id="{{ $id }}-description" class="mt-1 text-sm text-on-surface-variant">{{ $description }}</p>
                    @endif
                </div>
                <button
                    type="button"
                    class="-mr-2 -mt-2 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface focus:outline-none focus:ring-2 focus:ring-primary"
                    onclick="this.closest('dialog').close()"
                    aria-label="Cerrar modal"
                >
                    <span class="material-symbols-outlined text-[1.25rem]" aria-hidden="true">close</span>
                </button>
            </div>

            <div class="mt-4 text-sm leading-6 text-on-surface break-words">
                {{ $slot }}
            </div>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-2 border-t border-outline-variant/60 bg-surface-container-low px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
        @isset($actions)
            {{ $actions }}
        @else
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface transition-colors hover:bg-surface-container-high focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                onclick="this.closest('dialog').close()"
            >
                Cerrar
            </button>
        @endisset
    </div>
</dialog>
