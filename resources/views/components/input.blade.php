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
    $hasError = $errors->has($dotKey);
@endphp

<div class="space-y-1">
    <label for="{{ $fieldId }}" class="block text-sm font-semibold text-on-surface-variant">{{ $label }}</label>
    <input
        id="{{ $fieldId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($dotKey, $value) }}"
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        {{ $attributes->merge(['class' => 'block w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3.5 py-3 text-sm text-on-surface shadow-sm placeholder:text-outline/50 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20']) }}
    />
    @error($dotKey)
        <p id="{{ $fieldId }}-error" class="text-sm text-error">{{ $message }}</p>
    @enderror
</div>
