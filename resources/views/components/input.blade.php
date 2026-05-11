@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
    'autocomplete' => null,
])

<div class="space-y-1">
    <label for="{{ $name }}" class="block text-sm font-medium text-neutral-800">{{ $label }}</label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        {{ $attributes->merge(['class' => 'block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand']) }}
    />
    @error($name)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
