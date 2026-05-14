@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
    'autocomplete' => null,
    'errorKey' => null,
])

@php
    $dotKey = $errorKey ?? preg_replace('/^(\w+)\[(\w+)\]$/', '$1.$2', $name);
    $fieldId = 'in_'.md5($name);
@endphp

<div class="space-y-1">
    <label for="{{ $fieldId }}" class="block text-sm font-semibold text-on-surface-variant">{{ $label }}</label>
    <input
        id="{{ $fieldId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($dotKey, $value) }}"
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        {{ $attributes->merge(['class' => 'block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm placeholder:text-outline/50 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary']) }}
    />
    @error($dotKey)
        <p class="text-sm text-error">{{ $message }}</p>
    @enderror
</div>
