@props([
'for' => null,
])

<label
    @if ($for) for="{{ $for }}" @endif
    {{ $attributes->class([
        'block text-sm font-medium text-gray-900',
    ]) }}>
    {{ $slot }}
</label>