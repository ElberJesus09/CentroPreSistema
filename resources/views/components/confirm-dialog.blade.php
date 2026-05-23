<dialog
    id="app-confirm-dialog"
    class="fixed inset-0 z-50 m-auto w-[calc(100%-2rem)] max-w-md overflow-hidden rounded-lg border border-outline-variant bg-surface-container-lowest p-0 text-left shadow-2xl backdrop:bg-black/50 backdrop:backdrop-blur-[2px]"
    aria-labelledby="app-confirm-dialog-title"
    aria-describedby="app-confirm-dialog-message"
    onclick="if (event.target === this) this.close()"
>
    <div class="flex items-start gap-4 p-5 sm:p-6">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-fixed text-primary">
            <span class="material-symbols-outlined" aria-hidden="true">help</span>
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 id="app-confirm-dialog-title" class="font-display text-lg font-bold text-on-surface">Confirmar acción</h2>
                    <p id="app-confirm-dialog-message" class="mt-1 text-sm text-on-surface-variant">¿Deseas continuar?</p>
                </div>
                <button
                    type="button"
                    class="-mr-2 -mt-2 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface focus:outline-none focus:ring-2 focus:ring-primary"
                    data-confirm-cancel
                    aria-label="Cerrar modal"
                >
                    <span class="material-symbols-outlined text-[1.25rem]" aria-hidden="true">close</span>
                </button>
            </div>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-2 border-t border-outline-variant/60 bg-surface-container-low px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
        <button
            type="button"
            class="inline-flex items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface transition-colors hover:bg-surface-container-high focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            data-confirm-cancel
        >
            Cancelar
        </button>
        <button
            type="button"
            class="inline-flex items-center justify-center rounded-lg border border-transparent bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary shadow-sm transition-colors hover:bg-primary-container focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            data-confirm-accept
        >
            Confirmar
        </button>
    </div>
</dialog>
