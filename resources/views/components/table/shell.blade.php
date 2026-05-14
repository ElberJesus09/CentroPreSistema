{{-- Contenedor responsive estándar para tablas administrativas --}}
<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-outline-variant/60 text-left text-sm">
            {{ $slot }}
        </table>
    </div>
    @isset($footer)
        <div class="border-t border-outline-variant/50 bg-surface-container-low px-4 py-3">
            {{ $footer }}
        </div>
    @endisset
</div>
