{{-- Modal nativo (dialog). Cerrar con el boton o programaticamente .close() --}}
@props([
    'id' => 'modal',
])

<dialog
    id="{{ $id }}"
    class="w-full max-w-lg rounded-lg border border-neutral-200 bg-white p-6 shadow-xl backdrop:bg-black/40"
>
    {{ $slot }}
    <div class="mt-6 flex justify-end border-t border-neutral-100 pt-4">
        <button
            type="button"
            class="rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-800 hover:bg-neutral-50"
            onclick="this.closest('dialog').close()"
        >
            Cerrar
        </button>
    </div>
</dialog>
