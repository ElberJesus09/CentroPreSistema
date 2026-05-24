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
    $hasError = $errors->has($computedError);
@endphp

<div class="space-y-1">
    <label for="{{ $fieldId }}" class="block text-sm font-semibold text-on-surface-variant">{{ $label }}</label>
    <textarea
        id="{{ $fieldId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @endif
        {{ $attributes->merge(['class' => 'block w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3.5 py-3 text-sm text-on-surface shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20']) }}
    >{{ old($computedError, $value) }}</textarea>
    @error($computedError)
        <p id="{{ $fieldId }}-error" class="text-sm text-error">{{ $message }}</p>
    @enderror
</div>
