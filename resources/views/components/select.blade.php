@props([
    'label',
    'name',
    'errorKey' => null,
])

@php
    $dotKey = $errorKey ?? preg_replace('/^(\w+)\[(\w+)\]$/', '$1.$2', $name);
    $fieldId = 'sel_'.md5($name);
    $hasError = $errors->has($dotKey);
@endphp

<div class="space-y-1">
    <label for="{{ $fieldId }}" class="block text-sm font-semibold text-on-surface-variant">{{ $label }}</label>
    <select
        id="{{ $fieldId }}"
        name="{{ $name }}"
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @endif
        {{ $attributes->merge(['class' => 'block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary']) }}
    >
        {{ $slot }}
    </select>
    @error($dotKey)
        <p id="{{ $fieldId }}-error" class="text-sm text-error">{{ $message }}</p>
    @enderror
</div>
