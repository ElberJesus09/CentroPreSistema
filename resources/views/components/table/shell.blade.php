{{-- Contenedor responsive estandar para tablas administrativas --}}
<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
            {{ $slot }}
        </table>
    </div>
    @isset($footer)
        <div class="border-t border-neutral-100 px-4 py-3">
            {{ $footer }}
        </div>
    @endisset
</div>
