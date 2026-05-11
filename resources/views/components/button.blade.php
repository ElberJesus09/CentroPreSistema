@props([
    'type' => 'button',
    'variant' => 'primary',
])

@php
    $variantClass = match ($variant) {
        'primary' => 'bg-brand text-white hover:bg-brand/90 border border-transparent',
        'secondary' => 'bg-white text-neutral-800 border border-neutral-300 hover:bg-neutral-50',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 border border-transparent',
        'link' => 'bg-transparent text-brand border border-transparent hover:underline',
        default => 'bg-brand text-white hover:bg-brand/90 border border-transparent',
    };
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 disabled:opacity-50 '.$variantClass]) }}
>
    {{ $slot }}
</button>
