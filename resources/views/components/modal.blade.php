{{-- Modal nativo (dialog). Cerrar con el botón o programáticamente .close() --}}
@props([
    'id' => 'modal',
])

<dialog
    id="{{ $id }}"
    class="w-full max-w-lg rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-xl backdrop:bg-black/40"
>
    {{ $slot }}
    <div class="mt-6 flex justify-end border-t border-outline-variant/50 pt-4">
        <button
            type="button"
            class="rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2 text-sm font-semibold text-on-surface hover:bg-surface-container-high"
            onclick="this.closest('dialog').close()"
        >
            Cerrar
        </button>
    </div>
</dialog>
