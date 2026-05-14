@props([
    'label',
    'name',
    'rows' => 3,
    'value' => '',
    'errorKey' => null,
])

@php
    $computedError = $errorKey ?? preg_replace('/^(\w+)\[(\w+)\]$/', '$1.$2', $name);
    $fieldId = 'ta_'.md5($name);
@endphp

<div class="space-y-1">
    <label for="{{ $fieldId }}" class="block text-sm font-semibold text-on-surface-variant">{{ $label }}</label>
    <textarea
        id="{{ $fieldId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary']) }}
    >{{ old($computedError, $value) }}</textarea>
    @error($computedError)
        <p class="text-sm text-error">{{ $message }}</p>
    @enderror
</div>
