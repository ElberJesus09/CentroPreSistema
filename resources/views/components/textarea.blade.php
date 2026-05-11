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
    <label for="{{ $fieldId }}" class="block text-sm font-medium text-neutral-800">{{ $label }}</label>
    <textarea
        id="{{ $fieldId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand']) }}
    >{{ old($computedError, $value) }}</textarea>
    @error($computedError)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
