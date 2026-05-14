@props([
    'type' => 'button',
    'variant' => 'primary',
])

@php
    $variantClass = match ($variant) {
        'primary' => 'bg-primary text-on-primary hover:bg-primary-container border border-transparent shadow-sm',
        'secondary' => 'bg-surface-container-lowest text-on-surface border border-outline-variant hover:bg-surface-container-high',
        'danger' => 'bg-error text-white hover:opacity-90 border border-transparent',
        'link' => 'bg-transparent text-primary border border-transparent hover:text-secondary hover:underline shadow-none',
        default => 'bg-primary text-on-primary hover:bg-primary-container border border-transparent shadow-sm',
    };
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold transition-all focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-50 active:scale-[0.98] '.$variantClass]) }}
>
    {{ $slot }}
</button>
